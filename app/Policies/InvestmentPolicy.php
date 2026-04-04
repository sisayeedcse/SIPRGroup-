<?php

namespace App\Policies;

use App\Models\Investment;
use App\Models\User;

class InvestmentPolicy
{
    public function view(User $user, Investment $investment): bool
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

        return in_array($role, ['admin', 'finance', 'secretary'], true);
    }

    public function update(User $user, Investment $investment): bool
    {
        return $this->create($user);
    }

    public function delete(User $user, Investment $investment): bool
    {
        return $this->create($user);
    }

    public function createMilestone(User $user, Investment $investment): bool
    {
        return $this->create($user);
    }

    public function createCollection(User $user, Investment $investment): bool
    {
        return $this->create($user);
    }
}
