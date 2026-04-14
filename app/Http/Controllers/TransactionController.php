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

        $currentUser = $request->user();
        $role = $currentUser->role->value ?? $currentUser->role;
        $isMember = $role === 'member';

        $query = Transaction::query()->with('user')->orderByDesc('date')->orderByDesc('id');

        if ($isMember) {
            $query
                ->where('user_id', $currentUser->id)
                ->where('type', 'investment');
        }

        if (! $isMember && $request->filled('type')) {
            $query->where('type', $request->string('type')->toString());
        }

        if (! $isMember && $request->filled('user_id')) {
            $query->where('user_id', (int) $request->integer('user_id'));
        }

        if ($request->filled('from')) {
            $query->whereDate('date', '>=', $request->string('from')->toString());
        }

        if ($request->filled('to')) {
            $query->whereDate('date', '<=', $request->string('to')->toString());
        }

        $transactions = $query->paginate(20)->withQueryString();
        $users = $isMember
            ? User::query()->where('id', $currentUser->id)->get(['id', 'name', 'member_id'])
            : User::query()->orderBy('name')->get(['id', 'name', 'member_id']);

        $investedTotal = (float) Transaction::query()
            ->where('user_id', $currentUser->id)
            ->where('type', 'investment')
            ->sum('amount');

        return view('app.transactions.index', [
            'transactions' => $transactions,
            'users' => $users,
            'types' => ['deposit', 'investment', 'profit', 'expense', 'fine'],
            'isMember' => $isMember,
            'investedTotal' => $investedTotal,
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





    public function adjust(
        TransactionAdjustmentRequest $request,
        Transaction $transaction,
        PeriodLockService $periodLockService,
        WalletService $walletService,
        ActivityLogService $activityLogService
    ): RedirectResponse {
        // Always allow adjustments for authorized users - no need to check update since we removed that policy
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
