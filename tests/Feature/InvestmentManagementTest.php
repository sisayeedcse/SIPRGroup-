<?php

namespace Tests\Feature;

use App\Models\Investment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class InvestmentManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_active_member_can_view_investments_page(): void
    {
        $member = User::factory()->create([
            'status' => 'active',
            'role' => 'member',
        ]);

        $this->actingAs($member)
            ->get(route('investments.index'))
            ->assertOk()
            ->assertSee('Investments');
    }

    public function test_finance_can_create_investment_and_activity_is_logged(): void
    {
        $finance = User::factory()->create([
            'status' => 'active',
            'role' => 'finance',
        ]);

        $this->actingAs($finance)
            ->post(route('investments.store'), [
                'name' => 'Plastic Recovery Unit',
                'description' => 'Pilot facility',
                'sector' => 'Recycling',
                'partner' => 'Local Partner',
                'date' => now()->toDateString(),
                'capital_deployed' => 10000,
                'expected_return' => 12000,
                'actual_return' => 0,
                'status' => 'active',
                'notes' => 'Phase 1',
            ])
            ->assertRedirect();

        $investment = Investment::query()->first();

        $this->assertNotNull($investment);
        $this->assertDatabaseHas('investments', [
            'name' => 'Plastic Recovery Unit',
            'status' => 'active',
        ]);

        $this->assertDatabaseHas('activities', [
            'action' => 'investment-create',
            'user_id' => $finance->id,
            'role' => 'finance',
        ]);
    }

    public function test_secretary_can_add_milestone_and_collection(): void
    {
        $secretary = User::factory()->create([
            'status' => 'active',
            'role' => 'secretary',
        ]);

        $investment = Investment::query()->create([
            'name' => 'Small Venture',
            'date' => now()->toDateString(),
            'capital_deployed' => 5000,
            'status' => 'active',
            'actual_return' => 0,
        ]);

        $this->actingAs($secretary)
            ->post(route('investments.milestones.store', $investment), [
                'title' => 'Vendor finalization',
                'date' => now()->toDateString(),
                'done' => true,
            ])
            ->assertRedirect();

        $this->actingAs($secretary)
            ->post(route('investments.collections.store', $investment), [
                'date' => now()->toDateString(),
                'kg' => 100,
                'sold_kg' => 80,
                'revenue' => 1000,
                'cost' => 700,
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('milestones', [
            'investment_id' => $investment->id,
            'title' => 'Vendor finalization',
            'done' => true,
        ]);

        $this->assertDatabaseHas('collections', [
            'investment_id' => $investment->id,
            'kg' => 100.00,
            'profit' => 300.00,
        ]);

        $this->assertDatabaseHas('activities', [
            'action' => 'investment-milestone-add',
            'user_id' => $secretary->id,
        ]);

        $this->assertDatabaseHas('activities', [
            'action' => 'investment-collection-add',
            'user_id' => $secretary->id,
        ]);
    }

    public function test_member_cannot_create_investment(): void
    {
        $member = User::factory()->create([
            'status' => 'active',
            'role' => 'member',
        ]);

        $this->actingAs($member)
            ->post(route('investments.store'), [
                'name' => 'Blocked Investment',
                'date' => now()->toDateString(),
                'capital_deployed' => 100,
                'status' => 'active',
            ])
            ->assertForbidden();
    }
}
