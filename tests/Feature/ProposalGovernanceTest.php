<?php

namespace Tests\Feature;

use App\Models\Proposal;
use App\Models\ProposalVote;
use App\Models\User;
use App\Notifications\ProposalFinalizedNotification;
use App\Services\ProposalGovernanceService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class ProposalGovernanceTest extends TestCase
{
    use RefreshDatabase;

    public function test_proposal_auto_finalizes_to_approved_when_quorum_and_majority_yes(): void
    {
        Notification::fake();

        $member = User::factory()->create(['status' => 'active', 'role' => 'member']);

        $proposal = Proposal::query()->create([
            'title' => 'Auto Finalize Proposal',
            'description' => 'Auto governance rule',
            'date' => now()->toDateString(),
            'proposed_by' => $member->id,
            'status' => 'active',
            'quorum_required' => 2,
            'closes_at' => now()->addDays(3)->toDateString(),
        ]);

        $voterA = User::factory()->create(['status' => 'active', 'role' => 'member']);
        $voterB = User::factory()->create(['status' => 'active', 'role' => 'member']);

        ProposalVote::query()->create(['proposal_id' => $proposal->id, 'user_id' => $voterA->id, 'vote' => 'yes']);
        ProposalVote::query()->create(['proposal_id' => $proposal->id, 'user_id' => $voterB->id, 'vote' => 'yes']);

        $finalized = app(ProposalGovernanceService::class)->finalizeOne($proposal);

        $this->assertTrue($finalized);
        $this->assertDatabaseHas('proposals', [
            'id' => $proposal->id,
            'status' => 'approved',
        ]);

        Notification::assertSentTo($member, ProposalFinalizedNotification::class);
        Notification::assertSentTo($voterA, ProposalFinalizedNotification::class);
        Notification::assertSentTo($voterB, ProposalFinalizedNotification::class);
    }

    public function test_proposal_auto_finalizes_to_rejected_when_deadline_passed_without_quorum(): void
    {
        $member = User::factory()->create(['status' => 'active', 'role' => 'member']);

        $proposal = Proposal::query()->create([
            'title' => 'Deadline Finalize',
            'description' => 'Not enough votes',
            'date' => now()->toDateString(),
            'proposed_by' => $member->id,
            'status' => 'active',
            'quorum_required' => 3,
            'closes_at' => now()->subDay()->toDateString(),
        ]);

        $voter = User::factory()->create(['status' => 'active', 'role' => 'member']);
        ProposalVote::query()->create(['proposal_id' => $proposal->id, 'user_id' => $voter->id, 'vote' => 'yes']);

        $finalized = app(ProposalGovernanceService::class)->finalizeOne($proposal);

        $this->assertTrue($finalized);
        $this->assertDatabaseHas('proposals', [
            'id' => $proposal->id,
            'status' => 'rejected',
        ]);
    }

    public function test_privileged_user_can_manually_finalize_from_noticeboard_route(): void
    {
        $finance = User::factory()->create(['status' => 'active', 'role' => 'finance']);
        $member = User::factory()->create(['status' => 'active', 'role' => 'member']);

        $proposal = Proposal::query()->create([
            'title' => 'Manual Finalize',
            'description' => 'Manual force route',
            'date' => now()->toDateString(),
            'proposed_by' => $member->id,
            'status' => 'active',
            'quorum_required' => 5,
            'closes_at' => now()->addDays(10)->toDateString(),
        ]);

        $this->actingAs($finance)
            ->put(route('proposals.finalize', $proposal))
            ->assertRedirect();

        $this->assertDatabaseHas('proposals', [
            'id' => $proposal->id,
            'status' => 'rejected',
        ]);

        $this->assertDatabaseHas('activities', [
            'action' => 'proposal-finalized',
            'user_id' => $finance->id,
        ]);
    }
}
