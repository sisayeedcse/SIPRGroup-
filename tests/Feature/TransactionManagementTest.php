<?php

namespace Tests\Feature;

use App\Models\Transaction;
use App\Models\User;
use App\Models\Wallet;
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
}
