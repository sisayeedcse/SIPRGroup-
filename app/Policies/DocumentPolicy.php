<?php

namespace App\Policies;

use App\Models\Document;
use App\Models\User;

class DocumentPolicy
{
    public function view(User $user, Document $document): bool
    {
        return $this->viewAny($user);
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

    public function delete(User $user, Document $document): bool
    {
        $role = $user->role->value ?? $user->role;

        return in_array($role, ['admin', 'secretary'], true);
    }
}
