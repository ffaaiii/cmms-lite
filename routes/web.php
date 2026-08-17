<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canLogin' => Route::has('login'),
        'canRegister' => Route::has('register'),
        'laravelVersion' => Application::VERSION,
        'phpVersion' => PHP_VERSION,
    ]);
});

Route::get('/dashboard', function () {
    return Inertia::render('Dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

// Placeholder Routes untuk Testing Role Redirect & Middleware
Route::middleware(['auth', 'role:admin'])->prefix('admin')->group(function () {
    Route::get('/users', fn () => 'Placeholder: Admin Users Page')->name('admin.users.index');
});

Route::middleware(['auth', 'role:supervisor'])->prefix('supervisor')->group(function () {
    Route::get('/dashboard', fn () => 'Placeholder: Supervisor Dashboard')->name('supervisor.dashboard');
});

Route::middleware(['auth', 'role:technician'])->prefix('technician')->group(function () {
    Route::get('/tasks', fn () => 'Placeholder: Technician Tasks')->name('technician.tasks.index');
});

Route::middleware(['auth', 'role:plant_manager'])->prefix('executive')->group(function () {
    Route::get('/dashboard', fn () => 'Placeholder: Executive Dashboard')->name('executive.dashboard');
});

require __DIR__.'/auth.php';
