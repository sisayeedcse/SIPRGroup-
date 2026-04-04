<?php

namespace App\Policies;

use App\Models\Proposal;
use App\Models\User;

class ProposalPolicy
{
    public function view(User $user, Proposal $proposal): bool
    {
        return $user->status === 'active';
    }

    public function viewAny(User $user): bool
    {
        return $user->status === 'active';
    }

    public function create(User $user): bool
    {
        return $user->status === 'active';
    }

    public function update(User $user, Proposal $proposal): bool
    {
        if ($proposal->finalized_at !== null) {
            return false;
        }

        $role = $user->role->value ?? $user->role;

        return in_array($role, ['admin', 'finance', 'secretary'], true) || (int) $proposal->proposed_by === $user->id;
    }

    public function vote(User $user, Proposal $proposal): bool
    {
        return $user->status === 'active';
    }

    public function updateStatus(User $user, Proposal $proposal): bool
    {
        $role = $user->role->value ?? $user->role;

        return in_array($role, ['admin', 'finance', 'secretary'], true);
    }

    public function finalize(User $user, Proposal $proposal): bool
    {
        return $this->updateStatus($user, $proposal);
    }
}
