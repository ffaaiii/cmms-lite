<?php

namespace App\Policies;

use App\Models\User;

class InspectionChecklistPolicy
{
    public function viewAny(User $user): bool
    {
        return in_array($user->role->slug, ['supervisor', 'technician'], true);
    }

    public function create(User $user): bool
    {
        return $user->hasRole('technician');
    }

    // Confirm/dismiss usulan WO dari checklist — hanya Supervisor
    public function review(User $user): bool
    {
        return $user->hasRole('supervisor');
    }
}
