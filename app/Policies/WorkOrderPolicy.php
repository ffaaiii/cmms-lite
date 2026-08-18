<?php

namespace App\Policies;

use App\Models\User;
use App\Models\WorkOrder;

class WorkOrderPolicy
{
    public function viewAny(User $user): bool
    {
        return in_array($user->role->slug, ['supervisor', 'technician'], true);
    }

    public function view(User $user, WorkOrder $workOrder): bool
    {
        if ($user->hasRole('supervisor')) {
            return true;
        }

        if ($user->hasRole('technician')) {
            return $workOrder->assigned_to === $user->id;
        }

        return false;
    }

    public function create(User $user): bool
    {
        return $user->hasRole('supervisor');
    }

    public function assign(User $user): bool
    {
        return $user->hasRole('supervisor');
    }

    // Update field umum (deskripsi, prioritas) — hanya Supervisor.
    // Update STATUS oleh Teknisi ditangani policy method terpisah (3b),
    // supaya tidak tercampur dengan update field administratif ini.
    public function update(User $user): bool
    {
        return $user->hasRole('supervisor');
    }
}