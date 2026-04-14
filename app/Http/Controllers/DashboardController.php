<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use App\Models\User;
use App\Services\MemberFinancialSummaryService;
use App\Services\MonthlyDueService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(
        Request $request,
        MonthlyDueService $monthlyDueService,
        MemberFinancialSummaryService $summaryService
    ): View
    {
        $user = $request->user();
        $userId = $user->id;
        $dueSnapshot = $monthlyDueService->memberSnapshot($userId);
        $financialSummary = $summaryService->forUser($userId);

        $monthStart = now()->startOfMonth()->toDateString();
        $monthEnd = now()->endOfMonth()->toDateString();

        $monthlyMemberContributions = [];
        $adminMembers = collect();

        if (($user->role->value ?? $user->role) === 'admin') {
            $adminMembers = User::query()
                ->where('status', 'active')
                ->orderBy('name')
                ->get(['id', 'name', 'member_id']);

            $buildTypeRows = function (string $type) use ($monthStart, $monthEnd, $adminMembers): array {
                $amountByUserId = Transaction::query()
                    ->selectRaw('user_id, SUM(amount) as total_amount')
                    ->whereBetween('date', [$monthStart, $monthEnd])
                    ->where('type', $type)
                    ->where('is_adjustment', false)
                    ->groupBy('user_id')
                    ->pluck('total_amount', 'user_id');

                $rows = $adminMembers
                    ->map(function (User $member) use ($type, $amountByUserId): array {
                        $amount = (float) ($amountByUserId[$member->id] ?? 0);

                        return [
                            'member_name' => $member->name,
                            'member_id' => $member->member_id,
                            'amount' => $amount,
                            'pending' => $type === 'deposit' && $amount <= 0,
                        ];
                    })
                    ->sortByDesc('amount')
                    ->values();

                return [
                    'rows' => $rows,
                    'total' => $rows->sum('amount'),
                ];
            };

            $monthlyMemberContributions = [
                'deposit' => $buildTypeRows('deposit'),
                'investment' => $buildTypeRows('investment'),
            ];
        }

        return view('app.dashboard', [
            'dueSnapshot' => $dueSnapshot,
            'financialSummary' => $financialSummary,
            'currentMonth' => now()->format('Y-m'),
            'monthlyMemberContributions' => $monthlyMemberContributions,
            'adminMembers' => $adminMembers,
        ]);
    }
}
