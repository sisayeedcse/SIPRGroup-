<?php

namespace App\Http\Controllers;

use App\Http\Requests\TransactionAdjustmentRequest;
use App\Http\Requests\TransactionRequest;
use App\Models\Transaction;
use App\Models\User;
use App\Services\ActivityLogService;
use App\Services\PeriodLockService;
use App\Services\WalletService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class TransactionController extends Controller
{
    private const HIGH_VALUE_ADJUSTMENT_THRESHOLD = 1000.00;

    public function show(Transaction $transaction): View
    {
        $this->authorize('view', $transaction);

        $transaction->load('user');

        return view('app.transactions.show', [
            'transaction' => $transaction,
        ]);
    }

    public function index(Request $request): View
    {
        $this->authorize('viewAny', Transaction::class);

        $query = Transaction::query()->with('user')->orderByDesc('date')->orderByDesc('id');

        if ($request->filled('type')) {
            $query->where('type', $request->string('type')->toString());
        }

        if ($request->filled('user_id')) {
            $query->where('user_id', (int) $request->integer('user_id'));
        }

        if ($request->filled('from')) {
            $query->whereDate('date', '>=', $request->string('from')->toString());
        }

        if ($request->filled('to')) {
            $query->whereDate('date', '<=', $request->string('to')->toString());
        }

        $transactions = $query->paginate(20)->withQueryString();
        $users = User::query()->orderBy('name')->get(['id', 'name', 'member_id']);

        return view('app.transactions.index', [
            'transactions' => $transactions,
            'users' => $users,
            'types' => ['deposit', 'investment', 'profit', 'expense', 'fine'],
        ]);
    }

    public function store(
        TransactionRequest $request,
        PeriodLockService $periodLockService,
        WalletService $walletService,
        ActivityLogService $activityLogService
    ): RedirectResponse
    {
        $this->authorize('create', Transaction::class);
        $periodLockService->ensureWritableForActor(
            $request->user(),
            $request->string('date')->toString()
        );

        DB::transaction(function () use ($request, $walletService, $activityLogService): void {
            $transaction = Transaction::query()->create($request->validated());
            $walletService->applyNewTransaction($transaction);
            $activityLogService->transactionCreated($request->user(), $transaction);
        });

        return back()->with('status', 'Transaction created.');
    }

    public function update(
        TransactionRequest $request,
        Transaction $transaction,
        PeriodLockService $periodLockService,
        WalletService $walletService,
        ActivityLogService $activityLogService
    ): RedirectResponse
    {
        $periodLockService->ensureWritableForActor($request->user(), (string) $transaction->date?->toDateString());
        $periodLockService->ensureWritableForActor($request->user(), $request->string('date')->toString());
        $this->authorize('update', $transaction);

        DB::transaction(function () use ($request, $transaction, $walletService, $activityLogService): void {
            $old = $transaction->replicate();
            $old->user_id = $transaction->user_id;
            $old->type = $transaction->type;
            $old->amount = $transaction->amount;
            $old->date = $transaction->date;
            $old->note = $transaction->note;

            $transaction->update($request->validated());
            $updated = $transaction->fresh();

            $walletService->replaceTransaction($old, $updated);
            $activityLogService->transactionUpdated($request->user(), $old, $updated);
        });

        return back()->with('status', 'Transaction updated.');
    }

    public function destroy(
        Transaction $transaction,
        PeriodLockService $periodLockService,
        WalletService $walletService,
        ActivityLogService $activityLogService,
        Request $request
    ): RedirectResponse
    {
        $this->authorize('delete', $transaction);
        $periodLockService->ensureWritableForActor($request->user(), (string) $transaction->date?->toDateString());

        DB::transaction(function () use ($transaction, $walletService, $activityLogService, $request): void {
            $walletService->removeTransaction($transaction);
            $activityLogService->transactionDeleted($request->user(), $transaction);
            $transaction->delete();
        });

        return back()->with('status', 'Transaction deleted.');
    }

    public function adjust(
        TransactionAdjustmentRequest $request,
        Transaction $transaction,
        PeriodLockService $periodLockService,
        WalletService $walletService,
        ActivityLogService $activityLogService
    ): RedirectResponse {
        $this->authorize('update', $transaction);
        $periodLockService->ensureWritableForActor(
            $request->user(),
            $request->string('date')->toString()
        );

        DB::transaction(function () use ($request, $transaction, $walletService, $activityLogService): void {
            $payload = $request->validated();
            $actorRole = $request->user()->role->value ?? $request->user()->role;
            $requiresApproval = $actorRole === 'finance'
                && (float) $payload['amount'] >= self::HIGH_VALUE_ADJUSTMENT_THRESHOLD;

            $payload['user_id'] = $transaction->user_id;
            $payload['adjustment_for_id'] = $transaction->id;
            $payload['is_adjustment'] = true;
            $payload['requires_approval'] = $requiresApproval;
            $payload['approval_status'] = $requiresApproval ? 'pending' : 'approved';
            $payload['approved_by'] = $requiresApproval ? null : $request->user()->id;
            $payload['approved_at'] = $requiresApproval ? null : now();
            $payload['approval_note'] = null;

            $adjustment = Transaction::query()->create($payload);

            if ($requiresApproval) {
                $activityLogService->transactionAdjustmentRequested($request->user(), $transaction, $adjustment);

                return;
            }

            $walletService->applyNewTransaction($adjustment);
            $activityLogService->transactionAdjusted($request->user(), $transaction, $adjustment);
        });

        $statusMessage = ((float) $request->validated()['amount'] >= self::HIGH_VALUE_ADJUSTMENT_THRESHOLD
            && (($request->user()->role->value ?? $request->user()->role) === 'finance'))
            ? 'Adjustment created and pending admin approval.'
            : 'Adjustment transaction created.';

        return back()->with('status', $statusMessage);
    }

    public function approveAdjustment(
        Request $request,
        Transaction $transaction,
        WalletService $walletService,
        ActivityLogService $activityLogService
    ): RedirectResponse {
        $this->authorize('update', $transaction);

        abort_unless(
            $transaction->is_adjustment
            && $transaction->requires_approval
            && $transaction->approval_status === 'pending',
            404
        );

        $payload = $request->validate([
            'approval_note' => ['nullable', 'string', 'max:1000'],
        ]);

        DB::transaction(function () use ($request, $transaction, $walletService, $activityLogService, $payload): void {
            $transaction->update([
                'approval_status' => 'approved',
                'approved_by' => $request->user()->id,
                'approved_at' => now(),
                'approval_note' => $payload['approval_note'] ?? null,
            ]);

            $approved = $transaction->fresh();
            $walletService->applyNewTransaction($approved);

            $activityLogService->transactionAdjustmentApproved($request->user(), $approved);
        });

        return back()->with('status', 'Adjustment approved and applied.');
    }

    public function rejectAdjustment(
        Request $request,
        Transaction $transaction,
        ActivityLogService $activityLogService
    ): RedirectResponse {
        $this->authorize('update', $transaction);

        abort_unless(
            $transaction->is_adjustment
            && $transaction->requires_approval
            && $transaction->approval_status === 'pending',
            404
        );

        $payload = $request->validate([
            'approval_note' => ['nullable', 'string', 'max:1000'],
        ]);

        $transaction->update([
            'approval_status' => 'rejected',
            'approved_by' => $request->user()->id,
            'approved_at' => now(),
            'approval_note' => $payload['approval_note'] ?? null,
        ]);

        $activityLogService->transactionAdjustmentRejected(
            $request->user(),
            $transaction->fresh(),
            $payload['approval_note'] ?? null
        );

        return back()->with('status', 'Adjustment rejected.');
    }
}
