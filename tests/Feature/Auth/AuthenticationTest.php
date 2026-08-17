<?php

use App\Models\Role;
use App\Models\User;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed(RoleSeeder::class);
});

test('user dapat login dengan kredensial benar', function () {
    $role = Role::where('slug', 'technician')->first();
    $user = User::factory()->create([
        'role_id' => $role->id,
        'password' => bcrypt('password123'),
    ]);

    $response = $this->post('/login', [
        'email' => $user->email,
        'password' => 'password123',
    ]);

    $this->assertAuthenticatedAs($user);
    $response->assertRedirect(route('technician.tasks.index'));
});

test('user tidak dapat login dengan password salah', function () {
    $role = Role::where('slug', 'technician')->first();
    $user = User::factory()->create([
        'role_id' => $role->id,
        'password' => bcrypt('password123'),
    ]);

    $response = $this->post('/login', [
        'email' => $user->email,
        'password' => 'password-salah',
    ]);

    $this->assertGuest();
    $response->assertSessionHasErrors();
});

test('user diarahkan ke dashboard sesuai role masing-masing setelah login', function () {
    $cases = [
        'admin' => 'admin.users.index',
        'supervisor' => 'supervisor.dashboard',
        'technician' => 'technician.tasks.index',
        'plant_manager' => 'executive.dashboard',
    ];

    foreach ($cases as $slug => $routeName) {
        $role = Role::where('slug', $slug)->first();
        $user = User::factory()->create([
            'role_id' => $role->id,
            'password' => bcrypt('password123'),
        ]);

        $response = $this->post('/login', [
            'email' => $user->email,
            'password' => 'password123',
        ]);

        $response->assertRedirect(route($routeName));

        $this->post('/logout');
    }
});

test('route register bawaan Breeze sudah mati (404)', function () {
    $response = $this->get('/register');

    $response->assertNotFound();
});
