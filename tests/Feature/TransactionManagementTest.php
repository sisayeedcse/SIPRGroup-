<?php

namespace Tests\Feature;

use App\Models\Transaction;
use App\Models\User;
use App\Models\Wallet;
use App\Services\PeriodLockService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TransactionManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_active_member_can_view_transactions_page(): void
    {
        $member = User::factory()->create([
            'role' => 'member',
            'status' => 'active',
        ]);

        $this->actingAs($member)
            ->get(route('transactions.index'))
            ->assertOk()
            ->assertSee('Transactions');
    }

    public function test_member_cannot_create_transactions(): void
    {
        $member = User::factory()->create([
            'role' => 'member',
            'status' => 'active',
        ]);

        $target = User::factory()->create([
            'role' => 'member',
            'status' => 'active',
        ]);

        $this->actingAs($member)
            ->post(route('transactions.store'), [
                'user_id' => $target->id,
                'type' => 'deposit',
                'amount' => 100,
                'date' => now()->toDateString(),
            ])
            ->assertForbidden();
    }

    public function test_finance_can_create_transaction_and_wallet_is_updated(): void
    {
        $finance = User::factory()->create([
            'role' => 'finance',
            'status' => 'active',
        ]);

        $target = User::factory()->create([
            'role' => 'member',
            'status' => 'active',
        ]);

        Wallet::query()->create([
            'user_id' => $target->id,
            'available' => 0,
            'locked' => 0,
        ]);

        $this->actingAs($finance)
            ->post(route('transactions.store'), [
                'user_id' => $target->id,
                'type' => 'deposit',
                'amount' => 150,
                'date' => now()->toDateString(),
                'note' => 'Monthly collection',
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('transactions', [
            'user_id' => $target->id,
            'type' => 'deposit',
        ]);

        $this->assertDatabaseHas('wallets', [
            'user_id' => $target->id,
            'available' => 150.00,
        ]);

        $this->assertDatabaseHas('activities', [
            'action' => 'tx-create',
            'user_id' => $finance->id,
            'role' => 'finance',
        ]);
    }

    public function test_admin_can_update_transaction_and_wallet_is_recalculated(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
            'status' => 'active',
        ]);

        $target = User::factory()->create([
            'role' => 'member',
            'status' => 'active',
        ]);

        Wallet::query()->create([
            'user_id' => $target->id,
            'available' => 100,
            'locked' => 0,
        ]);

        $transaction = Transaction::query()->create([
            'user_id' => $target->id,
            'type' => 'deposit',
            'amount' => 100,
            'date' => now()->toDateString(),
            'note' => 'Initial',
        ]);

        $this->actingAs($admin)
            ->put(route('transactions.update', $transaction), [
                'user_id' => $target->id,
                'type' => 'expense',
                'amount' => 25,
                'date' => now()->toDateString(),
                'note' => 'Stationery',
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('transactions', [
            'id' => $transaction->id,
            'type' => 'expense',
            'amount' => 25.00,
        ]);

        $this->assertDatabaseHas('wallets', [
            'user_id' => $target->id,
            'available' => -25.00,
        ]);

        $this->assertDatabaseHas('activities', [
            'action' => 'tx-update',
            'user_id' => $admin->id,
            'role' => 'admin',
        ]);
    }

    public function test_admin_can_delete_transaction_and_wallet_is_reverted(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
            'status' => 'active',
        ]);

        $target = User::factory()->create([
            'role' => 'member',
            'status' => 'active',
        ]);

        Wallet::query()->create([
            'user_id' => $target->id,
            'available' => 300,
            'locked' => 0,
        ]);

        $transaction = Transaction::query()->create([
            'user_id' => $target->id,
            'type' => 'deposit',
            'amount' => 200,
            'date' => now()->toDateString(),
            'note' => 'One time',
        ]);

        $this->actingAs($admin)
            ->delete(route('transactions.destroy', $transaction))
            ->assertRedirect();

        $this->assertDatabaseMissing('transactions', [
            'id' => $transaction->id,
        ]);

        $this->assertDatabaseHas('wallets', [
            'user_id' => $target->id,
            'available' => 100.00,
        ]);

        $this->assertDatabaseHas('activities', [
            'action' => 'tx-delete',
            'user_id' => $admin->id,
            'role' => 'admin',
        ]);
    }

    public function test_finance_can_create_adjustment_without_modifying_original_transaction(): void
    {
        $finance = User::factory()->create([
            'role' => 'finance',
            'status' => 'active',
        ]);

        $target = User::factory()->create([
            'role' => 'member',
            'status' => 'active',
        ]);

        Wallet::query()->create([
            'user_id' => $target->id,
            'available' => 300,
            'locked' => 0,
        ]);

        $original = Transaction::query()->create([
            'user_id' => $target->id,
            'type' => 'deposit',
            'amount' => 300,
            'date' => now()->toDateString(),
            'note' => 'Initial deposit',
        ]);

        $this->actingAs($finance)
            ->post(route('transactions.adjust', $original), [
                'type' => 'expense',
                'amount' => 50,
                'date' => now()->toDateString(),
                'note' => 'Correction adjustment',
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('transactions', [
            'id' => $original->id,
            'type' => 'deposit',
            'amount' => 300.00,
            'is_adjustment' => false,
        ]);

        $this->assertDatabaseHas('transactions', [
            'adjustment_for_id' => $original->id,
            'user_id' => $target->id,
            'type' => 'expense',
            'amount' => 50.00,
            'is_adjustment' => true,
            'approval_status' => 'approved',
        ]);

        $this->assertDatabaseHas('wallets', [
            'user_id' => $target->id,
            'available' => 250.00,
        ]);

        $this->assertDatabaseHas('activities', [
            'action' => 'tx-adjust',
            'user_id' => $finance->id,
            'role' => 'finance',
        ]);
    }

    public function test_high_value_adjustment_by_finance_is_pending_until_admin_approval(): void
    {
        $finance = User::factory()->create([
            'role' => 'finance',
            'status' => 'active',
        ]);

        $admin = User::factory()->create([
            'role' => 'admin',
            'status' => 'active',
        ]);

        $target = User::factory()->create([
            'role' => 'member',
            'status' => 'active',
        ]);

        Wallet::query()->create([
            'user_id' => $target->id,
            'available' => 500,
            'locked' => 0,
        ]);

        $original = Transaction::query()->create([
            'user_id' => $target->id,
            'type' => 'deposit',
            'amount' => 500,
            'date' => now()->toDateString(),
            'note' => 'Base',
        ]);

        $this->actingAs($finance)
            ->post(route('transactions.adjust', $original), [
                'type' => 'expense',
                'amount' => 1200,
                'date' => now()->toDateString(),
                'note' => 'High value correction',
            ])
            ->assertRedirect();

        $pending = Transaction::query()
            ->where('adjustment_for_id', $original->id)
            ->firstOrFail();

        $this->assertSame('pending', $pending->approval_status);
        $this->assertTrue((bool) $pending->requires_approval);

        $this->assertDatabaseHas('wallets', [
            'user_id' => $target->id,
            'available' => 500.00,
        ]);

        $this->assertDatabaseHas('activities', [
            'action' => 'tx-adjust-request',
            'user_id' => $finance->id,
        ]);

        $this->actingAs($admin)
            ->post(route('transactions.adjustments.approve', $pending), [
                'approval_note' => 'Validated',
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('transactions', [
            'id' => $pending->id,
            'approval_status' => 'approved',
            'approved_by' => $admin->id,
        ]);

        $this->assertDatabaseHas('wallets', [
            'user_id' => $target->id,
            'available' => -700.00,
        ]);

        $this->assertDatabaseHas('activities', [
            'action' => 'tx-adjust-approve',
            'user_id' => $admin->id,
        ]);
    }

    public function test_admin_can_reject_pending_high_value_adjustment_without_wallet_change(): void
    {
        $finance = User::factory()->create([
            'role' => 'finance',
            'status' => 'active',
        ]);

        $admin = User::factory()->create([
            'role' => 'admin',
            'status' => 'active',
        ]);

        $target = User::factory()->create([
            'role' => 'member',
            'status' => 'active',
        ]);

        Wallet::query()->create([
            'user_id' => $target->id,
            'available' => 800,
            'locked' => 0,
        ]);

        $original = Transaction::query()->create([
            'user_id' => $target->id,
            'type' => 'deposit',
            'amount' => 800,
            'date' => now()->toDateString(),
            'note' => 'Base',
        ]);

        $this->actingAs($finance)
            ->post(route('transactions.adjust', $original), [
                'type' => 'expense',
                'amount' => 1500,
                'date' => now()->toDateString(),
                'note' => 'Needs approval',
            ])
            ->assertRedirect();

        $pending = Transaction::query()
            ->where('adjustment_for_id', $original->id)
            ->firstOrFail();

        $this->actingAs($admin)
            ->post(route('transactions.adjustments.reject', $pending), [
                'approval_note' => 'Invalid request',
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('transactions', [
            'id' => $pending->id,
            'approval_status' => 'rejected',
            'approved_by' => $admin->id,
        ]);

        $this->assertDatabaseHas('wallets', [
            'user_id' => $target->id,
            'available' => 800.00,
        ]);

        $this->assertDatabaseHas('activities', [
            'action' => 'tx-adjust-reject',
            'user_id' => $admin->id,
        ]);
    }

    public function test_finance_cannot_create_transaction_in_closed_month(): void
    {
        $admin = User::factory()->create(['role' => 'admin', 'status' => 'active']);
        $finance = User::factory()->create(['role' => 'finance', 'status' => 'active']);
        $target = User::factory()->create(['role' => 'member', 'status' => 'active']);

        app(PeriodLockService::class)->closeMonth(now()->format('Y-m'), $admin->id, 'Month close');

        $this->actingAs($finance)
            ->post(route('transactions.store'), [
                'user_id' => $target->id,
                'type' => 'deposit',
                'amount' => 100,
                'date' => now()->toDateString(),
                'note' => 'Blocked by close',
            ])
            ->assertSessionHasErrors('date');
    }

    public function test_admin_can_create_transaction_in_closed_month_as_override(): void
    {
        $admin = User::factory()->create(['role' => 'admin', 'status' => 'active']);
        $target = User::factory()->create(['role' => 'member', 'status' => 'active']);

        app(PeriodLockService::class)->closeMonth(now()->format('Y-m'), $admin->id, 'Month close');

        $this->actingAs($admin)
            ->post(route('transactions.store'), [
                'user_id' => $target->id,
                'type' => 'deposit',
                'amount' => 100,
                'date' => now()->toDateString(),
                'note' => 'Admin override',
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('transactions', [
            'user_id' => $target->id,
            'type' => 'deposit',
            'amount' => 100.00,
        ]);
    }

    public function test_finance_cannot_approve_pending_adjustment(): void
    {
        $admin = User::factory()->create(['role' => 'admin', 'status' => 'active']);
        $finance = User::factory()->create(['role' => 'finance', 'status' => 'active']);
        $member = User::factory()->create(['role' => 'member', 'status' => 'active']);

        Wallet::query()->create([
            'user_id' => $member->id,
            'available' => 500,
            'locked' => 0,
        ]);

        $original = Transaction::query()->create([
            'user_id' => $member->id,
            'type' => 'deposit',
            'amount' => 500,
            'date' => now()->toDateString(),
            'note' => 'Base',
        ]);

        $this->actingAs($finance)
            ->post(route('transactions.adjust', $original), [
                'type' => 'expense',
                'amount' => 1500,
                'date' => now()->toDateString(),
                'note' => 'Pending',
            ])
            ->assertRedirect();

        $pending = Transaction::query()->where('adjustment_for_id', $original->id)->firstOrFail();

        $this->actingAs($finance)
            ->post(route('transactions.adjustments.approve', $pending), [
                'approval_note' => 'Trying self-approve',
            ])
            ->assertForbidden();

        $this->actingAs($admin)
            ->post(route('transactions.adjustments.approve', $pending), [
                'approval_note' => 'Admin approve',
            ])
            ->assertRedirect();
    }

    public function test_finance_cannot_update_or_delete_or_adjust_in_closed_month(): void
    {
        $admin = User::factory()->create(['role' => 'admin', 'status' => 'active']);
        $finance = User::factory()->create(['role' => 'finance', 'status' => 'active']);
        $member = User::factory()->create(['role' => 'member', 'status' => 'active']);

        Wallet::query()->create([
            'user_id' => $member->id,
            'available' => 400,
            'locked' => 0,
        ]);

        $transaction = Transaction::query()->create([
            'user_id' => $member->id,
            'type' => 'deposit',
            'amount' => 400,
            'date' => now()->toDateString(),
            'note' => 'Current month tx',
        ]);

        app(PeriodLockService::class)->closeMonth(now()->format('Y-m'), $admin->id, 'Locked');

        $this->actingAs($finance)
            ->put(route('transactions.update', $transaction), [
                'user_id' => $member->id,
                'type' => 'deposit',
                'amount' => 450,
                'date' => now()->toDateString(),
                'note' => 'Try update',
            ])
            ->assertSessionHasErrors('date');

        $this->actingAs($finance)
            ->delete(route('transactions.destroy', $transaction))
            ->assertSessionHasErrors('date');

        $this->actingAs($finance)
            ->post(route('transactions.adjust', $transaction), [
                'type' => 'expense',
                'amount' => 20,
                'date' => now()->toDateString(),
                'note' => 'Try adjust',
            ])
            ->assertSessionHasErrors('date');
    }
}
