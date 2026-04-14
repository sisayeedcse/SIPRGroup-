<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
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

        if (($user->role->value ?? $user->role) === 'admin') {
            $buildTypeRows = function (string $type) use ($monthStart, $monthEnd): array {
                $rows = Transaction::query()
                    ->with('user:id,name,member_id')
                    ->selectRaw('user_id, SUM(amount) as total_amount')
                    ->whereBetween('date', [$monthStart, $monthEnd])
                    ->where('type', $type)
                    ->where('is_adjustment', false)
                    ->groupBy('user_id')
                    ->orderByDesc('total_amount')
                    ->get()
                    ->map(function (Transaction $row): array {
                        return [
                            'member_name' => $row->user?->name ?? 'Unknown Member',
                            'member_id' => $row->user?->member_id ?? '-',
                            'amount' => (float) ($row->total_amount ?? 0),
                        ];
                    })
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
        ]);
    }
}
