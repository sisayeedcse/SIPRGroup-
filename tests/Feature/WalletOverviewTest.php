<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Wallet;
use App\Models\WalletHistory;
use App\Models\Transaction;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WalletOverviewTest extends TestCase
{
    use RefreshDatabase;

    public function test_active_user_can_open_wallet_index(): void
    {
        $user = User::factory()->create([
            'status' => 'active',
        ]);

        $this->actingAs($user)
            ->get(route('wallets.index'))
            ->assertOk()
            ->assertSee('Wallet Overview');
    }

    public function test_wallet_passbook_preview_shows_history_for_selected_member(): void
    {
        $viewer = User::factory()->create([
            'status' => 'active',
            'role' => 'finance',
        ]);

        $member = User::factory()->create([
            'status' => 'active',
        ]);

        $wallet = Wallet::query()->create([
            'user_id' => $member->id,
            'available' => 350,
            'locked' => 40,
        ]);

        WalletHistory::query()->create([
            'wallet_id' => $wallet->id,
            'date' => now()->toDateString(),
            'type' => 'credit',
            'label' => 'Transaction created',
            'amount' => 150,
            'note' => 'deposit',
            'is_locked' => false,
        ]);

        $this->actingAs($viewer)
            ->get(route('wallets.index', ['user_id' => $member->id]))
            ->assertOk()
            ->assertSee($member->name)
            ->assertSee('Transaction created');
    }

    public function test_wallet_preview_shows_savings_and_invested_totals(): void
    {
        $viewer = User::factory()->create([
            'status' => 'active',
            'role' => 'finance',
        ]);

        $member = User::factory()->create([
            'status' => 'active',
            'role' => 'member',
        ]);

        Wallet::query()->create([
            'user_id' => $member->id,
            'available' => 800,
            'locked' => 50,
        ]);

        Transaction::query()->create([
            'user_id' => $member->id,
            'type' => 'deposit',
            'amount' => 1000,
            'date' => now()->toDateString(),
            'note' => 'monthly savings',
        ]);

        Transaction::query()->create([
            'user_id' => $member->id,
            'type' => 'investment',
            'amount' => 200,
            'date' => now()->toDateString(),
            'note' => 'monthly investment',
        ]);

        $this->actingAs($viewer)
            ->get(route('wallets.index', ['user_id' => $member->id]))
            ->assertOk()
            ->assertSee('Savings Total')
            ->assertSee('Invested Total')
            ->assertSee('1,000.00')
            ->assertSee('200.00');
    }
}
