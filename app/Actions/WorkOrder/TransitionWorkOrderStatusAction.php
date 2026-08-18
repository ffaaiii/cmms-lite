<?php

namespace App\Actions\WorkOrder;

use App\Models\WorkOrder;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class TransitionWorkOrderStatusAction
{
    // Peta transisi valid — satu-satunya sumber kebenaran state machine
    // untuk aksi Teknisi (bukan approve/reject, itu Action terpisah).
    private const ALLOWED_TRANSITIONS = [
        'assigned' => 'in_progress',
        'in_progress' => 'completed',
    ];

    /**
     * @param  array{part_name: string, quantity: int, unit: ?string}[]  $parts
     */
    public function execute(WorkOrder $workOrder, string $toStatus, int $actorId, ?string $note = null, array $parts = []): WorkOrder
    {
        $fromStatus = $workOrder->status;

        // Guard defense-in-depth — Policy di controller sudah cek ownership,
        // tapi Action tetap validasi state machine sendiri supaya aman kalau
        // dipanggil dari jalur lain (Command/Job) di masa depan.
        if (! isset(self::ALLOWED_TRANSITIONS[$fromStatus]) || self::ALLOWED_TRANSITIONS[$fromStatus] !== $toStatus) {
            throw new InvalidArgumentException("Transisi status dari '{$fromStatus}' ke '{$toStatus}' tidak valid.");
        }

        return DB::transaction(function () use ($workOrder, $fromStatus, $toStatus, $actorId, $note, $parts) {
            $updateData = ['status' => $toStatus];

            if ($toStatus === 'completed') {
                $updateData['completed_at'] = now();
            }

            $workOrder->update($updateData);

            foreach ($parts as $part) {
                $workOrder->parts()->create($part);
            }

            $workOrder->logs()->create([
                'user_id' => $actorId,
                'from_status' => $fromStatus,
                'to_status' => $toStatus,
                'note' => $note,
            ]);

            return $workOrder->fresh();
        });
    }
}