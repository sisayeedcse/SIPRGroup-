<?php

namespace App\Http\Controllers;

use App\Http\Requests\TransactionRequest;
use App\Models\Transaction;
use App\Models\User;
use App\Services\ActivityLogService;
use App\Services\WalletService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class TransactionController extends Controller
{
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
        WalletService $walletService,
        ActivityLogService $activityLogService
    ): RedirectResponse
    {
        $this->authorize('create', Transaction::class);

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
        WalletService $walletService,
        ActivityLogService $activityLogService
    ): RedirectResponse
    {
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
        WalletService $walletService,
        ActivityLogService $activityLogService,
        Request $request
    ): RedirectResponse
    {
        $this->authorize('delete', $transaction);

        DB::transaction(function () use ($transaction, $walletService, $activityLogService, $request): void {
            $walletService->removeTransaction($transaction);
            $activityLogService->transactionDeleted($request->user(), $transaction);
            $transaction->delete();
        });

        return back()->with('status', 'Transaction deleted.');
    }
}
