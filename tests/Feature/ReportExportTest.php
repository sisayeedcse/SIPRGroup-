<?php

namespace Tests\Feature;

use App\Models\Transaction;
use App\Models\User;
use App\Models\Wallet;
use App\Models\WalletHistory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReportExportTest extends TestCase
{
    use RefreshDatabase;

    public function test_finance_can_open_reports_page(): void
    {
        $finance = User::factory()->create([
            'status' => 'active',
            'role' => 'finance',
        ]);

        $this->actingAs($finance)
            ->get(route('reports.index'))
            ->assertOk()
            ->assertSee('Reports');
    }

    public function test_member_cannot_access_reports_page(): void
    {
        $member = User::factory()->create([
            'status' => 'active',
            'role' => 'member',
        ]);

        $this->actingAs($member)
            ->get(route('reports.index'))
            ->assertForbidden();
    }

    public function test_finance_can_download_transactions_csv(): void
    {
        $finance = User::factory()->create([
            'status' => 'active',
            'role' => 'finance',
        ]);

        $target = User::factory()->create([
            'status' => 'active',
            'role' => 'member',
        ]);

        Transaction::query()->create([
            'user_id' => $target->id,
            'type' => 'deposit',
            'amount' => 200,
            'date' => now()->toDateString(),
            'note' => 'report-test',
        ]);

        $response = $this->actingAs($finance)
            ->get(route('reports.transactions.csv'));

        $response->assertOk();
        $response->assertHeader('content-type', 'text/csv; charset=UTF-8');
    }

    public function test_finance_can_download_wallet_passbook_pdf(): void
    {
        $finance = User::factory()->create([
            'status' => 'active',
            'role' => 'finance',
        ]);

        $target = User::factory()->create([
            'status' => 'active',
            'role' => 'member',
        ]);

        $wallet = Wallet::query()->create([
            'user_id' => $target->id,
            'available' => 300,
            'locked' => 25,
        ]);

        WalletHistory::query()->create([
            'wallet_id' => $wallet->id,
            'date' => now()->toDateString(),
            'type' => 'credit',
            'label' => 'Transaction created',
            'amount' => 150,
            'note' => 'report-entry',
            'is_locked' => false,
        ]);

        $response = $this->actingAs($finance)
            ->get(route('reports.wallet.passbook.pdf', ['user_id' => $target->id]));

        $response->assertOk();
        $response->assertHeader('content-type', 'application/pdf');
    }
}
