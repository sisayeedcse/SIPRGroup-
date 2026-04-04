<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MemberManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_active_member_can_view_members_directory(): void
    {
        $member = User::factory()->create([
            'status' => 'active',
            'role' => 'member',
        ]);

        User::factory()->create([
            'status' => 'active',
            'role' => 'finance',
        ]);

        $this->actingAs($member)
            ->get(route('members.index'))
            ->assertOk()
            ->assertSee('Members Directory');
    }

    public function test_non_admin_cannot_update_member_profile_controls(): void
    {
        $actor = User::factory()->create([
            'status' => 'active',
            'role' => 'member',
        ]);

        $target = User::factory()->create([
            'status' => 'active',
            'role' => 'member',
            'locked' => false,
        ]);

        $this->actingAs($actor)
            ->put(route('members.update', $target), [
                'role' => 'finance',
                'status' => 'active',
                'locked' => true,
                'title' => 'Updated title',
                'phone' => '01700000000',
            ])
            ->assertForbidden();
    }

    public function test_admin_can_update_member_and_activity_is_logged(): void
    {
        $admin = User::factory()->create([
            'status' => 'active',
            'role' => 'admin',
        ]);

        $target = User::factory()->create([
            'status' => 'active',
            'role' => 'member',
            'locked' => false,
            'title' => null,
            'phone' => null,
        ]);

        $this->actingAs($admin)
            ->put(route('members.update', $target), [
                'role' => 'secretary',
                'status' => 'pending',
                'locked' => true,
                'title' => 'Operations Lead',
                'phone' => '01711112222',
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('users', [
            'id' => $target->id,
            'role' => 'secretary',
            'status' => 'pending',
            'locked' => true,
            'title' => 'Operations Lead',
            'phone' => '01711112222',
        ]);

        $this->assertDatabaseHas('activities', [
            'action' => 'member-update',
            'user_id' => $admin->id,
            'role' => 'admin',
        ]);
    }
}
