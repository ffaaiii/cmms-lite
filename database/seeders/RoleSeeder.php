<?php

namespace Database\Seeders;

use App\Models\Role;
use Illuminate\Database\Seeder;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        $roles = [
            ['name' => 'Admin', 'slug' => 'admin'],
            ['name' => 'Supervisor', 'slug' => 'supervisor'],
            ['name' => 'Teknisi', 'slug' => 'technician'],
            ['name' => 'Plant Manager', 'slug' => 'plant_manager'],
        ];

        foreach ($roles as $role) {
            Role::firstOrCreate(['slug' => $role['slug']], $role);
        }
    }
}
