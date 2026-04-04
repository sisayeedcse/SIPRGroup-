<?php

namespace Tests\Feature;

use App\Models\Announcement;
use App\Models\Document;
use App\Models\Investment;
use App\Models\Proposal;
use App\Models\Transaction;
use App\Models\User;
use App\Models\Wallet;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Gate;
use Tests\TestCase;

class PolicyAuthorizationTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_has_write_access_across_core_modules(): void
    {
        $admin = User::factory()->create(['role' => 'admin', 'status' => 'active']);

        $this->assertTrue($admin->can('create', Transaction::class));
        $this->assertTrue($admin->can('update', User::factory()->create()));
        $this->assertTrue($admin->can('create', Investment::class));
        $this->assertTrue($admin->can('create', Announcement::class));
        $this->assertTrue($admin->can('updateStatus', Proposal::query()->create([
            'title' => 'P',
            'description' => 'D',
            'date' => now()->toDateString(),
            'proposed_by' => $admin->id,
            'status' => 'active',
        ])));
        $this->assertTrue($admin->can('delete', Document::query()->create([
            'name' => 'Doc',
            'category' => 'other',
            'uploaded_by' => $admin->id,
        ])));
        $this->assertTrue($admin->can('viewAny', Wallet::class));
    }

    public function test_member_has_read_only_access_for_restricted_modules(): void
    {
        $member = User::factory()->create(['role' => 'member', 'status' => 'active']);

        $this->assertTrue($member->can('viewAny', Transaction::class));
        $this->assertFalse($member->can('create', Transaction::class));

        $this->assertTrue($member->can('viewAny', Investment::class));
        $this->assertFalse($member->can('create', Investment::class));

        $this->assertTrue($member->can('viewAny', Document::class));
        $this->assertFalse($member->can('create', Document::class));
    }

    public function test_report_gate_is_limited_to_privileged_roles(): void
    {
        $finance = User::factory()->create(['role' => 'finance', 'status' => 'active']);
        $member = User::factory()->create(['role' => 'member', 'status' => 'active']);

        $this->assertTrue(Gate::forUser($finance)->allows('viewReports'));
        $this->assertFalse(Gate::forUser($member)->allows('viewReports'));
    }
}
