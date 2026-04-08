<?php

namespace Tests\Feature;

use App\Models\MonthlyDue;
use App\Models\Transaction;
use App\Models\User;
use App\Services\MonthlyDueService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MonthlyDueServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_due_status_is_unpaid_when_no_contribution_exists(): void
    {
        $member = User::factory()->create(['status' => 'active']);

        $due = app(MonthlyDueService::class)->createOrUpdateDue(
            $member->id,
            now()->startOfMonth()->toDateString(),
            1000
        );

        $this->assertSame('unpaid', $due->status);
        $this->assertSame('0.00', (string) $due->paid_amount);
    }

    public function test_due_status_is_partial_when_paid_amount_is_below_expected(): void
    {
        $member = User::factory()->create(['status' => 'active']);

        Transaction::query()->create([
            'user_id' => $member->id,
            'type' => 'deposit',
            'amount' => 600,
            'date' => now()->startOfMonth()->addDay()->toDateString(),
            'note' => 'monthly deposit',
        ]);

        Transaction::query()->create([
            'user_id' => $member->id,
            'type' => 'investment',
            'amount' => 200,
            'date' => now()->startOfMonth()->addDays(2)->toDateString(),
            'note' => 'monthly investment',
        ]);

        $due = app(MonthlyDueService::class)->createOrUpdateDue(
            $member->id,
            now()->startOfMonth()->toDateString(),
            1000
        );

        $this->assertSame('partial', $due->status);
        $this->assertSame('800.00', (string) $due->paid_amount);
    }

    public function test_due_status_is_paid_when_paid_amount_reaches_expected(): void
    {
        $member = User::factory()->create(['status' => 'active']);

        Transaction::query()->create([
            'user_id' => $member->id,
            'type' => 'deposit',
            'amount' => 1000,
            'date' => now()->startOfMonth()->addDay()->toDateString(),
            'note' => 'monthly deposit',
        ]);

        $due = app(MonthlyDueService::class)->createOrUpdateDue(
            $member->id,
            now()->startOfMonth()->toDateString(),
            1000
        );

        $this->assertSame('paid', $due->status);
        $this->assertSame('1000.00', (string) $due->paid_amount);
    }

    public function test_sync_for_month_updates_existing_due_rows(): void
    {
        $memberA = User::factory()->create(['status' => 'active']);
        $memberB = User::factory()->create(['status' => 'active']);

        $month = now()->startOfMonth()->toDateString();

        MonthlyDue::query()->create([
            'user_id' => $memberA->id,
            'due_month' => $month,
            'expected_amount' => 900,
            'paid_amount' => 0,
            'status' => 'unpaid',
        ]);

        MonthlyDue::query()->create([
            'user_id' => $memberB->id,
            'due_month' => $month,
            'expected_amount' => 700,
            'paid_amount' => 0,
            'status' => 'unpaid',
        ]);

        Transaction::query()->create([
            'user_id' => $memberA->id,
            'type' => 'deposit',
            'amount' => 900,
            'date' => now()->startOfMonth()->addDay()->toDateString(),
            'note' => 'full payment',
        ]);

        Transaction::query()->create([
            'user_id' => $memberB->id,
            'type' => 'deposit',
            'amount' => 400,
            'date' => now()->startOfMonth()->addDay()->toDateString(),
            'note' => 'partial payment',
        ]);

        $updatedCount = app(MonthlyDueService::class)->syncForMonth($month);

        $this->assertSame(2, $updatedCount);

        $this->assertDatabaseHas('monthly_dues', [
            'user_id' => $memberA->id,
            'status' => 'paid',
            'paid_amount' => 900.00,
        ]);

        $this->assertDatabaseHas('monthly_dues', [
            'user_id' => $memberB->id,
            'status' => 'partial',
            'paid_amount' => 400.00,
        ]);
    }
}
