<?php

use App\Models\Asset;
use App\Models\WorkOrder;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed(\Database\Seeders\RoleSeeder::class);
});

// =============================================================
// CREATE (Supervisor)
// =============================================================

test('supervisor bisa membuat work order baru', function () {
    $supervisor = makeUser('supervisor');
    $asset = Asset::factory()->create();

    $response = $this->actingAs($supervisor)->post('/supervisor/work-orders', [
        'asset_id' => $asset->id,
        'type' => 'corrective',
        'priority' => 'urgent',
        'description' => 'Kebocoran pipa uap',
    ]);

    $response->assertRedirect(route('supervisor.work-orders.index'));
    $this->assertDatabaseHas('work_orders', [
        'asset_id' => $asset->id,
        'type' => 'corrective',
        'status' => 'draft',
        'created_by' => $supervisor->id,
    ]);
});

test('teknisi tidak bisa membuat work order', function () {
    $technician = makeUser('technician');
    $asset = Asset::factory()->create();

    $response = $this->actingAs($technician)->post('/supervisor/work-orders', [
        'asset_id' => $asset->id,
        'type' => 'corrective',
        'priority' => 'normal',
    ]);

    $response->assertForbidden();
});

// =============================================================
// ASSIGN & REASSIGN (reset rejection_count + is_escalated)
// =============================================================

test('supervisor bisa assign work order ke teknisi', function () {
    $supervisor = makeUser('supervisor');
    $technician = makeUser('technician');
    $wo = WorkOrder::factory()->create(['status' => 'draft']);

    $response = $this->actingAs($supervisor)
        ->patch("/supervisor/work-orders/{$wo->id}/assign", ['assigned_to' => $technician->id]);

    $response->assertRedirect();
    $this->assertDatabaseHas('work_orders', [
        'id' => $wo->id,
        'assigned_to' => $technician->id,
        'status' => 'assigned',
    ]);
});

test('assign gagal kalau user yang dipilih bukan teknisi', function () {
    $supervisor = makeUser('supervisor');
    $bukanTeknisi = makeUser('admin');
    $wo = WorkOrder::factory()->create(['status' => 'draft']);

    $response = $this->actingAs($supervisor)
        ->patch("/supervisor/work-orders/{$wo->id}/assign", ['assigned_to' => $bukanTeknisi->id]);

    $response->assertSessionHasErrors('assigned_to');
});

test('reassign ke teknisi lain me-reset rejection_count dan is_escalated', function () {
    $supervisor = makeUser('supervisor');
    $teknisiLama = makeUser('technician');
    $teknisiBaru = makeUser('technician');

    $wo = WorkOrder::factory()->create([
        'assigned_to' => $teknisiLama->id,
        'status' => 'in_progress',
        'rejection_count' => 2,
        'is_escalated' => true,
    ]);

    $this->actingAs($supervisor)
        ->patch("/supervisor/work-orders/{$wo->id}/assign", ['assigned_to' => $teknisiBaru->id]);

    $wo->refresh();
    expect($wo->assigned_to)->toBe($teknisiBaru->id);
    expect($wo->rejection_count)->toBe(0);
    expect($wo->is_escalated)->toBeFalse();
    expect($wo->status)->toBe('assigned');
});

test('assign ulang ke teknisi yang sama TIDAK reset rejection_count', function () {
    $supervisor = makeUser('supervisor');
    $teknisi = makeUser('technician');

    $wo = WorkOrder::factory()->create([
        'assigned_to' => $teknisi->id,
        'status' => 'in_progress',
        'rejection_count' => 1,
        'is_escalated' => false,
    ]);

    $this->actingAs($supervisor)
        ->patch("/supervisor/work-orders/{$wo->id}/assign", ['assigned_to' => $teknisi->id]);

    $wo->refresh();
    expect($wo->rejection_count)->toBe(1);
});

// =============================================================
// TRANSITION (Teknisi) — state machine & ownership
// =============================================================

test('teknisi pemilik bisa transisi assigned ke in_progress', function () {
    $technician = makeUser('technician');
    $wo = WorkOrder::factory()->assignedTo($technician)->create();

    $response = $this->actingAs($technician)
        ->patch("/technician/tasks/{$wo->id}/transition", ['status' => 'in_progress']);

    $response->assertRedirect();
    $this->assertDatabaseHas('work_orders', ['id' => $wo->id, 'status' => 'in_progress']);
    $this->assertDatabaseHas('work_order_logs', [
        'work_order_id' => $wo->id,
        'from_status' => 'assigned',
        'to_status' => 'in_progress',
    ]);
});

test('teknisi bisa transisi in_progress ke completed sekaligus catat part', function () {
    $technician = makeUser('technician');
    $wo = WorkOrder::factory()->assignedTo($technician)->inProgress()->create();

    $response = $this->actingAs($technician)
        ->patch("/technician/tasks/{$wo->id}/transition", [
            'status' => 'completed',
            'note' => 'Selesai ganti seal',
            'parts' => [
                ['part_name' => 'Seal Karet', 'quantity' => 2, 'unit' => 'pcs'],
            ],
        ]);

    $response->assertRedirect();
    $wo->refresh();
    expect($wo->status)->toBe('completed');
    expect($wo->completed_at)->not->toBeNull();
    $this->assertDatabaseHas('work_order_parts', [
        'work_order_id' => $wo->id,
        'part_name' => 'Seal Karet',
        'quantity' => 2,
    ]);
});

test('teknisi lain (bukan pemilik) tidak bisa transisi work order', function () {
    $pemilik = makeUser('technician');
    $teknisiLain = makeUser('technician');
    $wo = WorkOrder::factory()->assignedTo($pemilik)->create();

    $response = $this->actingAs($teknisiLain)
        ->patch("/technician/tasks/{$wo->id}/transition", ['status' => 'in_progress']);

    $response->assertForbidden();
});

test('supervisor tidak bisa pakai endpoint transition milik teknisi', function () {
    $supervisor = makeUser('supervisor');
    $technician = makeUser('technician');
    $wo = WorkOrder::factory()->assignedTo($technician)->create();

    $response = $this->actingAs($supervisor)
        ->patch("/technician/tasks/{$wo->id}/transition", ['status' => 'in_progress']);

    $response->assertForbidden();
});

test('transisi loncat status tidak valid ditolak', function () {
    $technician = makeUser('technician');
    $wo = WorkOrder::factory()->assignedTo($technician)->create();

    $this->actingAs($technician)
        ->patch("/technician/tasks/{$wo->id}/transition", ['status' => 'completed'])
        ->assertStatus(500);

    expect($wo->fresh()->status)->toBe('assigned');
});

test('transisi mundur dari completed ke in_progress lewat endpoint transition ditolak', function () {
    $technician = makeUser('technician');
    $wo = WorkOrder::factory()->assignedTo($technician)->completed()->create();

    $this->actingAs($technician)
        ->patch("/technician/tasks/{$wo->id}/transition", ['status' => 'in_progress'])
        ->assertStatus(500);
});

// =============================================================
// APPROVE (Supervisor)
// =============================================================

test('supervisor bisa approve work order completed, WO preventive update last_pm_at aset', function () {
    $supervisor = makeUser('supervisor');
    $technician = makeUser('technician');
    $asset = Asset::factory()->create(['last_pm_at' => null]);

    $wo = WorkOrder::factory()
        ->assignedTo($technician)
        ->completed()
        ->create(['asset_id' => $asset->id, 'type' => 'preventive']);

    $response = $this->actingAs($supervisor)
        ->patch("/supervisor/work-orders/{$wo->id}/approve");

    $response->assertRedirect();
    $wo->refresh();
    expect($wo->status)->toBe('closed');
    expect($wo->approved_by)->toBe($supervisor->id);
    expect($wo->closed_at)->not->toBeNull();

    $asset->refresh();
    expect($asset->last_pm_at->toDateString())->toBe($wo->completed_at->toDateString());
});

test('approve work order corrective TIDAK mengubah last_pm_at aset', function () {
    $supervisor = makeUser('supervisor');
    $technician = makeUser('technician');
    $asset = Asset::factory()->create(['last_pm_at' => null]);

    $wo = WorkOrder::factory()
        ->assignedTo($technician)
        ->completed()
        ->create(['asset_id' => $asset->id, 'type' => 'corrective']);

    $this->actingAs($supervisor)->patch("/supervisor/work-orders/{$wo->id}/approve");

    expect($asset->fresh()->last_pm_at)->toBeNull();
});

test('approve gagal kalau status work order belum completed', function () {
    $supervisor = makeUser('supervisor');
    $wo = WorkOrder::factory()->create(['status' => 'assigned']);

    $this->actingAs($supervisor)
        ->patch("/supervisor/work-orders/{$wo->id}/approve")
        ->assertStatus(500);
});

test('teknisi tidak bisa approve work order', function () {
    $technician = makeUser('technician');
    $wo = WorkOrder::factory()->completed()->create();

    $response = $this->actingAs($technician)
        ->patch("/supervisor/work-orders/{$wo->id}/approve");

    $response->assertForbidden();
});

// =============================================================
// REJECT (Supervisor) & ESKALASI (ADR-004)
// =============================================================

test('supervisor bisa reject work order completed, status balik ke in_progress', function () {
    $supervisor = makeUser('supervisor');
    $wo = WorkOrder::factory()->completed()->create();

    $response = $this->actingAs($supervisor)
        ->patch("/supervisor/work-orders/{$wo->id}/reject", [
            'rejection_note' => 'Part yang dipakai tidak sesuai spek',
        ]);

    $response->assertRedirect();
    $wo->refresh();
    expect($wo->status)->toBe('in_progress');
    expect($wo->rejection_count)->toBe(1);
    expect($wo->is_escalated)->toBeFalse();
    expect($wo->rejection_note)->toBe('Part yang dipakai tidak sesuai spek');
    expect($wo->completed_at)->toBeNull();
});

test('reject wajib isi rejection_note', function () {
    $supervisor = makeUser('supervisor');
    $wo = WorkOrder::factory()->completed()->create();

    $response = $this->actingAs($supervisor)
        ->patch("/supervisor/work-orders/{$wo->id}/reject", []);

    $response->assertSessionHasErrors('rejection_note');
});

test('reject ke-2 memicu is_escalated menjadi true', function () {
    $supervisor = makeUser('supervisor');
    $wo = WorkOrder::factory()->completed()->create(['rejection_count' => 1]);

    $this->actingAs($supervisor)
        ->patch("/supervisor/work-orders/{$wo->id}/reject", [
            'rejection_note' => 'Masih belum sesuai, kedua kalinya',
        ]);

    $wo->refresh();
    expect($wo->rejection_count)->toBe(2);
    expect($wo->is_escalated)->toBeTrue();
});

test('teknisi tidak bisa reject work order', function () {
    $technician = makeUser('technician');
    $wo = WorkOrder::factory()->completed()->create();

    $response = $this->actingAs($technician)
        ->patch("/supervisor/work-orders/{$wo->id}/reject", [
            'rejection_note' => 'Coba reject',
        ]);

    $response->assertForbidden();
});

// =============================================================
// VIEW / OWNERSHIP LISTING
// =============================================================

test('teknisi hanya melihat work order miliknya sendiri di listing', function () {
    $technician = makeUser('technician');
    $lainnya = makeUser('technician');

    WorkOrder::factory()->assignedTo($technician)->create();
    WorkOrder::factory()->assignedTo($lainnya)->create();

    $response = $this->actingAs($technician)->get('/technician/tasks');

    $response->assertOk();
    $response->assertInertia(fn ($page) => $page->has('workOrders.data', 1));
});

test('supervisor melihat semua work order di listing', function () {
    $supervisor = makeUser('supervisor');
    $t1 = makeUser('technician');
    $t2 = makeUser('technician');

    WorkOrder::factory()->assignedTo($t1)->create();
    WorkOrder::factory()->assignedTo($t2)->create();

    $response = $this->actingAs($supervisor)->get('/supervisor/work-orders');

    $response->assertOk();
    $response->assertInertia(fn ($page) => $page->has('workOrders.data', 2));
});

test('teknisi tidak bisa buka detail work order milik teknisi lain', function () {
    $pemilik = makeUser('technician');
    $bukanPemilik = makeUser('technician');
    $wo = WorkOrder::factory()->assignedTo($pemilik)->create();

    $response = $this->actingAs($bukanPemilik)->get("/technician/tasks/{$wo->id}");

    $response->assertForbidden();
});