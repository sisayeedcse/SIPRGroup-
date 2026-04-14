<?php

namespace App\Policies;

use App\Models\Transaction;
use App\Models\User;

class TransactionPolicy
{
    public function view(User $user, Transaction $transaction): bool
    {
        if ($user->status !== 'active') {
            return false;
        }

        $role = $user->role->value ?? $user->role;

        if (in_array($role, ['admin', 'finance', 'secretary', 'advisor'], true)) {
            return true;
        }

        return $transaction->user_id === $user->id;
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
        $role = $user->role->value ?? $user->role;

        // Only admins can approve/reject adjustments
        return $role === 'admin';
    }
}
