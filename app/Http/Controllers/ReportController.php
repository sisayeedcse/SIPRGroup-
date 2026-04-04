<?php

namespace App\Http\Controllers;

use App\Models\Investment;
use App\Models\ReportExport;
use App\Models\Transaction;
use App\Models\User;
use App\Jobs\GenerateReportExportJob;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Illuminate\View\View;

class ReportController extends Controller
{
    public function index(Request $request): View
    {
        $this->authorize('viewReports');

        return view('app.reports.index', [
            'users' => User::query()->orderBy('name')->get(['id', 'name', 'member_id']),
            'types' => ['deposit', 'investment', 'profit', 'expense', 'fine'],
            'exports' => ReportExport::query()
                ->where('requested_by', $request->user()->id)
                ->latest()
                ->get(),
        ]);
    }

    public function requestTransactionsExport(Request $request): RedirectResponse
    {
        $this->authorize('viewReports');

        $filters = $request->validate([
            'type' => ['nullable', 'string'],
            'user_id' => ['nullable', 'integer', 'exists:users,id'],
            'from' => ['nullable', 'date'],
            'to' => ['nullable', 'date'],
        ]);

        $export = ReportExport::query()->create([
            'requested_by' => $request->user()->id,
            'type' => 'transactions_csv',
            'filters' => $filters,
            'status' => 'pending',
        ]);

        GenerateReportExportJob::dispatch($export->id);

        return back()->with('status', 'Transactions export requested. You will be notified when it is ready.');
    }

    public function requestInvestmentsExport(Request $request): RedirectResponse
    {
        $this->authorize('viewReports');

        $filters = $request->validate([
            'status' => ['nullable', 'string'],
        ]);

        $export = ReportExport::query()->create([
            'requested_by' => $request->user()->id,
            'type' => 'investments_csv',
            'filters' => $filters,
            'status' => 'pending',
        ]);

        GenerateReportExportJob::dispatch($export->id);

        return back()->with('status', 'Investments export requested. You will be notified when it is ready.');
    }

    public function requestWalletPassbookExport(Request $request): RedirectResponse
    {
        $this->authorize('viewReports');

        $filters = $request->validate([
            'user_id' => ['required', 'integer', 'exists:users,id'],
        ]);

        $export = ReportExport::query()->create([
            'requested_by' => $request->user()->id,
            'type' => 'wallet_passbook_pdf',
            'filters' => $filters,
            'status' => 'pending',
        ]);

        GenerateReportExportJob::dispatch($export->id);

        return back()->with('status', 'Wallet passbook export requested. You will be notified when it is ready.');
    }

    public function transactionsCsv(Request $request): StreamedResponse
    {
        $this->authorize('viewReports');

        $query = Transaction::query()->with('user')->orderBy('date')->orderBy('id');

        if ($request->filled('type')) {
            $query->where('type', $request->string('type')->toString());
        }

        if ($request->filled('user_id')) {
            $query->where('user_id', $request->integer('user_id'));
        }

        if ($request->filled('from')) {
            $query->whereDate('date', '>=', $request->string('from')->toString());
        }

        if ($request->filled('to')) {
            $query->whereDate('date', '<=', $request->string('to')->toString());
        }

        $transactions = $query->get();

        return response()->streamDownload(function () use ($transactions): void {
            $out = fopen('php://output', 'w');
            fputcsv($out, ['Date', 'Type', 'Member ID', 'Member Name', 'Amount', 'Note']);

            foreach ($transactions as $tx) {
                fputcsv($out, [
                    optional($tx->date)->format('Y-m-d'),
                    $tx->type,
                    $tx->user?->member_id,
                    $tx->user?->name,
                    number_format((float) $tx->amount, 2, '.', ''),
                    $tx->note,
                ]);
            }

            fclose($out);
        }, 'transactions-report.csv', [
            'Content-Type' => 'text/csv',
        ]);
    }

    public function investmentsCsv(Request $request): StreamedResponse
    {
        $this->authorize('viewReports');

        $query = Investment::query()->orderBy('date')->orderBy('id');

        if ($request->filled('status')) {
            $query->where('status', $request->string('status')->toString());
        }

        $investments = $query->get();

        return response()->streamDownload(function () use ($investments): void {
            $out = fopen('php://output', 'w');
            fputcsv($out, ['Date', 'Name', 'Sector', 'Partner', 'Status', 'Capital', 'Expected Return', 'Actual Return']);

            foreach ($investments as $investment) {
                fputcsv($out, [
                    optional($investment->date)->format('Y-m-d'),
                    $investment->name,
                    $investment->sector,
                    $investment->partner,
                    $investment->status,
                    number_format((float) $investment->capital_deployed, 2, '.', ''),
                    number_format((float) ($investment->expected_return ?? 0), 2, '.', ''),
                    number_format((float) ($investment->actual_return ?? 0), 2, '.', ''),
                ]);
            }

            fclose($out);
        }, 'investments-report.csv', [
            'Content-Type' => 'text/csv',
        ]);
    }

    public function walletPassbookPdf(Request $request)
    {
        $this->authorize('viewReports');

        $request->validate([
            'user_id' => ['required', 'integer', 'exists:users,id'],
        ]);

        $user = User::query()->with(['wallet.histories' => fn ($q) => $q->orderBy('date')->orderBy('id')])->findOrFail($request->integer('user_id'));

        $pdf = Pdf::loadView('reports.wallet-passbook', [
            'member' => $user,
            'wallet' => $user->wallet,
            'entries' => $user->wallet?->histories ?? collect(),
            'generatedAt' => now(),
        ])->setPaper('a4');

        return $pdf->download('wallet-passbook-'.$user->member_id.'.pdf');
    }

    public function downloadExport(ReportExport $reportExport)
    {
        $this->authorize('viewReports');

        abort_unless($reportExport->status === 'completed' && $reportExport->file_path, 404);

        abort_unless(Storage::disk('public')->exists($reportExport->file_path), 404);

        return response()->download(
            Storage::disk('public')->path($reportExport->file_path),
            basename($reportExport->file_path)
        );
    }
}
