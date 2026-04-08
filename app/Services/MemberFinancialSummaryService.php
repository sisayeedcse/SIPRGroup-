<?php

namespace App\Services;

use App\Models\Transaction;

class MemberFinancialSummaryService
{
    public function forUser(int $userId): array
    {
        $savingsTotal = (float) Transaction::query()
            ->where('user_id', $userId)
            ->where('type', 'deposit')
            ->sum('amount');

        $investedTotal = (float) Transaction::query()
            ->where('user_id', $userId)
            ->where('type', 'investment')
            ->sum('amount');

        return [
            'savings_total' => $savingsTotal,
            'invested_total' => $investedTotal,
        ];
    }
}
