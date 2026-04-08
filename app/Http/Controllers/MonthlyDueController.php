<?php

namespace App\Http\Controllers;

use App\Models\MonthlyDue;
use App\Models\User;
use App\Services\MonthlyDueService;
use App\Services\PeriodLockService;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Illuminate\View\View;

class MonthlyDueController extends Controller
{
    public function index(Request $request): View
    {
        $this->authorize('viewReports');

        $monthInput = $request->string('month')->toString();
        $monthStart = $monthInput !== ''
            ? Carbon::createFromFormat('Y-m', $monthInput)->startOfMonth()
            : now()->startOfMonth();

        $monthDate = $monthStart->toDateString();

        $duesQuery = MonthlyDue::query()
            ->with('user')
            ->whereDate('due_month', $monthDate)
            ->orderBy('status')
            ->orderByDesc('paid_amount')
            ->orderBy('id');

        $dues = (clone $duesQuery)->paginate(25)->withQueryString();

        $expectedTotal = (float) (clone $duesQuery)->sum('expected_amount');
        $paidTotal = (float) (clone $duesQuery)->sum('paid_amount');
        $paidCount = (clone $duesQuery)->where('status', 'paid')->count();
        $partialCount = (clone $duesQuery)->where('status', 'partial')->count();
        $unpaidCount = (clone $duesQuery)->where('status', 'unpaid')->count();
        $memberCount = $paidCount + $partialCount + $unpaidCount;

        $collectionRate = $expectedTotal > 0
            ? round(($paidTotal / $expectedTotal) * 100, 2)
            : 0.0;

        $monthClosed = app(PeriodLockService::class)->isMonthClosed($monthDate);

        return view('app.reports.monthly-dues', [
            'dues' => $dues,
            'month' => $monthStart->format('Y-m'),
            'expectedTotal' => $expectedTotal,
            'paidTotal' => $paidTotal,
            'collectionRate' => $collectionRate,
            'memberCount' => $memberCount,
            'paidCount' => $paidCount,
            'partialCount' => $partialCount,
            'unpaidCount' => $unpaidCount,
            'monthClosed' => $monthClosed,
        ]);
    }

    public function prepare(Request $request, MonthlyDueService $dueService): RedirectResponse
    {
        $this->authorize('viewReports');

        $payload = $request->validate([
            'month' => ['required', 'date_format:Y-m'],
            'expected_amount' => ['required', 'numeric', 'min:0'],
        ]);

        $month = Carbon::createFromFormat('Y-m', $payload['month'])->startOfMonth();
        $expected = (float) $payload['expected_amount'];

        User::query()
            ->where('status', 'active')
            ->orderBy('id')
            ->pluck('id')
            ->each(function (int $userId) use ($dueService, $month, $expected): void {
                $dueService->createOrUpdateDue($userId, $month->toDateString(), $expected);
            });

        $dueService->syncForMonth($month->toDateString());

        return redirect()
            ->route('reports.monthly-dues.index', ['month' => $month->format('Y-m')])
            ->with('status', 'Monthly dues prepared and synchronized.');
    }

    public function csv(Request $request): StreamedResponse
    {
        $this->authorize('viewReports');

        $request->validate([
            'month' => ['required', 'date_format:Y-m'],
        ]);

        $month = Carbon::createFromFormat('Y-m', $request->string('month')->toString())->startOfMonth();

        $dues = MonthlyDue::query()
            ->with('user')
            ->whereDate('due_month', $month->toDateString())
            ->orderBy('status')
            ->orderByDesc('paid_amount')
            ->orderBy('id')
            ->get();

        $filename = 'monthly-dues-'.$month->format('Y-m').'.csv';

        return response()->streamDownload(function () use ($dues): void {
            $out = fopen('php://output', 'w');

            fputcsv($out, ['Member ID', 'Member Name', 'Month', 'Expected Amount', 'Paid Amount', 'Outstanding Amount', 'Status']);

            foreach ($dues as $due) {
                $expected = (float) $due->expected_amount;
                $paid = (float) $due->paid_amount;
                $outstanding = max(0.0, $expected - $paid);

                fputcsv($out, [
                    $due->user?->member_id,
                    $due->user?->name,
                    $month->format('Y-m'),
                    number_format($expected, 2, '.', ''),
                    number_format($paid, 2, '.', ''),
                    number_format($outstanding, 2, '.', ''),
                    $due->status,
                ]);
            }

            fclose($out);
        }, $filename, [
            'Content-Type' => 'text/csv',
        ]);
    }

    public function remindUnpaid(Request $request, MonthlyDueService $dueService): RedirectResponse
    {
        $this->authorize('viewReports');

        $payload = $request->validate([
            'month' => ['required', 'date_format:Y-m'],
        ]);

        $sent = $dueService->sendUnpaidReminders($payload['month']);

        return redirect()
            ->route('reports.monthly-dues.index', ['month' => $payload['month']])
            ->with('status', "Reminder notifications sent to {$sent} members.");
    }

    public function closeMonth(Request $request, PeriodLockService $periodLockService): RedirectResponse
    {
        $this->authorize('viewReports');

        $payload = $request->validate([
            'month' => ['required', 'date_format:Y-m'],
            'note' => ['nullable', 'string', 'max:1000'],
        ]);

        $periodLockService->closeMonth(
            $payload['month'],
            $request->user()->id,
            $payload['note'] ?? null
        );

        return redirect()
            ->route('reports.monthly-dues.index', ['month' => $payload['month']])
            ->with('status', 'Month closed successfully.');
    }
}
