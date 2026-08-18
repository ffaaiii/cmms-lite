<?php

namespace App\Actions\Maintenance;

use App\Models\Asset;
use App\Models\Notification;
use App\Models\User;
use App\Models\WorkOrder;
use Illuminate\Support\Facades\DB;

class GeneratePreventiveScheduleAction
{
    public function execute(): array
    {
        $systemUser = User::where('email', 'system@cmms-lite.local')->firstOrFail();
        $generated = [];

        // Ambil semua aset aktif, lalu filter yang jatuh tempo di PHP
        $dueAssets = Asset::where('status', 'active')
            ->get()
            ->filter(fn (Asset $asset) => $this->isDue($asset));

        foreach ($dueAssets as $asset) {
            // Guard anti-duplikat: skip kalau aset masih punya WO preventive
            // yang belum closed (siklus sebelumnya belum selesai).
            $hasOpenPreventiveWo = WorkOrder::where('asset_id', $asset->id)
                ->where('type', 'preventive')
                ->where('status', '!=', 'closed')
                ->exists();

            if ($hasOpenPreventiveWo) {
                continue;
            }

            $workOrder = DB::transaction(function () use ($asset, $systemUser) {
                $wo = WorkOrder::create([
                    'asset_id' => $asset->id,
                    'created_by' => $systemUser->id,
                    'type' => 'preventive',
                    'priority' => 'normal',
                    'status' => 'draft',
                    'description' => "Rencana PM otomatis — interval {$asset->pm_interval_days} hari terlampaui.",
                    'scheduled_at' => now()->toDateString(),
                ]);

                $wo->logs()->create([
                    'user_id' => $systemUser->id,
                    'from_status' => null,
                    'to_status' => 'draft',
                    'note' => 'Digenerate otomatis oleh sistem (jadwal PM jatuh tempo).',
                ]);

                return $wo;
            });

            $this->notifySupervisors($asset, $workOrder);

            $generated[] = $workOrder;
        }

        return $generated;
    }

    /**
     * Cek apakah aset sudah jatuh tempo PM
     */
    private function isDue(Asset $asset): bool
    {
        if ($asset->last_pm_at === null) {
            return true;
        }

        return $asset->last_pm_at->copy()->addDays($asset->pm_interval_days)->lte(now());
    }

    private function notifySupervisors(Asset $asset, WorkOrder $workOrder): void
    {
        $supervisors = User::whereHas('role', fn ($q) => $q->where('slug', 'supervisor'))->get();

        foreach ($supervisors as $supervisor) {
            Notification::create([
                'user_id' => $supervisor->id,
                'type' => 'pm_due',
                'message' => "PM jatuh tempo untuk aset \"{$asset->name}\" — rencana WO sudah dibuat, silakan review & assign.",
                'related_work_order_id' => $workOrder->id,
            ]);
        }
    }
}
