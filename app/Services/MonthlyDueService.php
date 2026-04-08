<?php

namespace App\Services;

use App\Models\MonthlyDue;
use App\Models\Transaction;
use App\Notifications\InAppNotification;
use Carbon\Carbon;

class MonthlyDueService
{
    /** @var list<string> */
    private array $contributionTypes = ['deposit', 'investment'];

    public function createOrUpdateDue(int $userId, string $dueMonth, float $expectedAmount): MonthlyDue
    {
        $month = Carbon::parse($dueMonth)->startOfMonth()->toDateString();

        $due = MonthlyDue::query()->updateOrCreate(
            [
                'user_id' => $userId,
                'due_month' => $month,
            ],
            [
                'expected_amount' => $expectedAmount,
            ]
        );

        return $this->syncOne($due);
    }

    public function syncForMonth(string $dueMonth): int
    {
        $month = Carbon::parse($dueMonth)->startOfMonth()->toDateString();
        $count = 0;

        MonthlyDue::query()
            ->whereDate('due_month', $month)
            ->orderBy('id')
            ->get()
            ->each(function (MonthlyDue $due) use (&$count): void {
                $this->syncOne($due);
                $count++;
            });

        return $count;
    }

    public function syncOne(MonthlyDue $due): MonthlyDue
    {
        $monthStart = Carbon::parse($due->due_month)->startOfMonth();
        $monthEnd = $monthStart->copy()->endOfMonth();

        $paidAmount = (float) Transaction::query()
            ->where('user_id', $due->user_id)
            ->whereIn('type', $this->contributionTypes)
            ->whereBetween('date', [$monthStart->toDateString(), $monthEnd->toDateString()])
            ->sum('amount');

        $expectedAmount = (float) $due->expected_amount;

        $due->update([
            'paid_amount' => $paidAmount,
            'status' => $this->resolveStatus($expectedAmount, $paidAmount),
        ]);

        return $due->fresh();
    }

    public function memberSnapshot(int $userId, ?string $month = null): array
    {
        $monthStart = $month !== null
            ? Carbon::createFromFormat('Y-m', $month)->startOfMonth()
            : now()->startOfMonth();

        $currentDue = MonthlyDue::query()
            ->where('user_id', $userId)
            ->whereDate('due_month', $monthStart->toDateString())
            ->first();

        $arrearsRows = MonthlyDue::query()
            ->where('user_id', $userId)
            ->whereDate('due_month', '<', $monthStart->toDateString())
            ->where('status', '!=', 'paid')
            ->get(['expected_amount', 'paid_amount']);

        $arrearsCount = $arrearsRows->count();
        $arrearsAmount = $arrearsRows
            ->reduce(function (float $carry, MonthlyDue $row): float {
                $outstanding = max(0.0, (float) $row->expected_amount - (float) $row->paid_amount);

                return $carry + $outstanding;
            }, 0.0);

        return [
            'month' => $monthStart->format('Y-m'),
            'current_status' => $currentDue?->status ?? 'unpaid',
            'current_expected' => (float) ($currentDue?->expected_amount ?? 0),
            'current_paid' => (float) ($currentDue?->paid_amount ?? 0),
            'arrears_count' => $arrearsCount,
            'arrears_amount' => $arrearsAmount,
            'has_due_row' => $currentDue !== null,
        ];
    }

    public function sendUnpaidReminders(string $month): int
    {
        $monthStart = Carbon::createFromFormat('Y-m', $month)->startOfMonth();

        $dues = MonthlyDue::query()
            ->with('user')
            ->whereDate('due_month', $monthStart->toDateString())
            ->whereIn('status', ['unpaid', 'partial'])
            ->get();

        $count = 0;

        foreach ($dues as $due) {
            if (! $due->user || $due->user->status !== 'active') {
                continue;
            }

            $expected = (float) $due->expected_amount;
            $paid = (float) $due->paid_amount;
            $outstanding = max(0.0, $expected - $paid);

            $due->user->notify(new InAppNotification(
                'Monthly Due Reminder',
                sprintf(
                    'Your due status for %s is %s. Outstanding amount: %.2f.',
                    $monthStart->format('Y-m'),
                    strtoupper($due->status),
                    $outstanding
                ),
                [
                    'month' => $monthStart->format('Y-m'),
                    'status' => $due->status,
                    'expected_amount' => $expected,
                    'paid_amount' => $paid,
                    'outstanding_amount' => $outstanding,
                ]
            ));

            $count++;
        }

        return $count;
    }

    private function resolveStatus(float $expectedAmount, float $paidAmount): string
    {
        if ($paidAmount <= 0.0) {
            return 'unpaid';
        }

        if ($expectedAmount > 0.0 && $paidAmount < $expectedAmount) {
            return 'partial';
        }

        return 'paid';
    }
}
