<?php

namespace App\Actions\Maintenance;

use App\Models\InspectionChecklist;
use App\Models\WorkOrder;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class GenerateWorkOrderFromChecklistAction
{
    private const PRIORITY_MAP = [
        'damaged' => 'urgent',
        'needs_attention' => 'normal',
    ];

    public function confirm(InspectionChecklist $checklist, int $supervisorId): InspectionChecklist
    {
        if ($checklist->status !== 'pending_review') {
            throw new InvalidArgumentException('Hanya checklist berstatus pending_review yang bisa dikonfirmasi.');
        }

        return DB::transaction(function () use ($checklist, $supervisorId) {
            $workOrder = WorkOrder::create([
                'asset_id' => $checklist->asset_id,
                'created_by' => $supervisorId,
                'type' => 'corrective',
                'priority' => self::PRIORITY_MAP[$checklist->condition_found] ?? 'normal',
                'status' => 'draft',
                'description' => "Ditemukan dari inspeksi ({$checklist->condition_found}): {$checklist->notes}",
            ]);

            $workOrder->logs()->create([
                'user_id' => $supervisorId,
                'from_status' => null,
                'to_status' => 'draft',
                'note' => "Digenerate dari usulan checklist inspeksi #{$checklist->id}.",
            ]);

            $checklist->update([
                'status' => 'confirmed',
                'reviewed_by' => $supervisorId,
                'generated_work_order_id' => $workOrder->id,
            ]);

            return $checklist->fresh();
        });
    }

    public function dismiss(InspectionChecklist $checklist, int $supervisorId): InspectionChecklist
    {
        if ($checklist->status !== 'pending_review') {
            throw new InvalidArgumentException('Hanya checklist berstatus pending_review yang bisa ditolak.');
        }

        $checklist->update([
            'status' => 'dismissed',
            'reviewed_by' => $supervisorId,
        ]);

        return $checklist->fresh();
    }
}
