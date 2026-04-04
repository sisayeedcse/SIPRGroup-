<?php

namespace Tests\Feature;

use App\Models\Activity;
use App\Models\Document;
use App\Models\Investment;
use App\Models\Proposal;
use App\Models\ReportExport;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class MissingFeaturesTest extends TestCase
{
    use RefreshDatabase;

    public function test_privileged_user_can_view_activity_log(): void
    {
        $finance = User::factory()->create([
            'status' => 'active',
            'role' => 'finance',
        ]);

        Activity::query()->create([
            'action' => 'proposal-create',
            'detail' => 'Created proposal #1.',
            'user_id' => $finance->id,
            'role' => 'finance',
        ]);

        $this->actingAs($finance)
            ->get(route('activities.index'))
            ->assertOk()
            ->assertSee('Activity Log');
    }

    public function test_user_can_update_own_profile(): void
    {
        $member = User::factory()->create([
            'status' => 'active',
            'role' => 'member',
        ]);

        $this->actingAs($member)
            ->patch(route('profile.update'), [
                'name' => 'Updated Member',
                'email' => 'updated@example.com',
                'phone' => '1234567890',
                'title' => 'Coordinator',
                'address' => 'Test Address',
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('users', [
            'id' => $member->id,
            'name' => 'Updated Member',
            'email' => 'updated@example.com',
            'phone' => '1234567890',
            'title' => 'Coordinator',
            'address' => 'Test Address',
        ]);
    }

    public function test_privileged_user_can_download_completed_report_export(): void
    {
        Storage::fake('public');

        $finance = User::factory()->create([
            'status' => 'active',
            'role' => 'finance',
        ]);

        $path = 'reports/test-export.csv';
        Storage::disk('public')->put($path, 'hello,world');

        $export = ReportExport::query()->create([
            'requested_by' => $finance->id,
            'type' => 'transactions_csv',
            'filters' => [],
            'status' => 'completed',
            'file_path' => $path,
            'completed_at' => now(),
        ]);

        $this->actingAs($finance)
            ->get(route('reports.exports.download', $export))
            ->assertDownload('test-export.csv');
    }

    public function test_user_can_open_transaction_and_investment_detail_pages(): void
    {
        $member = User::factory()->create([
            'status' => 'active',
            'role' => 'member',
        ]);

        $transaction = Transaction::query()->create([
            'user_id' => $member->id,
            'type' => 'deposit',
            'amount' => 125,
            'date' => now()->toDateString(),
            'note' => 'detail-test',
        ]);

        $investment = Investment::query()->create([
            'name' => 'Detail Project',
            'description' => 'Testing detail page',
            'sector' => 'Agriculture',
            'partner' => 'Partner',
            'date' => now()->toDateString(),
            'capital_deployed' => 5000,
            'expected_return' => 6000,
            'actual_return' => 0,
            'status' => 'active',
        ]);

        $this->actingAs($member)
            ->get(route('transactions.show', $transaction))
            ->assertOk()
            ->assertSee('Transaction Detail');

        $this->actingAs($member)
            ->get(route('investments.show', $investment))
            ->assertOk()
            ->assertSee('Investment Detail');
    }

    public function test_user_can_open_proposal_and_document_detail_pages(): void
    {
        Storage::fake('public');

        $member = User::factory()->create([
            'status' => 'active',
            'role' => 'member',
        ]);

        $proposal = Proposal::query()->create([
            'title' => 'Detail Proposal',
            'description' => 'Testing proposal detail page',
            'amount' => 750,
            'date' => now()->toDateString(),
            'proposed_by' => $member->id,
            'status' => 'active',
            'quorum_required' => 2,
            'closes_at' => now()->addDays(5)->toDateString(),
        ]);

        $path = 'documents/detail-test.pdf';
        Storage::disk('public')->put($path, 'pdf contents');

        $document = Document::query()->create([
            'name' => 'Detail File',
            'category' => 'legal',
            'file_path' => $path,
            'uploaded_by' => $member->id,
        ]);

        $this->actingAs($member)
            ->get(route('proposals.show', $proposal))
            ->assertOk()
            ->assertSee('Proposal Detail');

        $this->actingAs($member)
            ->get(route('documents.show', $document))
            ->assertOk()
            ->assertSee('Document Detail');

        $this->actingAs($member)
            ->get(route('documents.download', $document))
            ->assertDownload('detail-test.pdf');
    }

    public function test_proposal_owner_can_edit_proposal(): void
    {
        $member = User::factory()->create([
            'status' => 'active',
            'role' => 'member',
        ]);

        $proposal = Proposal::query()->create([
            'title' => 'Original Title',
            'description' => 'Original description',
            'amount' => 1000,
            'date' => now()->toDateString(),
            'proposed_by' => $member->id,
            'status' => 'active',
            'quorum_required' => 3,
            'closes_at' => now()->addDays(7)->toDateString(),
        ]);

        $this->actingAs($member)
            ->put(route('proposals.update', $proposal), [
                'title' => 'Updated Title',
                'description' => 'Updated description',
                'amount' => 1500,
                'date' => now()->toDateString(),
                'closes_at' => now()->addDays(10)->toDateString(),
                'quorum_required' => 4,
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('proposals', [
            'id' => $proposal->id,
            'title' => 'Updated Title',
            'description' => 'Updated description',
            'amount' => 1500,
            'quorum_required' => 4,
        ]);

        $this->assertDatabaseHas('activities', [
            'action' => 'proposal-update',
            'user_id' => $member->id,
        ]);
    }
}