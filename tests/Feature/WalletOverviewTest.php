<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Wallet;
use App\Models\WalletHistory;
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
}
