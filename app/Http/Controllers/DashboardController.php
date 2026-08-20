<?php

namespace App\Http\Controllers;

use App\Actions\Dashboard\CalculateReliabilityMetricsAction;
use App\Models\Asset;
use App\Models\WorkOrder;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function supervisor(Request $request, CalculateReliabilityMetricsAction $action): Response
    {
        [$from, $to] = $this->resolvePeriod($request);

        return Inertia::render('Dashboard/SupervisorDashboard', [
            'period' => ['from' => $from->toDateString(), 'to' => $to->toDateString()],
            'metrics' => $action->execute($from, $to),
            'statCards' => [
                'total_wo_open' => WorkOrder::where('status', '!=', 'closed')->count(),
                'wo_urgent' => WorkOrder::where('priority', 'urgent')->where('status', '!=', 'closed')->count(),
                'pm_due_this_week' => Asset::where('status', 'active')
                    ->get()
                    ->filter(function (Asset $asset) {
                        if ($asset->last_pm_at === null) {
                            return true;
                        }
                        $dueDate = $asset->last_pm_at->copy()->addDays($asset->pm_interval_days);

                        return $dueDate->between(now(), now()->addWeek());
                    })->count(),
            ],
        ]);
    }

    public function executive(Request $request, CalculateReliabilityMetricsAction $action): Response
    {
        [$from, $to] = $this->resolvePeriod($request);

        $metrics = $action->execute($from, $to);

        return Inertia::render('Dashboard/ExecutiveDashboard', [
            'period' => ['from' => $from->toDateString(), 'to' => $to->toDateString()],
            'metrics' => [
                'mttr_hours' => $metrics['mttr_hours'],
                'mtbf_aggregate_hours' => $metrics['mtbf_aggregate_hours'],
                'downtime_avg_hours' => $metrics['downtime_avg_hours'],
            ],
        ]);
    }

    private function resolvePeriod(Request $request): array
    {
        if ($request->filled('from') && $request->filled('to')) {
            return [Carbon::parse($request->input('from'))->startOfDay(), Carbon::parse($request->input('to'))->endOfDay()];
        }

        $days = (int) $request->input('days', 30);

        return [now()->subDays($days)->startOfDay(), now()->endOfDay()];
    }
}
