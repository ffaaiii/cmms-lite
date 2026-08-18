<?php

namespace App\Actions\WorkOrder;

use App\Models\WorkOrder;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class ApproveWorkOrderAction
{
    public function execute(WorkOrder $workOrder, int $actorId): WorkOrder
    {
        if ($workOrder->status !== 'completed') {
            throw new InvalidArgumentException('Hanya work order berstatus completed yang bisa di-approve.');
        }

        return DB::transaction(function () use ($workOrder, $actorId) {
            $workOrder->update([
                'status' => 'closed',
                'approved_by' => $actorId,
                'closed_at' => now(),
            ]);

            // Reset interval PM hanya untuk WO preventive — corrective tidak
            // menggeser jadwal PM terjadwal (disepakati bersama user).
            if ($workOrder->type === 'preventive') {
                $workOrder->asset->update([
                    'last_pm_at' => $workOrder->completed_at,
                ]);
            }

            $workOrder->logs()->create([
                'user_id' => $actorId,
                'from_status' => 'completed',
                'to_status' => 'closed',
                'note' => 'Disetujui oleh supervisor.',
            ]);

            return $workOrder->fresh();
        });
    }
}