<?php

namespace Database\Seeders;

use App\Models\Asset;
use App\Models\InspectionChecklist;
use App\Models\Role;
use App\Models\User;
use App\Models\WorkOrder;
use Illuminate\Database\Seeder;

class DemoDataSeeder extends Seeder
{
    public function run(): void
    {
        // ── Akun demo per role (nama sesuai persona 02-stakeholder-persona.md) ──
        $admin = User::firstOrCreate(
            ['email' => 'admin@cmms-lite.local'],
            ['name' => 'Admin', 'password' => bcrypt('password'), 'role_id' => Role::where('slug', 'admin')->value('id'), 'email_verified_at' => now()]
        );

        $rina = User::firstOrCreate(
            ['email' => 'rina@cmms-lite.local'],
            ['name' => 'Rina Supervisor', 'password' => bcrypt('password'), 'role_id' => Role::where('slug', 'supervisor')->value('id'), 'email_verified_at' => now()]
        );

        $deni = User::firstOrCreate(
            ['email' => 'deni@cmms-lite.local'],
            ['name' => 'Deni Teknisi', 'password' => bcrypt('password'), 'role_id' => Role::where('slug', 'technician')->value('id'), 'email_verified_at' => now()]
        );

        User::firstOrCreate(
            ['email' => 'hendra@cmms-lite.local'],
            ['name' => 'Pak Hendra', 'password' => bcrypt('password'), 'role_id' => Role::where('slug', 'plant_manager')->value('id'), 'email_verified_at' => now()]
        );

        // ── Aset "bermasalah" — pusat cerita demo ──
        $turbin1 = Asset::firstOrCreate(
            ['name' => 'Turbin Uap Unit 1'],
            [
                'category' => 'turbine', 'location' => 'Area Powerhouse',
                'installed_at' => now()->subYears(5), 'condition' => 'needs_attention',
                'pm_interval_days' => 90, 'last_pm_at' => now()->subDays(30), 'status' => 'active',
            ]
        );

        // ── Aset "sehat" — pembanding ──
        $sumur1 = Asset::firstOrCreate(
            ['name' => 'Sumur Produksi DRJ-12'],
            [
                'category' => 'well', 'location' => 'Well Pad 3',
                'installed_at' => now()->subYears(3), 'condition' => 'good',
                'pm_interval_days' => 120, 'last_pm_at' => now()->subDays(10), 'status' => 'active',
            ]
        );

        $pipa1 = Asset::firstOrCreate(
            ['name' => 'Pipa Uap Header Utama'],
            [
                'category' => 'pipe', 'location' => 'Jalur Distribusi Utara',
                'installed_at' => now()->subYears(4), 'condition' => 'good',
                'pm_interval_days' => 60, 'last_pm_at' => now()->subDays(5), 'status' => 'active',
            ]
        );

        $cooling1 = Asset::firstOrCreate(
            ['name' => 'Cooling Tower CT-2'],
            [
                'category' => 'cooling_tower', 'location' => 'Area Pendingin',
                'installed_at' => now()->subYears(2), 'condition' => 'good',
                'pm_interval_days' => 90, 'last_pm_at' => null, 'status' => 'active', // sengaja null biar PM otomatis due
            ]
        );

        // ── Narasi Turbin 1: 2 WO corrective closed dengan gap pendek (MTBF jelek) ──
        // Kerusakan 1: Mulai dikerjakan 20 hari lalu -> Selesai 19 hari lalu
        $this->closedCorrectiveWo($turbin1, $rina, $deni, now()->subDays(19), now()->subDays(20));

        // Kerusakan 2: Mulai dikerjakan 5 hari lalu -> Selesai 4 hari lalu
        $this->closedCorrectiveWo($turbin1, $rina, $deni, now()->subDays(4), now()->subDays(5));

        // ── Narasi Turbin 1: WO ke-3 sudah 2x direject, is_escalated = true ──
        WorkOrder::create([
            'asset_id' => $turbin1->id,
            'assigned_to' => $deni->id,
            'created_by' => $rina->id,
            'type' => 'corrective',
            'priority' => 'urgent',
            'status' => 'in_progress',
            'description' => 'Getaran tidak normal pada bearing turbin, sudah 2x perbaikan belum sesuai standar.',
            'rejection_count' => 2,
            'rejection_note' => 'Hasil pengukuran vibrasi masih di atas ambang batas, perlu penggantian bearing bukan sekadar pelumasan.',
            'is_escalated' => true,
        ]);

        // ── WO normal di berbagai status, untuk aset sehat ──
        WorkOrder::create([
            'asset_id' => $sumur1->id, 'created_by' => $rina->id,
            'type' => 'preventive', 'priority' => 'normal', 'status' => 'draft',
            'description' => 'Rencana PM rutin triwulan.',
        ]);

        WorkOrder::create([
            'asset_id' => $pipa1->id, 'assigned_to' => $deni->id, 'created_by' => $rina->id,
            'type' => 'corrective', 'priority' => 'normal', 'status' => 'assigned',
            'description' => 'Ditemukan rembesan kecil pada sambungan pipa saat inspeksi rutin.',
        ]);

        // Cooling Tower: Mulai dikerjakan 60 hari lalu -> Selesai 59 hari lalu
        $this->closedCorrectiveWo($cooling1, $rina, $deni, now()->subDays(59), now()->subDays(60));

        // ── Checklist inspeksi contoh ──
        InspectionChecklist::create([
            'asset_id' => $sumur1->id,
            'inspected_by' => $deni->id,
            'condition_found' => 'good',
            'status' => 'confirmed',
            'created_at' => now(),
        ]);

        InspectionChecklist::create([
            'asset_id' => $turbin1->id,
            'inspected_by' => $deni->id,
            'condition_found' => 'damaged',
            'notes' => 'Suara berdengung tidak biasa dari area bearing, perlu pengecekan lanjutan segera.',
            'status' => 'pending_review',
            'created_at' => now(),
        ]);
    }

    // Helper: WO corrective closed lengkap dengan log in_progress (untuk MTTR)
    // dan completed_at/closed_at presisi (untuk MTBF/downtime).
    private function closedCorrectiveWo(Asset $asset, User $supervisor, User $technician, $completedAt, $inProgressAt): void
    {
        $wo = WorkOrder::create([
            'asset_id' => $asset->id,
            'assigned_to' => $technician->id,
            'created_by' => $supervisor->id,
            'approved_by' => $supervisor->id,
            'type' => 'corrective',
            'priority' => 'normal',
            'status' => 'closed',
            'description' => 'Perbaikan terjadwal dari temuan lapangan.',
            'completed_at' => $completedAt,
            'closed_at' => $completedAt->copy()->addHours(2),
            'created_at' => $inProgressAt->copy()->subHours(3),
        ]);

        $wo->logs()->create([
            'user_id' => $technician->id,
            'from_status' => 'assigned',
            'to_status' => 'in_progress',
            'note' => null,
            'created_at' => $inProgressAt,
        ]);
    }
}
