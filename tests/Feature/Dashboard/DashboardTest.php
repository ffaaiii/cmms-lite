<?php

use App\Actions\Dashboard\CalculateReliabilityMetricsAction;
use App\Models\Asset;
use App\Models\User;
use App\Models\WorkOrder;
use App\Models\WorkOrderLog;
use Inertia\Testing\AssertableInertia as Assert;

use function Pest\Laravel\actingAs;

/*
|--------------------------------------------------------------------------
| Action / Unit-like Feature Tests for CalculateReliabilityMetricsAction
|--------------------------------------------------------------------------
*/

it('calculates mttr correctly based on in_progress log transition to completed_at', function () {
    $asset = Asset::factory()->create();
    $user = User::factory()->create();

    $now = now();

    // Work order 1: log in_progress 2 jam sebelum completed_at
    $wo1 = WorkOrder::factory()->create([
        'asset_id' => $asset->id,
        'type' => 'corrective',
        'status' => 'closed',
        'completed_at' => $now,
        'closed_at' => $now,
    ]);

    WorkOrderLog::forceCreate([
        'work_order_id' => $wo1->id,
        'user_id' => $user->id,
        'to_status' => 'in_progress',
        'created_at' => $now->copy()->subHours(2),
    ]);

    // Work order 2: log in_progress 4 jam sebelum completed_at
    $wo2 = WorkOrder::factory()->create([
        'asset_id' => $asset->id,
        'type' => 'corrective',
        'status' => 'closed',
        'completed_at' => $now,
        'closed_at' => $now,
    ]);

    WorkOrderLog::forceCreate([
        'work_order_id' => $wo2->id,
        'user_id' => $user->id,
        'to_status' => 'in_progress',
        'created_at' => $now->copy()->subHours(4),
    ]);

    $action = new CalculateReliabilityMetricsAction;
    $metrics = $action->execute(now()->subDays(30), now()->addDays(2));

    // Rata-rata: (2 jam + 4 jam) / 2 = 3.0 jam
    expect($metrics['mttr_hours'])->toBe(3.0);
});

it('calculates mtbf per asset correctly based on gaps between corrective completions', function () {
    $asset = Asset::factory()->create(['name' => 'Pompa Utama']);

    // Kerusakan 1: selesai 10 hari lalu
    WorkOrder::factory()->create([
        'asset_id' => $asset->id,
        'type' => 'corrective',
        'status' => 'closed',
        'completed_at' => now()->subDays(10),
        'closed_at' => now()->subDays(10),
    ]);

    // Kerusakan 2: selesai 0 hari lalu (gap 10 hari = 240 jam)
    WorkOrder::factory()->create([
        'asset_id' => $asset->id,
        'type' => 'corrective',
        'status' => 'closed',
        'completed_at' => now(),
        'closed_at' => now(),
    ]);

    $action = new CalculateReliabilityMetricsAction;
    $metrics = $action->execute(now()->subDays(30), now()->addDay());

    expect($metrics['mtbf_per_asset'])->toHaveCount(1);
    expect($metrics['mtbf_per_asset'][0]['asset_name'])->toBe('Pompa Utama');
    expect($metrics['mtbf_per_asset'][0]['failure_count'])->toBe(2);
    expect($metrics['mtbf_per_asset'][0]['mtbf_hours'])->toBe(240.0);
});

it('calculates mtbf aggregate correctly across multiple assets', function () {
    $assetA = Asset::factory()->create(['name' => 'Aset A']);
    $assetB = Asset::factory()->create(['name' => 'Aset B']);

    // Aset A: Gap 10 hari (240 jam)
    WorkOrder::factory()->create([
        'asset_id' => $assetA->id,
        'type' => 'corrective',
        'status' => 'closed',
        'completed_at' => now()->subDays(10),
        'closed_at' => now()->subDays(10),
    ]);
    WorkOrder::factory()->create([
        'asset_id' => $assetA->id,
        'type' => 'corrective',
        'status' => 'closed',
        'completed_at' => now(),
        'closed_at' => now(),
    ]);

    // Aset B: Gap 5 hari (120 jam)
    WorkOrder::factory()->create([
        'asset_id' => $assetB->id,
        'type' => 'corrective',
        'status' => 'closed',
        'completed_at' => now()->subDays(5),
        'closed_at' => now()->subDays(5),
    ]);
    WorkOrder::factory()->create([
        'asset_id' => $assetB->id,
        'type' => 'corrective',
        'status' => 'closed',
        'completed_at' => now(),
        'closed_at' => now(),
    ]);

    $action = new CalculateReliabilityMetricsAction;
    $metrics = $action->execute(now()->subDays(30), now()->addDay());

    // Rata-rata agregat: (240 jam + 120 jam) / 2 = 180.0 jam
    expect($metrics['mtbf_aggregate_hours'])->toBe(180.0);
});

it('calculates average downtime correctly from created_at to closed_at for corrective work orders', function () {
    $asset = Asset::factory()->create();

    // WO 1: Downtime 8 jam
    WorkOrder::factory()->create([
        'asset_id' => $asset->id,
        'type' => 'corrective',
        'status' => 'closed',
        'created_at' => now()->subHours(8),
        'closed_at' => now(),
    ]);

    $action = new CalculateReliabilityMetricsAction;
    $metrics = $action->execute(now()->subDays(30), now()->addDay());

    expect($metrics['downtime_avg_hours'])->toBe(8.0);
});

it('returns null for metrics when there is no data in period', function () {
    $action = new CalculateReliabilityMetricsAction;
    $metrics = $action->execute(now()->subDays(30), now()->addDay());

    expect($metrics['mttr_hours'])->toBeNull();
    expect($metrics['mtbf_aggregate_hours'])->toBeNull();
    expect($metrics['downtime_avg_hours'])->toBeNull();
    expect($metrics['mtbf_per_asset'])->toBeEmpty();
});

/*
|--------------------------------------------------------------------------
| HTTP / Page Rendering Tests
|--------------------------------------------------------------------------
*/

it('renders supervisor dashboard with metrics and stat cards', function () {
    $supervisor = makeUser('supervisor');

    actingAs($supervisor)
        ->get(route('supervisor.dashboard'))
        ->assertStatus(200)
        ->assertInertia(fn (Assert $page) => $page
            ->component('Dashboard/SupervisorDashboard')
            ->has('period')
            ->has('metrics')
            ->has('statCards')
            ->has('statCards.total_wo_open')
            ->has('statCards.wo_urgent')
            ->has('statCards.pm_due_this_week')
        );
});

it('renders executive dashboard with aggregated metrics only', function () {
    $executive = makeUser('plant_manager');

    actingAs($executive)
        ->get(route('executive.dashboard'))
        ->assertStatus(200)
        ->assertInertia(fn (Assert $page) => $page
            ->component('Dashboard/ExecutiveDashboard')
            ->has('period')
            ->has('metrics')
            ->missing('metrics.mtbf_per_asset')
        );
});

it('allows filtering supervisor dashboard by days parameter', function () {
    $supervisor = makeUser('supervisor');

    actingAs($supervisor)
        ->get(route('supervisor.dashboard', ['days' => 7]))
        ->assertStatus(200)
        ->assertInertia(fn (Assert $page) => $page
            ->component('Dashboard/SupervisorDashboard')
            ->has('period.from')
            ->has('period.to')
        );
});

/*
|--------------------------------------------------------------------------
| RBAC & Exclusion Tests
|--------------------------------------------------------------------------
*/

it('forbids technician from accessing supervisor dashboard', function () {
    $technician = makeUser('technician');

    actingAs($technician)
        ->get(route('supervisor.dashboard'))
        ->assertForbidden();
});

it('forbids supervisor from accessing executive dashboard', function () {
    $supervisor = makeUser('supervisor');

    actingAs($supervisor)
        ->get(route('executive.dashboard'))
        ->assertForbidden();
});

it('excludes preventive work orders from mtbf and downtime calculation', function () {
    $asset = Asset::factory()->create();

    // WO preventive closed dengan downtime besar sengaja dibuat —
    // harus DIABAIKAN sepenuhnya oleh MTBF dan downtime.
    WorkOrder::factory()->create([
        'asset_id' => $asset->id,
        'type' => 'preventive',
        'status' => 'closed',
        'created_at' => now()->subHours(100),
        'completed_at' => now(),
        'closed_at' => now(),
    ]);
    WorkOrder::factory()->create([
        'asset_id' => $asset->id,
        'type' => 'preventive',
        'status' => 'closed',
        'created_at' => now()->subHours(50),
        'completed_at' => now()->subDays(5),
        'closed_at' => now()->subDays(5),
    ]);

    $action = new CalculateReliabilityMetricsAction;
    $metrics = $action->execute(now()->subDays(30), now()->addDay());

    expect($metrics['mtbf_per_asset'])->toBeEmpty();
    expect($metrics['downtime_avg_hours'])->toBeNull();
});
