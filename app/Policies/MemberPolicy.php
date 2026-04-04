<?php

namespace App\Policies;

use App\Models\User;

class MemberPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->status === 'active';
    }

    public function update(User $user, User $member): bool
    {
        $role = $user->role->value ?? $user->role;

        return $role === 'admin';
    }
}
