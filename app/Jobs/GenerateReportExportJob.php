<?php

namespace App\Jobs;

use App\Models\Investment;
use App\Models\ReportExport;
use App\Models\Transaction;
use App\Models\User;
use App\Notifications\ReportExportReadyNotification;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;
use Throwable;

class GenerateReportExportJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(public readonly int $reportExportId)
    {
    }

    public function handle(): void
    {
        $export = ReportExport::query()->with('requester')->findOrFail($this->reportExportId);
        $export->update(['status' => 'processing']);

        try {
            $filePath = match ($export->type) {
                'transactions_csv' => $this->buildTransactionsCsv($export),
                'investments_csv' => $this->buildInvestmentsCsv($export),
                'wallet_passbook_pdf' => $this->buildWalletPassbookPdf($export),
                default => throw new \RuntimeException('Unsupported report type: '.$export->type),
            };

            $export->update([
                'status' => 'completed',
                'file_path' => $filePath,
                'completed_at' => now(),
                'error_message' => null,
            ]);

            $export->requester?->notify(new ReportExportReadyNotification($export->fresh()));
        } catch (Throwable $throwable) {
            $export->update([
                'status' => 'failed',
                'error_message' => $throwable->getMessage(),
            ]);

            throw $throwable;
        }
    }

    private function buildTransactionsCsv(ReportExport $export): string
    {
        $filters = $export->filters ?? [];
        $query = Transaction::query()->with('user')->orderBy('date')->orderBy('id');

        if (! empty($filters['type'])) {
            $query->where('type', $filters['type']);
        }

        if (! empty($filters['user_id'])) {
            $query->where('user_id', (int) $filters['user_id']);
        }

        if (! empty($filters['from'])) {
            $query->whereDate('date', '>=', $filters['from']);
        }

        if (! empty($filters['to'])) {
            $query->whereDate('date', '<=', $filters['to']);
        }

        $rows = $query->get();
        $filePath = 'reports/report-export-'.$export->id.'-transactions.csv';
        $fullPath = Storage::disk('public')->path($filePath);
        $this->ensureDirectoryExists($fullPath);
        $handle = fopen($fullPath, 'w');
        fputcsv($handle, ['Date', 'Type', 'Member ID', 'Member Name', 'Amount', 'Note']);

        foreach ($rows as $tx) {
            fputcsv($handle, [
                optional($tx->date)->format('Y-m-d'),
                $tx->type,
                $tx->user?->member_id,
                $tx->user?->name,
                number_format((float) $tx->amount, 2, '.', ''),
                $tx->note,
            ]);
        }

        fclose($handle);

        return $filePath;
    }

    private function buildInvestmentsCsv(ReportExport $export): string
    {
        $filters = $export->filters ?? [];
        $query = Investment::query()->orderBy('date')->orderBy('id');

        if (! empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        $rows = $query->get();
        $filePath = 'reports/report-export-'.$export->id.'-investments.csv';
        $fullPath = Storage::disk('public')->path($filePath);
        $this->ensureDirectoryExists($fullPath);
        $handle = fopen($fullPath, 'w');
        fputcsv($handle, ['Date', 'Name', 'Sector', 'Partner', 'Status', 'Capital', 'Expected Return', 'Actual Return']);

        foreach ($rows as $investment) {
            fputcsv($handle, [
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

        fclose($handle);

        return $filePath;
    }

    private function buildWalletPassbookPdf(ReportExport $export): string
    {
        $filters = $export->filters ?? [];
        $userId = (int) ($filters['user_id'] ?? 0);
        $user = User::query()->with(['wallet.histories' => fn ($q) => $q->orderBy('date')->orderBy('id')])->findOrFail($userId);

        $filePath = 'reports/report-export-'.$export->id.'-wallet-passbook.pdf';
        $fullPath = Storage::disk('public')->path($filePath);
        $this->ensureDirectoryExists($fullPath);

        Pdf::loadView('reports.wallet-passbook', [
            'member' => $user,
            'wallet' => $user->wallet,
            'entries' => $user->wallet?->histories ?? collect(),
            'generatedAt' => now(),
        ])->setPaper('a4')->save($fullPath);

        return $filePath;
    }

    private function ensureDirectoryExists(string $fullPath): void
    {
        $directory = dirname($fullPath);

        if (! is_dir($directory)) {
            mkdir($directory, 0777, true);
        }
    }
}
