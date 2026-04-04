<?php

namespace App\Policies;

use App\Models\Announcement;
use App\Models\User;

class AnnouncementPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->status === 'active';
    }

    public function create(User $user): bool
    {
        $role = $user->role->value ?? $user->role;

        return in_array($role, ['admin', 'secretary'], true);
    }

    public function update(User $user, Announcement $announcement): bool
    {
        return $this->create($user);
    }

    public function delete(User $user, Announcement $announcement): bool
    {
        return $this->create($user);
    }
}
