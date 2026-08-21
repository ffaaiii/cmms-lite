<?php

use App\Actions\WorkOrder\RejectWorkOrderAction;
use App\Models\Asset;
use App\Models\Notification;
use App\Models\WorkOrder;

use function Pest\Laravel\actingAs;

test('assign work order membuat notifikasi wo_assigned untuk teknisi', function () {
    $supervisor = makeUser('supervisor');
    $technician = makeUser('technician');
    $asset = Asset::factory()->create();

    $workOrder = WorkOrder::factory()->create([
        'asset_id' => $asset->id,
        'status' => 'draft',
    ]);

    actingAs($supervisor)->patch(route('supervisor.work-orders.assign', $workOrder->id), [
        'assigned_to' => $technician->id,
    ]);

    expect(Notification::where('user_id', $technician->id)->count())->toBe(1);

    $notif = Notification::where('user_id', $technician->id)->first();
    expect($notif->type)->toBe('wo_assigned');
    expect($notif->related_work_order_id)->toBe($workOrder->id);
});

test('reject work order membuat notifikasi wo_rejected untuk teknisi pemilik', function () {
    $supervisor = makeUser('supervisor');
    $technician = makeUser('technician');
    $asset = Asset::factory()->create();

    $workOrder = WorkOrder::factory()->create([
        'asset_id' => $asset->id,
        'assigned_to' => $technician->id,
        'status' => 'completed',
        'completed_at' => now(),
        'rejection_count' => 0,
    ]);

    (new RejectWorkOrderAction)->execute($workOrder, $supervisor->id, 'Hasil perbaikan belum rapi');

    $notif = Notification::where('user_id', $technician->id)->where('type', 'wo_rejected')->first();
    expect($notif)->not->toBeNull();
    expect($notif->related_work_order_id)->toBe($workOrder->id);
});

test('reject ke-2 membuat notifikasi wo_escalated untuk semua supervisor', function () {
    $supervisor1 = makeUser('supervisor');
    $supervisor2 = makeUser('supervisor');
    $technician = makeUser('technician');
    $asset = Asset::factory()->create();

    $workOrder = WorkOrder::factory()->create([
        'asset_id' => $asset->id,
        'assigned_to' => $technician->id,
        'status' => 'completed',
        'completed_at' => now(),
        'rejection_count' => 1, // reject ini akan jadi yang ke-2
    ]);

    (new RejectWorkOrderAction)->execute($workOrder, $supervisor1->id, 'Masih belum sesuai lagi');

    expect(Notification::where('user_id', $supervisor1->id)->where('type', 'wo_escalated')->count())->toBe(1);
    expect(Notification::where('user_id', $supervisor2->id)->where('type', 'wo_escalated')->count())->toBe(1);
});

test('user bisa menandai notifikasi sebagai sudah dibaca', function () {
    $user = makeUser('technician');

    $notif = Notification::create([
        'user_id' => $user->id,
        'type' => 'wo_assigned',
        'message' => 'Test notifikasi',
        'is_read' => false,
    ]);

    actingAs($user)
        ->patch(route('notifications.read', $notif->id))
        ->assertRedirect();

    expect($notif->fresh()->is_read)->toBeTrue();
});

test('user tidak bisa menandai notifikasi milik orang lain sebagai dibaca', function () {
    $owner = makeUser('technician');
    $bukanOwner = makeUser('technician');

    $notif = Notification::create([
        'user_id' => $owner->id,
        'type' => 'wo_assigned',
        'message' => 'Test notifikasi',
        'is_read' => false,
    ]);

    actingAs($bukanOwner)
        ->patch(route('notifications.read', $notif->id))
        ->assertForbidden();
});
