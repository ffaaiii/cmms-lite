<?php

namespace App\Actions\Dashboard;

use App\Models\Asset;
use App\Models\WorkOrder;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class CalculateReliabilityMetricsAction
{
    public function execute(Carbon $from, Carbon $to): array
    {
        return [
            'mttr_hours' => $this->calculateMttr($from, $to),
            'mtbf_per_asset' => $this->calculateMtbfPerAsset($from, $to),
            'mtbf_aggregate_hours' => $this->calculateMtbfAggregate($from, $to),
            'downtime_avg_hours' => $this->calculateAverageDowntime($from, $to),
        ];
    }

    private function calculateMttr(Carbon $from, Carbon $to): ?float
    {
        $closedWorkOrders = WorkOrder::where('type', 'corrective')
            ->where('status', 'closed')
            ->whereBetween('closed_at', [$from, $to])
            ->with('logs')
            ->get();

        $durations = $closedWorkOrders
            ->map(function (WorkOrder $wo) {
                // Pencarian log yang fleksibel untuk String maupun Enum Object
                $startedLog = $wo->logs->first(function ($log) {
                    $status = $log->to_status instanceof \BackedEnum
                        ? $log->to_status->value
                        : (string) $log->to_status;

                    return $status === 'in_progress';
                });

                if (! $startedLog || ! $wo->completed_at) {
                    return null;
                }

                $startTime = Carbon::parse($startedLog->created_at);
                $completedTime = Carbon::parse($wo->completed_at);

                return $startTime->diffInMinutes($completedTime);
            })
            ->filter(fn ($minutes) => $minutes !== null && $minutes >= 0);

        if ($durations->isEmpty()) {
            return null;
        }

        return round($durations->avg() / 60, 2);
    }

    private function calculateMtbfPerAsset(Carbon $from, Carbon $to): Collection
    {
        return Asset::all()->map(function (Asset $asset) use ($from, $to) {
            $completedDates = WorkOrder::where('asset_id', $asset->id)
                ->where('type', 'corrective')
                ->where('status', 'closed')
                ->whereBetween('closed_at', [$from, $to])
                ->orderBy('completed_at')
                ->pluck('completed_at');

            $mtbfHours = $this->averageGapInHours($completedDates);

            return [
                'asset_id' => $asset->id,
                'asset_name' => $asset->name,
                'mtbf_hours' => $mtbfHours,
                'failure_count' => $completedDates->count(),
            ];
        })->filter(fn ($row) => $row['failure_count'] > 0)->values();
    }

    private function calculateMtbfAggregate(Carbon $from, Carbon $to): ?float
    {
        $perAsset = $this->calculateMtbfPerAsset($from, $to)
            ->pluck('mtbf_hours')
            ->filter(fn ($v) => $v !== null);

        return $perAsset->isEmpty() ? null : round($perAsset->avg(), 2);
    }

    private function averageGapInHours(Collection $timestamps): ?float
    {
        if ($timestamps->count() < 2) {
            return null;
        }

        $gaps = [];
        for ($i = 1; $i < $timestamps->count(); $i++) {
            $prev = Carbon::parse($timestamps[$i - 1]);
            $curr = Carbon::parse($timestamps[$i]);
            $gaps[] = $prev->diffInHours($curr);
        }

        return round(array_sum($gaps) / count($gaps), 2);
    }

    private function calculateAverageDowntime(Carbon $from, Carbon $to): ?float
    {
        $durations = WorkOrder::where('type', 'corrective')
            ->where('status', 'closed')
            ->whereBetween('closed_at', [$from, $to])
            ->get()
            ->map(fn (WorkOrder $wo) => Carbon::parse($wo->created_at)->diffInHours(Carbon::parse($wo->closed_at)));

        return $durations->isEmpty() ? null : round($durations->avg(), 2);
    }
}
