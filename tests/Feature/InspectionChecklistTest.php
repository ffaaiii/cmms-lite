<?php

use App\Models\Asset;
use App\Models\InspectionChecklist;
use App\Models\WorkOrder;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed(RoleSeeder::class);
});

// =============================================================
// CREATE (Teknisi)
// =============================================================

test('teknisi bisa isi checklist kondisi baik, status langsung confirmed tanpa review', function () {
    $technician = makeUser('technician');
    $asset = Asset::factory()->create();

    $response = $this->actingAs($technician)->post('/technician/checklists', [
        'asset_id' => $asset->id,
        'condition_found' => 'good',
    ]);

    $response->assertRedirect(route('technician.tasks.index'));
    $this->assertDatabaseHas('inspection_checklists', [
        'asset_id' => $asset->id,
        'inspected_by' => $technician->id,
        'condition_found' => 'good',
        'status' => 'confirmed',
        'reviewed_by' => null,
    ]);
});

test('teknisi isi checklist needs_attention menghasilkan status pending_review', function () {
    $technician = makeUser('technician');
    $asset = Asset::factory()->create();

    $response = $this->actingAs($technician)->post('/technician/checklists', [
        'asset_id' => $asset->id,
        'condition_found' => 'needs_attention',
        'notes' => 'Ada retakan kecil di pipa',
    ]);

    $response->assertRedirect(route('technician.tasks.index'));
    $this->assertDatabaseHas('inspection_checklists', [
        'asset_id' => $asset->id,
        'condition_found' => 'needs_attention',
        'status' => 'pending_review',
    ]);
});

test('checklist damaged/needs_attention wajib isi notes', function () {
    $technician = makeUser('technician');
    $asset = Asset::factory()->create();

    $response = $this->actingAs($technician)->post('/technician/checklists', [
        'asset_id' => $asset->id,
        'condition_found' => 'damaged',
    ]);

    $response->assertSessionHasErrors('notes');
});

test('checklist good tidak wajib isi notes', function () {
    $technician = makeUser('technician');
    $asset = Asset::factory()->create();

    $response = $this->actingAs($technician)->post('/technician/checklists', [
        'asset_id' => $asset->id,
        'condition_found' => 'good',
    ]);

    $response->assertSessionDoesntHaveErrors('notes');
});

test('supervisor tidak bisa membuat checklist', function () {
    $supervisor = makeUser('supervisor');
    $asset = Asset::factory()->create();

    $response = $this->actingAs($supervisor)->post('/technician/checklists', [
        'asset_id' => $asset->id,
        'condition_found' => 'good',
    ]);

    $response->assertForbidden();
});

// =============================================================
// REVIEW LISTING (Supervisor) — hanya pending_review yang tampil
// =============================================================

test('supervisor hanya melihat checklist berstatus pending_review di listing', function () {
    $supervisor = makeUser('supervisor');
    $technician = makeUser('technician');
    $asset = Asset::factory()->create();

    InspectionChecklist::create([
        'asset_id' => $asset->id, 'inspected_by' => $technician->id,
        'condition_found' => 'good', 'status' => 'confirmed',
    ]);
    InspectionChecklist::create([
        'asset_id' => $asset->id, 'inspected_by' => $technician->id,
        'condition_found' => 'damaged', 'notes' => 'Rusak parah', 'status' => 'pending_review',
    ]);

    $response = $this->actingAs($supervisor)->get('/supervisor/checklists');

    $response->assertOk();
    $response->assertInertia(fn ($page) => $page
        ->has('checklists.data', 1)
        ->where('checklists.data.0.condition_found', 'damaged')
    );
});

test('teknisi tidak bisa akses halaman review checklist supervisor', function () {
    $technician = makeUser('technician');

    $response = $this->actingAs($technician)->get('/supervisor/checklists');

    $response->assertForbidden();
});

// =============================================================
// CONFIRM — generate WO corrective
// =============================================================

test('supervisor confirm checklist damaged menghasilkan WO priority urgent', function () {
    $supervisor = makeUser('supervisor');
    $technician = makeUser('technician');
    $asset = Asset::factory()->create();

    $checklist = InspectionChecklist::create([
        'asset_id' => $asset->id,
        'inspected_by' => $technician->id,
        'condition_found' => 'damaged',
        'notes' => 'Turbin bergetar tidak normal',
        'status' => 'pending_review',
    ]);

    $response = $this->actingAs($supervisor)
        ->patch("/supervisor/checklists/{$checklist->id}/confirm");

    $response->assertRedirect();

    $checklist->refresh();
    expect($checklist->status)->toBe('confirmed');
    expect($checklist->reviewed_by)->toBe($supervisor->id);
    expect($checklist->generated_work_order_id)->not->toBeNull();

    $wo = WorkOrder::find($checklist->generated_work_order_id);
    expect($wo->type)->toBe('corrective');
    expect($wo->priority)->toBe('urgent');
    expect($wo->status)->toBe('draft');
    expect($wo->asset_id)->toBe($asset->id);
    expect($wo->created_by)->toBe($supervisor->id);
    expect($wo->assigned_to)->toBeNull();
});

test('supervisor confirm checklist needs_attention menghasilkan WO priority normal', function () {
    $supervisor = makeUser('supervisor');
    $technician = makeUser('technician');
    $asset = Asset::factory()->create();

    $checklist = InspectionChecklist::create([
        'asset_id' => $asset->id,
        'inspected_by' => $technician->id,
        'condition_found' => 'needs_attention',
        'notes' => 'Perlu pengecekan lanjutan',
        'status' => 'pending_review',
    ]);

    $this->actingAs($supervisor)->patch("/supervisor/checklists/{$checklist->id}/confirm");

    $wo = WorkOrder::find($checklist->fresh()->generated_work_order_id);
    expect($wo->priority)->toBe('normal');
});

test('confirm checklist yang sudah bukan pending_review gagal', function () {
    $supervisor = makeUser('supervisor');
    $technician = makeUser('technician');
    $asset = Asset::factory()->create();

    $checklist = InspectionChecklist::create([
        'asset_id' => $asset->id, 'inspected_by' => $technician->id,
        'condition_found' => 'good', 'status' => 'confirmed',
    ]);

    $this->actingAs($supervisor)
        ->patch("/supervisor/checklists/{$checklist->id}/confirm")
        ->assertStatus(500);
});

test('teknisi tidak bisa confirm checklist', function () {
    $technician = makeUser('technician');
    $asset = Asset::factory()->create();

    $checklist = InspectionChecklist::create([
        'asset_id' => $asset->id, 'inspected_by' => $technician->id,
        'condition_found' => 'damaged', 'notes' => 'Rusak', 'status' => 'pending_review',
    ]);

    $response = $this->actingAs($technician)
        ->patch("/supervisor/checklists/{$checklist->id}/confirm");

    $response->assertForbidden();
});

// =============================================================
// DISMISS — tidak generate WO
// =============================================================

test('supervisor bisa dismiss checklist tanpa membuat WO', function () {
    $supervisor = makeUser('supervisor');
    $technician = makeUser('technician');
    $asset = Asset::factory()->create();

    $checklist = InspectionChecklist::create([
        'asset_id' => $asset->id,
        'inspected_by' => $technician->id,
        'condition_found' => 'needs_attention',
        'notes' => 'Kondisi masih wajar, tidak perlu WO',
        'status' => 'pending_review',
    ]);

    $response = $this->actingAs($supervisor)
        ->patch("/supervisor/checklists/{$checklist->id}/dismiss");

    $response->assertRedirect();

    $checklist->refresh();
    expect($checklist->status)->toBe('dismissed');
    expect($checklist->reviewed_by)->toBe($supervisor->id);
    expect($checklist->generated_work_order_id)->toBeNull();

    expect(WorkOrder::where('asset_id', $asset->id)->count())->toBe(0);
});

test('dismiss checklist yang sudah bukan pending_review gagal', function () {
    $supervisor = makeUser('supervisor');
    $technician = makeUser('technician');
    $asset = Asset::factory()->create();

    $checklist = InspectionChecklist::create([
        'asset_id' => $asset->id, 'inspected_by' => $technician->id,
        'condition_found' => 'good', 'status' => 'confirmed',
    ]);

    $this->actingAs($supervisor)
        ->patch("/supervisor/checklists/{$checklist->id}/dismiss")
        ->assertStatus(500);
});
