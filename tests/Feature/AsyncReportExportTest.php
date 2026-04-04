<?php

namespace Tests\Feature;

use App\Jobs\GenerateReportExportJob;
use App\Models\ReportExport;
use App\Models\User;
use App\Notifications\ReportExportReadyNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class AsyncReportExportTest extends TestCase
{
    use RefreshDatabase;

    public function test_reports_page_can_queue_transaction_export(): void
    {
        Storage::fake('public');
        Notification::fake();

        $finance = User::factory()->create(['status' => 'active', 'role' => 'finance']);

        $this->actingAs($finance)
            ->post(route('reports.exports.transactions'), [
                'type' => 'deposit',
                'from' => now()->subWeek()->toDateString(),
                'to' => now()->toDateString(),
            ])
            ->assertRedirect();

        $export = ReportExport::query()
            ->where('requested_by', $finance->id)
            ->where('type', 'transactions_csv')
            ->firstOrFail();

        $this->assertSame('completed', $export->status);
        $this->assertNotNull($export->file_path);
        Storage::disk('public')->assertExists($export->file_path);

        Notification::assertSentTo($finance, ReportExportReadyNotification::class);
    }

    public function test_report_job_generates_file_and_notifies_user(): void
    {
        Storage::fake('public');
        Notification::fake();

        $user = User::factory()->create(['status' => 'active', 'role' => 'finance']);

        $export = ReportExport::query()->create([
            'requested_by' => $user->id,
            'type' => 'transactions_csv',
            'filters' => ['type' => 'deposit'],
            'status' => 'pending',
        ]);

        GenerateReportExportJob::dispatchSync($export->id);

        $updated = $export->fresh();

        $this->assertSame('completed', $updated->status);
        $this->assertNotNull($updated->file_path);
        Storage::disk('public')->assertExists($updated->file_path);

        Notification::assertSentTo($user, ReportExportReadyNotification::class);
    }
}
