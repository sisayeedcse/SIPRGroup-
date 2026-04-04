<?php

namespace App\Policies;

use App\Models\Transaction;
use App\Models\User;

class TransactionPolicy
{
    public function view(User $user, Transaction $transaction): bool
    {
        return $user->status === 'active';
    }

    public function viewAny(User $user): bool
    {
        return $user->status === 'active';
    }

    public function create(User $user): bool
    {
        $role = $user->role->value ?? $user->role;

        return in_array($role, ['admin', 'finance'], true);
    }

    public function update(User $user, Transaction $transaction): bool
    {
        return $this->create($user);
    }

    public function delete(User $user, Transaction $transaction): bool
    {
        return $this->create($user);
    }
}
