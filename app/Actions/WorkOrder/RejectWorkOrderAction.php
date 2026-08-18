<?php

namespace App\Actions\WorkOrder;

use App\Models\WorkOrder;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class RejectWorkOrderAction
{
    // Threshold eskalasi (ADR-004) — 2x reject berturut-turut.
    // "Berturut-turut" disederhanakan jadi rejection_count total,
    // karena closed adalah status akhir (tidak ada reject setelah approve).
    private const ESCALATION_THRESHOLD = 2;

    public function execute(WorkOrder $workOrder, int $actorId, string $rejectionNote): WorkOrder
    {
        if ($workOrder->status !== 'completed') {
            throw new InvalidArgumentException('Hanya work order berstatus completed yang bisa di-reject.');
        }

        return DB::transaction(function () use ($workOrder, $actorId, $rejectionNote) {
            $newRejectionCount = $workOrder->rejection_count + 1;
            $isEscalated = $newRejectionCount >= self::ESCALATION_THRESHOLD;

            $workOrder->update([
                'status' => 'in_progress',
                'rejection_count' => $newRejectionCount,
                'rejection_note' => $rejectionNote,
                'is_escalated' => $isEscalated,
                'completed_at' => null, // WO aktif lagi, belum selesai
            ]);

            $workOrder->logs()->create([
                'user_id' => $actorId,
                'from_status' => 'completed',
                'to_status' => 'in_progress',
                'note' => "Ditolak: {$rejectionNote}",
            ]);

            // Notifikasi in-app ke semua Supervisor DITUNDA ke M4/M7 —
            // sistem Notification belum dibangun. Untuk sekarang eskalasi
            // cukup tercermin dari is_escalated=true + log di atas.

            return $workOrder->fresh();
        });
    }
}