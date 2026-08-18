<?php

use App\Models\Asset;
use App\Models\Role;
use App\Models\User;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed(RoleSeeder::class);
});

// --- CREATE ---

test('admin bisa membuat aset baru', function () {
    $admin = makeUser('admin');

    $response = $this->actingAs($admin)->post('/admin/assets', [
        'name' => 'Turbin Uap 1',
        'category' => 'turbine',
        'location' => 'Garut',
        'pm_interval_days' => 90,
    ]);

    $response->assertRedirect(route('admin.assets.index'));
    $this->assertDatabaseHas('assets', ['name' => 'Turbin Uap 1']);
});

test('supervisor tidak bisa membuat aset baru', function () {
    $supervisor = makeUser('supervisor');

    $response = $this->actingAs($supervisor)->post('/admin/assets', [
        'name' => 'Turbin Uap 1',
        'category' => 'turbine',
        'pm_interval_days' => 90,
    ]);

    $response->assertForbidden();
});

test('teknisi tidak bisa membuat aset baru', function () {
    $technician = makeUser('technician');

    $response = $this->actingAs($technician)->post('/admin/assets', [
        'name' => 'Turbin Uap 1',
        'category' => 'turbine',
        'pm_interval_days' => 90,
    ]);

    $response->assertForbidden();
});

test('membuat aset gagal tanpa nama', function () {
    $admin = makeUser('admin');

    $response = $this->actingAs($admin)->post('/admin/assets', [
        'category' => 'turbine',
        'pm_interval_days' => 90,
    ]);

    $response->assertSessionHasErrors('name');
});

// --- FULL UPDATE (Admin) ---

test('admin bisa update semua field aset', function () {
    $admin = makeUser('admin');
    $asset = Asset::factory()->create(['name' => 'Aset Lama']);

    $response = $this->actingAs($admin)->put("/admin/assets/{$asset->id}", [
        'name' => 'Aset Baru',
        'category' => 'well',
        'condition' => 'damaged',
        'pm_interval_days' => 60,
        'status' => 'inactive',
    ]);

    $response->assertRedirect(route('admin.assets.index'));
    $this->assertDatabaseHas('assets', ['id' => $asset->id, 'name' => 'Aset Baru', 'status' => 'inactive']);
});

test('supervisor tidak bisa akses full update endpoint admin', function () {
    $supervisor = makeUser('supervisor');
    $asset = Asset::factory()->create();

    $response = $this->actingAs($supervisor)->put("/admin/assets/{$asset->id}", [
        'name' => 'Coba Ubah',
        'category' => 'well',
        'condition' => 'damaged',
        'pm_interval_days' => 60,
        'status' => 'inactive',
    ]);

    $response->assertForbidden();
});

// --- UPDATE CONDITION (Supervisor, field terbatas) ---

test('supervisor bisa update kondisi dan lokasi aset', function () {
    $supervisor = makeUser('supervisor');
    $asset = Asset::factory()->create(['condition' => 'good', 'location' => 'Lokasi Lama']);

    $response = $this->actingAs($supervisor)->patch("/supervisor/assets/{$asset->id}/condition", [
        'condition' => 'needs_attention',
        'location' => 'Lokasi Baru',
    ]);

    $response->assertRedirect(route('supervisor.assets.index'));
    $this->assertDatabaseHas('assets', [
        'id' => $asset->id,
        'condition' => 'needs_attention',
        'location' => 'Lokasi Baru',
    ]);
});

test('supervisor tidak bisa ubah field lain lewat endpoint updateCondition', function () {
    $supervisor = makeUser('supervisor');
    $asset = Asset::factory()->create(['name' => 'Nama Asli', 'pm_interval_days' => 90]);

    $this->actingAs($supervisor)->patch("/supervisor/assets/{$asset->id}/condition", [
        'condition' => 'damaged',
        'location' => 'Lokasi Baru',
        'name' => 'Nama Diubah Paksa',       // tidak dikenal validator
        'pm_interval_days' => 9999,           // tidak dikenal validator
    ]);

    $asset->refresh();

    expect($asset->name)->toBe('Nama Asli');
    expect($asset->pm_interval_days)->toBe(90);
});

test('admin tidak bisa akses endpoint updateCondition milik supervisor', function () {
    $admin = makeUser('admin');
    $asset = Asset::factory()->create();

    $response = $this->actingAs($admin)->patch("/supervisor/assets/{$asset->id}/condition", [
        'condition' => 'damaged',
    ]);

    $response->assertForbidden();
});

test('teknisi tidak bisa update kondisi aset', function () {
    $technician = makeUser('technician');
    $asset = Asset::factory()->create();

    $response = $this->actingAs($technician)->patch("/supervisor/assets/{$asset->id}/condition", [
        'condition' => 'damaged',
    ]);

    $response->assertForbidden();
});

// --- DELETE (soft delete, Admin only) ---

test('admin bisa soft delete aset', function () {
    $admin = makeUser('admin');
    $asset = Asset::factory()->create();

    $response = $this->actingAs($admin)->delete("/admin/assets/{$asset->id}");

    $response->assertRedirect(route('admin.assets.index'));
    $this->assertSoftDeleted('assets', ['id' => $asset->id]);
});

test('supervisor tidak bisa hapus aset', function () {
    $supervisor = makeUser('supervisor');
    $asset = Asset::factory()->create();

    $response = $this->actingAs($supervisor)->delete("/admin/assets/{$asset->id}");

    $response->assertForbidden();
    $this->assertDatabaseHas('assets', ['id' => $asset->id, 'deleted_at' => null]);
});

// --- VIEW (viewAny — semua role kecuali plant_manager boleh lihat listing) ---

test('teknisi bisa melihat listing aset (read-only)', function () {
    $technician = makeUser('technician');
    Asset::factory()->create();

    $response = $this->actingAs($technician)->get('/technician/assets');

    $response->assertOk();
});

test('plant manager tidak bisa akses listing aset sama sekali', function () {
    $plantManager = makeUser('plant_manager');

    $response = $this->actingAs($plantManager)->get('/admin/assets');

    $response->assertForbidden();
});

test('guest tidak bisa akses listing aset', function () {
    $response = $this->get('/admin/assets');

    $response->assertRedirect('/login');
});

// --- Soft-deleted asset tetap muncul di listing ---

test('aset yang sudah dihapus tetap muncul di listing dengan flag is_deleted', function () {
    $admin = makeUser('admin');
    $asset = Asset::factory()->create(['name' => 'Aset Terhapus']);
    $asset->delete();

    $response = $this->actingAs($admin)->get('/admin/assets');

    $response->assertOk();
    $response->assertInertia(fn ($page) => $page
        ->has('assets.data', 1)
        ->where('assets.data.0.name', 'Aset Terhapus')
        ->where('assets.data.0.is_deleted', true)
    );
});
