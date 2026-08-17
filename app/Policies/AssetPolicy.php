<?php

namespace App\Policies;

use App\Models\User;

class AssetPolicy
{
    public function viewAny(User $user): bool
    {
        return in_array($user->role->slug, ['admin', 'supervisor', 'technician'], true);
    }

    public function create(User $user): bool
    {
        return $user->hasRole('admin');
    }

    public function update(User $user): bool
    {
        return $user->hasRole('admin');
    }

    public function updateCondition(User $user): bool
    {
        return $user->hasRole('supervisor');
    }

    public function delete(User $user): bool
    {
        return $user->hasRole('admin');
    }
}
