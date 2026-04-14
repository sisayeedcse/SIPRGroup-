<?php

namespace App\Http\Controllers;

use App\Models\MonthlyDue;
use App\Models\Transaction;
use App\Models\User;
use App\Services\ActivityLogService;
use App\Services\WalletService;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class MonthlyPaymentController extends Controller
{
    public function index(Request $request): View
    {
        $this->authorize('viewReports');

        $filterYear = $request->string('year')->toString() ?: now()->format('Y');
        $filterMonth = $request->string('month')->toString() ?: now()->format('Y-m');

        $months = [];
        for ($i = 0; $i < 12; $i++) {
            $date = Carbon::createFromDate($filterYear, 1, 1)->addMonths($i);
            $months[$date->format('Y-m')] = $date->format('F Y');
        }

        $selectedDate = Carbon::createFromFormat('Y-m', $filterMonth)->startOfMonth();
        $monthStart = $selectedDate->toDateString();
        $monthEnd = $selectedDate->copy()->endOfMonth()->toDateString();

        // Get all active members
        $members = User::query()
            ->where('status', 'active')
            ->orderBy('name')
            ->get();

        // Get all deposit transactions for this month
        $deposits = Transaction::query()
            ->where('type', 'deposit')
            ->where('is_adjustment', false)
            ->whereBetween('date', [$monthStart, $monthEnd])
            ->get()
            ->groupBy('user_id');

        // Build member payment records
        $paymentRecords = $members->map(function (User $member) use ($deposits) {
            $memberDeposits = $deposits->get($member->id, collect());
            $totalPaid = $memberDeposits->sum('amount');

            return [
                'user_id' => $member->id,
                'user_name' => $member->name,
                'user_member_id' => $member->member_id,
                'total_paid' => (float) $totalPaid,
                'deposits' => $memberDeposits,
            ];
        })->sortByDesc('total_paid')->values();

        $expectedTotal = $paymentRecords->count() * 100; // Default expected per member, can be customized
        $paidTotal = (float) $paymentRecords->sum('total_paid');
        $outstanding = $expectedTotal - $paidTotal;

        $stats = [
            'paid' => $paymentRecords->filter(fn($r) => $r['total_paid'] > 0)->count(),
            'pending' => $paymentRecords->filter(fn($r) => $r['total_paid'] == 0)->count(),
        ];

        return view('app.monthly-payments.index', [
            'paymentRecords' => $paymentRecords,
            'members' => $members,
            'month' => $filterMonth,
            'year' => $filterYear,
            'months' => $months,
            'selectedDate' => $selectedDate,
            'expectedTotal' => $expectedTotal,
            'paidTotal' => $paidTotal,
            'outstanding' => $outstanding,
            'stats' => $stats,
        ]);
    }

    public function addDeposit(
        Request $request,
        WalletService $walletService,
        ActivityLogService $activityLogService
    ): RedirectResponse
    {
        $this->authorize('create', Transaction::class);

        $payload = $request->validate([
            'user_id' => ['required', 'integer', 'exists:users,id'],
            'amount' => ['required', 'numeric', 'min:0.01'],
            'month' => ['required', 'date_format:Y-m'],
            'note' => ['nullable', 'string', 'max:500'],
        ]);

        // Parse month to get deposit date
        $selectedMonth = Carbon::createFromFormat('Y-m', $payload['month']);
        $depositDate = $selectedMonth->toDateString();
        $monthDate = $selectedMonth->startOfMonth()->toDateString();

        DB::transaction(function () use ($payload, $depositDate, $monthDate, $walletService, $activityLogService, $request): void {
            $transaction = Transaction::query()->create([
                'user_id' => (int) $payload['user_id'],
                'type' => 'deposit',
                'amount' => (float) $payload['amount'],
                'date' => $depositDate,
                'note' => $payload['note'] ?? null,
                'is_adjustment' => false,
                'requires_approval' => false,
                'approval_status' => 'approved',
                'approved_by' => $request->user()->id,
                'approved_at' => now(),
            ]);

            $walletService->applyNewTransaction($transaction);
            $activityLogService->transactionCreated($request->user(), $transaction);

            // Create or update MonthlyDue record
            $monthlyDue = MonthlyDue::query()->where('user_id', (int) $payload['user_id'])
                ->whereDate('due_month', $monthDate)
                ->first();

            if ($monthlyDue) {
                $newPaidAmount = (float) $monthlyDue->paid_amount + (float) $payload['amount'];
                $expectedAmount = (float) $monthlyDue->expected_amount;
            } else {
                $newPaidAmount = (float) $payload['amount'];
                $expectedAmount = (float) $payload['amount'];
                $monthlyDue = new MonthlyDue([
                    'user_id' => (int) $payload['user_id'],
                    'due_month' => $monthDate,
                    'expected_amount' => $expectedAmount,
                ]);
            }

            $newStatus = match (true) {
                $newPaidAmount >= $expectedAmount => 'paid',
                $newPaidAmount > 0 => 'partial',
                default => 'unpaid',
            };

            $monthlyDue->paid_amount = $newPaidAmount;
            $monthlyDue->status = $newStatus;
            $monthlyDue->save();
        });

        return back()->with('status', 'Deposit added successfully.');
    }

    public function removeDeposit(
        Request $request,
        Transaction $transaction,
        WalletService $walletService,
        ActivityLogService $activityLogService
    ): RedirectResponse
    {
        $this->authorize('delete', Transaction::class);

        if ($transaction->type !== 'deposit' || $transaction->is_adjustment) {
            return back()->with('error', 'Invalid transaction.');
        }

        DB::transaction(function () use ($transaction, $walletService, $activityLogService, $request): void {
            $userId = $transaction->user_id;
            $amount = $transaction->amount;
            $monthStart = Carbon::parse($transaction->date)->startOfMonth()->toDateString();

            $walletService->removeTransaction($transaction);
            $activityLogService->transactionDeleted($request->user(), $transaction);
            $transaction->delete();

            // Update MonthlyDue record
            $monthlyDue = MonthlyDue::query()
                ->where('user_id', $userId)
                ->whereDate('due_month', $monthStart)
                ->first();

            if ($monthlyDue) {
                $newPaidAmount = max(0, (float) $monthlyDue->paid_amount - (float) $amount);
                $expectedAmount = (float) $monthlyDue->expected_amount;

                $newStatus = match (true) {
                    $newPaidAmount >= $expectedAmount => 'paid',
                    $newPaidAmount > 0 => 'partial',
                    default => 'unpaid',
                };

                $monthlyDue->update([
                    'paid_amount' => $newPaidAmount,
                    'status' => $newStatus,
                ]);
            }
        });

        return back()->with('status', 'Deposit removed successfully.');
    }
}

