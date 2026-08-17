<?php

use App\Models\Role;
use App\Models\User;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed(RoleSeeder::class);
});

function makeUserWithRole(string $slug): User
{
    $role = Role::where('slug', $slug)->first();

    return User::factory()->create(['role_id' => $role->id]);
}

test('admin bisa akses halaman admin', function () {
    $admin = makeUserWithRole('admin');

    $response = $this->actingAs($admin)->get('/admin/users');

    $response->assertOk();
});

test('teknisi ditolak akses halaman admin', function () {
    $technician = makeUserWithRole('technician');

    $response = $this->actingAs($technician)->get('/admin/users');

    $response->assertForbidden();
});

test('supervisor ditolak akses halaman admin', function () {
    $supervisor = makeUserWithRole('supervisor');

    $response = $this->actingAs($supervisor)->get('/admin/users');

    $response->assertForbidden();
});

test('plant manager ditolak akses halaman teknisi', function () {
    $plantManager = makeUserWithRole('plant_manager');

    $response = $this->actingAs($plantManager)->get('/technician/tasks');

    $response->assertForbidden();
});

test('teknisi bisa akses halaman miliknya sendiri', function () {
    $technician = makeUserWithRole('technician');

    $response = $this->actingAs($technician)->get('/technician/tasks');

    $response->assertOk();
});

test('guest (belum login) diarahkan ke login saat akses halaman terproteksi', function () {
    $response = $this->get('/admin/users');

    $response->assertRedirect('/login');
});
