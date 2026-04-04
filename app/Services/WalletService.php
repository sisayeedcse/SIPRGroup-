<?php

namespace App\Services;

use App\Models\Transaction;
use App\Models\Wallet;
use App\Models\WalletHistory;

class WalletService
{
    public function applyNewTransaction(Transaction $transaction): void
    {
        $delta = $this->deltaFor($transaction->type, (float) $transaction->amount);
        $wallet = $this->walletFor($transaction->user_id);

        $wallet->update([
            'available' => (float) $wallet->available + $delta,
        ]);

        $this->writeHistory($wallet->id, $transaction, $delta, 'Transaction created');
    }

    public function replaceTransaction(Transaction $old, Transaction $new): void
    {
        $oldDelta = $this->deltaFor($old->type, (float) $old->amount);
        $newDelta = $this->deltaFor($new->type, (float) $new->amount);

        if ($old->user_id === $new->user_id) {
            $wallet = $this->walletFor($new->user_id);
            $wallet->update([
                'available' => (float) $wallet->available - $oldDelta + $newDelta,
            ]);

            $this->writeHistory($wallet->id, $new, $newDelta - $oldDelta, 'Transaction updated');

            return;
        }

        $oldWallet = $this->walletFor($old->user_id);
        $oldWallet->update([
            'available' => (float) $oldWallet->available - $oldDelta,
        ]);
        $this->writeHistory($oldWallet->id, $old, -$oldDelta, 'Transaction moved from wallet');

        $newWallet = $this->walletFor($new->user_id);
        $newWallet->update([
            'available' => (float) $newWallet->available + $newDelta,
        ]);
        $this->writeHistory($newWallet->id, $new, $newDelta, 'Transaction moved to wallet');
    }

    public function removeTransaction(Transaction $transaction): void
    {
        $delta = $this->deltaFor($transaction->type, (float) $transaction->amount);
        $wallet = $this->walletFor($transaction->user_id);

        $wallet->update([
            'available' => (float) $wallet->available - $delta,
        ]);

        $this->writeHistory($wallet->id, $transaction, -$delta, 'Transaction deleted');
    }

    private function walletFor(int $userId): Wallet
    {
        return Wallet::query()->firstOrCreate(
            ['user_id' => $userId],
            ['available' => 0, 'locked' => 0]
        );
    }

    private function deltaFor(string $type, float $amount): float
    {
        return match ($type) {
            'deposit', 'profit' => $amount,
            'investment', 'expense', 'fine' => -$amount,
            default => 0,
        };
    }

    private function writeHistory(int $walletId, Transaction $transaction, float $delta, string $label): void
    {
        WalletHistory::query()->create([
            'wallet_id' => $walletId,
            'date' => $transaction->date,
            'type' => $delta >= 0 ? 'credit' : 'debit',
            'label' => $label,
            'amount' => abs($delta),
            'note' => trim(($transaction->type ?? 'transaction').' '.($transaction->note ?? '')),
            'is_locked' => false,
        ]);
    }
}
