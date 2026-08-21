<?php

namespace App\Actions\WorkOrder;

use App\Models\User;
use App\Models\WorkOrder;
use App\Support\Notifier;
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

            // Kirim notifikasi ke Teknisi bahwa WO ditolak
            if ($workOrder->technician) {
                Notifier::notify(
                    $workOrder->technician,
                    'wo_rejected',
                    "Work order untuk aset \"{$workOrder->asset->name}\" ditolak: {$rejectionNote}",
                    $workOrder->id,
                );
            }

            // Kirim notifikasi eskalasi ke semua Supervisor jika threshold tercapai
            if ($isEscalated) {
                $supervisors = User::whereHas('role', fn ($q) => $q->where('slug', 'supervisor'))->get();

                Notifier::notifyMany(
                    $supervisors,
                    'wo_escalated',
                    "Work order untuk aset \"{$workOrder->asset->name}\" perlu eskalasi (sudah {$newRejectionCount}x ditolak).",
                    $workOrder->id,
                );
            }

            return $workOrder->fresh();
        });
    }
}
