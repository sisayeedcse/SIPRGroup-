<?php

namespace Tests\Feature;

use App\Models\Announcement;
use App\Models\Proposal;
use App\Models\ProposalVote;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class NoticeboardManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_active_member_can_open_noticeboard(): void
    {
        $member = User::factory()->create([
            'status' => 'active',
            'role' => 'member',
        ]);

        $this->actingAs($member)
            ->get(route('noticeboard.index'))
            ->assertOk()
            ->assertSee('Noticeboard');
    }

    public function test_secretary_can_create_announcement_and_activity_is_logged(): void
    {
        $secretary = User::factory()->create([
            'status' => 'active',
            'role' => 'secretary',
        ]);

        $this->actingAs($secretary)
            ->post(route('announcements.store'), [
                'title' => 'Monthly Meeting',
                'message' => 'Meeting at 5 PM',
                'pinned' => true,
            ])
            ->assertRedirect();

        $announcement = Announcement::query()->first();
        $this->assertNotNull($announcement);

        $this->assertDatabaseHas('announcements', [
            'title' => 'Monthly Meeting',
            'author_id' => $secretary->id,
            'pinned' => true,
        ]);

        $this->assertDatabaseHas('activities', [
            'action' => 'announcement-create',
            'user_id' => $secretary->id,
        ]);
    }

    public function test_member_cannot_create_announcement(): void
    {
        $member = User::factory()->create([
            'status' => 'active',
            'role' => 'member',
        ]);

        $this->actingAs($member)
            ->post(route('announcements.store'), [
                'title' => 'Blocked',
                'message' => 'Blocked message',
            ])
            ->assertForbidden();
    }

    public function test_member_can_create_proposal_and_vote(): void
    {
        $member = User::factory()->create([
            'status' => 'active',
            'role' => 'member',
        ]);

        $this->actingAs($member)
            ->post(route('proposals.store'), [
                'title' => 'Buy Equipment',
                'description' => 'Need new equipment',
                'amount' => 5000,
                'date' => now()->toDateString(),
            ])
            ->assertRedirect();

        $proposal = Proposal::query()->first();
        $this->assertNotNull($proposal);

        $this->actingAs($member)
            ->post(route('proposals.vote', $proposal), [
                'vote' => 'yes',
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('proposals', [
            'id' => $proposal->id,
            'proposed_by' => $member->id,
            'status' => 'active',
        ]);

        $this->assertDatabaseHas('proposal_votes', [
            'proposal_id' => $proposal->id,
            'user_id' => $member->id,
            'vote' => 'yes',
        ]);

        $this->assertDatabaseHas('activities', [
            'action' => 'proposal-create',
            'user_id' => $member->id,
        ]);

        $this->assertDatabaseHas('activities', [
            'action' => 'proposal-vote',
            'user_id' => $member->id,
        ]);
    }

    public function test_finance_can_update_proposal_status(): void
    {
        $finance = User::factory()->create([
            'status' => 'active',
            'role' => 'finance',
        ]);

        $owner = User::factory()->create([
            'status' => 'active',
            'role' => 'member',
        ]);

        $proposal = Proposal::query()->create([
            'title' => 'Funding Request',
            'description' => 'Need funds',
            'amount' => 1200,
            'date' => now()->toDateString(),
            'proposed_by' => $owner->id,
            'status' => 'active',
        ]);

        $this->actingAs($finance)
            ->put(route('proposals.status.update', $proposal), [
                'status' => 'approved',
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('proposals', [
            'id' => $proposal->id,
            'status' => 'approved',
        ]);

        $this->assertDatabaseHas('activities', [
            'action' => 'proposal-status',
            'user_id' => $finance->id,
        ]);
    }
}
