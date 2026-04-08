<?php

namespace Tests\Feature;

use App\Models\Transaction;
use App\Models\MonthlyDue;
use App\Models\ClosedPeriod;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MonthlyDueDashboardTest extends TestCase
{
    use RefreshDatabase;

    public function test_finance_can_open_monthly_dues_dashboard(): void
    {
        $finance = User::factory()->create([
            'status' => 'active',
            'role' => 'finance',
        ]);

        $this->actingAs($finance)
            ->get(route('reports.monthly-dues.index'))
            ->assertOk()
            ->assertSee('Monthly Dues Dashboard');
    }

    public function test_member_cannot_open_monthly_dues_dashboard(): void
    {
        $member = User::factory()->create([
            'status' => 'active',
            'role' => 'member',
        ]);

        $this->actingAs($member)
            ->get(route('reports.monthly-dues.index'))
            ->assertForbidden();
    }

    public function test_prepare_action_creates_dues_and_syncs_statuses(): void
    {
        $finance = User::factory()->create([
            'status' => 'active',
            'role' => 'finance',
        ]);

        $memberPaid = User::factory()->create([
            'status' => 'active',
            'role' => 'member',
        ]);

        $memberUnpaid = User::factory()->create([
            'status' => 'active',
            'role' => 'member',
        ]);

        $month = now()->startOfMonth();

        Transaction::query()->create([
            'user_id' => $memberPaid->id,
            'type' => 'deposit',
            'amount' => 1000,
            'date' => $month->copy()->addDay()->toDateString(),
            'note' => 'monthly payment',
        ]);

        $this->actingAs($finance)
            ->post(route('reports.monthly-dues.prepare'), [
                'month' => $month->format('Y-m'),
                'expected_amount' => 1000,
            ])
            ->assertRedirect(route('reports.monthly-dues.index', ['month' => $month->format('Y-m')]));

        $this->assertDatabaseHas('monthly_dues', [
            'user_id' => $memberPaid->id,
            'status' => 'paid',
            'expected_amount' => 1000.00,
            'paid_amount' => 1000.00,
        ]);

        $this->assertDatabaseHas('monthly_dues', [
            'user_id' => $memberUnpaid->id,
            'status' => 'unpaid',
            'expected_amount' => 1000.00,
            'paid_amount' => 0.00,
        ]);
    }

    public function test_finance_can_download_monthly_dues_csv(): void
    {
        $finance = User::factory()->create([
            'status' => 'active',
            'role' => 'finance',
        ]);

        $member = User::factory()->create([
            'status' => 'active',
            'role' => 'member',
        ]);

        $month = now()->startOfMonth();

        MonthlyDue::query()->create([
            'user_id' => $member->id,
            'due_month' => $month->toDateString(),
            'expected_amount' => 1000,
            'paid_amount' => 750,
            'status' => 'partial',
        ]);

        $response = $this->actingAs($finance)
            ->get(route('reports.monthly-dues.csv', ['month' => $month->format('Y-m')]));

        $response->assertOk();
        $response->assertHeader('content-type', 'text/csv; charset=UTF-8');
    }

    public function test_reminder_action_notifies_unpaid_and_partial_members_only(): void
    {
        $finance = User::factory()->create([
            'status' => 'active',
            'role' => 'finance',
        ]);

        $paidMember = User::factory()->create(['status' => 'active', 'role' => 'member']);
        $partialMember = User::factory()->create(['status' => 'active', 'role' => 'member']);
        $unpaidMember = User::factory()->create(['status' => 'active', 'role' => 'member']);

        $month = now()->startOfMonth()->toDateString();

        MonthlyDue::query()->create([
            'user_id' => $paidMember->id,
            'due_month' => $month,
            'expected_amount' => 1000,
            'paid_amount' => 1000,
            'status' => 'paid',
        ]);

        MonthlyDue::query()->create([
            'user_id' => $partialMember->id,
            'due_month' => $month,
            'expected_amount' => 1000,
            'paid_amount' => 500,
            'status' => 'partial',
        ]);

        MonthlyDue::query()->create([
            'user_id' => $unpaidMember->id,
            'due_month' => $month,
            'expected_amount' => 1000,
            'paid_amount' => 0,
            'status' => 'unpaid',
        ]);

        $this->actingAs($finance)
            ->post(route('reports.monthly-dues.remind-unpaid'), [
                'month' => now()->startOfMonth()->format('Y-m'),
            ])
            ->assertRedirect();

        $this->assertSame(0, $paidMember->fresh()->notifications()->count());
        $this->assertSame(1, $partialMember->fresh()->notifications()->count());
        $this->assertSame(1, $unpaidMember->fresh()->notifications()->count());
    }

    public function test_admin_can_close_month_from_dashboard(): void
    {
        $admin = User::factory()->create([
            'status' => 'active',
            'role' => 'admin',
        ]);

        $month = now()->startOfMonth()->format('Y-m');

        $this->actingAs($admin)
            ->post(route('reports.monthly-dues.close-month'), [
                'month' => $month,
                'note' => 'Reconciled',
            ])
            ->assertRedirect(route('reports.monthly-dues.index', ['month' => $month]));

        $this->assertDatabaseHas('closed_periods', [
            'month' => now()->startOfMonth()->toDateString(),
            'closed_by' => $admin->id,
            'note' => 'Reconciled',
        ]);

        $this->assertTrue(ClosedPeriod::query()->whereDate('month', now()->startOfMonth()->toDateString())->exists());
    }
}
