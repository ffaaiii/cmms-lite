<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;

class SystemUserSeeder extends Seeder
{
    // User teknis untuk created_by pada WO yang digenerate otomatis oleh
    // scheduler (bukan role RBAC baru — role_id tetap 'admin', ini murni
    // workaround FK not null karena sistem bukan aktor RBAC).
    public function run(): void
    {
        $adminRole = Role::where('slug', 'admin')->firstOrFail();

        User::firstOrCreate(
            ['email' => 'system@cmms-lite.local'],
            [
                'name' => 'Sistem CMMS',
                'password' => bcrypt(str()->random(40)), // tidak pernah dipakai login
                'role_id' => $adminRole->id,
                'email_verified_at' => now(),
            ]
        );
    }
}
