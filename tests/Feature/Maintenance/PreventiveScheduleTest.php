<?php

use App\Models\Asset;
use App\Models\Notification;
use App\Models\User;
use App\Models\WorkOrder;
use Database\Seeders\RoleSeeder;
use Database\Seeders\SystemUserSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed(RoleSeeder::class);
    $this->seed(SystemUserSeeder::class);
});

function makeSupervisor(): User
{
    return makeUser('supervisor');
}

test('aset yang belum pernah PM (last_pm_at null) dianggap jatuh tempo', function () {
    $asset = Asset::factory()->create(['last_pm_at' => null, 'status' => 'active']);

    $this->artisan('pm:check');

    $this->assertDatabaseHas('work_orders', [
        'asset_id' => $asset->id,
        'type' => 'preventive',
        'status' => 'draft',
    ]);
});

test('aset yang melewati interval PM dianggap jatuh tempo', function () {
    $asset = Asset::factory()->create([
        'last_pm_at' => now()->subDays(100),
        'pm_interval_days' => 90,
        'status' => 'active',
    ]);

    $this->artisan('pm:check');

    $this->assertDatabaseHas('work_orders', ['asset_id' => $asset->id, 'type' => 'preventive']);
});

test('aset yang belum jatuh tempo tidak menghasilkan work order', function () {
    $asset = Asset::factory()->create([
        'last_pm_at' => now()->subDays(10),
        'pm_interval_days' => 90,
        'status' => 'active',
    ]);

    $this->artisan('pm:check');

    $this->assertDatabaseMissing('work_orders', ['asset_id' => $asset->id]);
});

test('aset nonaktif tidak dijadwalkan PM meski melewati interval', function () {
    $asset = Asset::factory()->create([
        'last_pm_at' => now()->subDays(200),
        'pm_interval_days' => 90,
        'status' => 'inactive',
    ]);

    $this->artisan('pm:check');

    $this->assertDatabaseMissing('work_orders', ['asset_id' => $asset->id]);
});

test('guard anti-duplikat: aset due tidak digenerate ulang kalau masih ada WO preventive belum closed', function () {
    $asset = Asset::factory()->create(['last_pm_at' => now()->subDays(200), 'pm_interval_days' => 90]);

    $this->artisan('pm:check');
    expect(WorkOrder::where('asset_id', $asset->id)->count())->toBe(1);

    // Jalankan lagi tanpa mengubah apapun — harusnya tidak nambah WO baru
    $this->artisan('pm:check');
    expect(WorkOrder::where('asset_id', $asset->id)->count())->toBe(1);
});

test('aset due digenerate lagi setelah WO preventive sebelumnya closed', function () {
    $asset = Asset::factory()->create(['last_pm_at' => now()->subDays(200), 'pm_interval_days' => 90]);

    $this->artisan('pm:check');
    $firstWo = WorkOrder::where('asset_id', $asset->id)->first();
    $firstWo->update(['status' => 'closed']);

    $this->artisan('pm:check');

    expect(WorkOrder::where('asset_id', $asset->id)->count())->toBe(2);
});

test('created_by work order otomatis adalah user sistem', function () {
    $asset = Asset::factory()->create(['last_pm_at' => null]);

    $this->artisan('pm:check');

    $wo = WorkOrder::where('asset_id', $asset->id)->first();
    expect($wo->creator->email)->toBe('system@cmms-lite.local');
});

test('semua supervisor menerima notifikasi pm_due', function () {
    $supervisor1 = makeSupervisor();
    $supervisor2 = makeSupervisor();
    $technician = makeUser('technician'); // tidak boleh dapat notifikasi ini

    $asset = Asset::factory()->create(['last_pm_at' => null]);

    $this->artisan('pm:check');

    $wo = WorkOrder::where('asset_id', $asset->id)->first();

    expect(Notification::where('user_id', $supervisor1->id)->where('type', 'pm_due')->count())->toBe(1);
    expect(Notification::where('user_id', $supervisor2->id)->where('type', 'pm_due')->count())->toBe(1);
    expect(Notification::where('user_id', $technician->id)->count())->toBe(0);

    $notif = Notification::where('user_id', $supervisor1->id)->first();
    expect($notif->related_work_order_id)->toBe($wo->id);
    expect($notif->is_read)->toBeFalse();
});

test('tidak ada supervisor sama sekali tidak menyebabkan error, WO tetap tergenerate', function () {
    $asset = Asset::factory()->create(['last_pm_at' => null]);

    $this->artisan('pm:check')->assertSuccessful();

    $this->assertDatabaseHas('work_orders', ['asset_id' => $asset->id]);
    expect(Notification::count())->toBe(0);
});
