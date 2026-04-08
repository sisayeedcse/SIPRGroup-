<?php

namespace Tests\Feature;

use App\Models\MonthlyDue;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MonthlyDueMemberWidgetTest extends TestCase
{
    use RefreshDatabase;

    public function test_dashboard_shows_member_due_status_and_arrears_count(): void
    {
        $member = User::factory()->create([
            'status' => 'active',
            'role' => 'member',
        ]);

        $currentMonth = now()->startOfMonth()->toDateString();
        $previousMonth = now()->startOfMonth()->subMonth()->toDateString();

        MonthlyDue::query()->create([
            'user_id' => $member->id,
            'due_month' => $currentMonth,
            'expected_amount' => 1000,
            'paid_amount' => 700,
            'status' => 'partial',
        ]);

        MonthlyDue::query()->create([
            'user_id' => $member->id,
            'due_month' => $previousMonth,
            'expected_amount' => 1000,
            'paid_amount' => 0,
            'status' => 'unpaid',
        ]);

        $this->actingAs($member)
            ->get(route('dashboard'))
            ->assertOk()
            ->assertSee('My Monthly Due Status')
            ->assertSee('Partial')
            ->assertSee('Arrears')
            ->assertSee('1')
            ->assertSee('1,000.00');
    }
}
