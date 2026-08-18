<?php

use App\Http\Controllers\AssetController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\WorkOrderController;
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

// Admin Routes
Route::middleware(['auth', 'role:admin'])->prefix('admin')->group(function () {
    Route::get('/users', fn () => 'Placeholder: Admin Users Page')->name('admin.users.index');
    Route::resource('assets', AssetController::class)->except(['show'])->names('admin.assets');
});

// Supervisor Routes
Route::middleware(['auth', 'role:supervisor'])->prefix('supervisor')->name('supervisor.')->group(function () {
    Route::get('/dashboard', fn () => 'Placeholder: Supervisor Dashboard')->name('dashboard');
    Route::get('/assets', [AssetController::class, 'supervisorIndex'])->name('assets.index');
    Route::patch('/assets/{asset}/condition', [AssetController::class, 'updateCondition'])->name('assets.updateCondition');

    // Work Orders Route (Supervisor)
    Route::get('/work-orders', [WorkOrderController::class, 'index'])->name('work-orders.index');
    Route::get('/work-orders/create', [WorkOrderController::class, 'create'])->name('work-orders.create');
    Route::post('/work-orders', [WorkOrderController::class, 'store'])->name('work-orders.store');
    Route::get('/work-orders/{workOrder}', [WorkOrderController::class, 'show'])->name('work-orders.show');
    Route::patch('/work-orders/{workOrder}/assign', [WorkOrderController::class, 'assign'])->name('work-orders.assign');
});

// Technician Routes
Route::middleware(['auth', 'role:technician'])->prefix('technician')->name('technician.')->group(function () {
    Route::get('/assets', [AssetController::class, 'technicianIndex'])->name('assets.index');

    // Work Orders / Tasks Route (Technician)
    Route::get('/tasks', [WorkOrderController::class, 'index'])->name('tasks.index');
    Route::get('/tasks/{workOrder}', [WorkOrderController::class, 'show'])->name('tasks.show');
});

// Executive Routes
Route::middleware(['auth', 'role:plant_manager'])->prefix('executive')->group(function () {
    Route::get('/dashboard', fn () => 'Placeholder: Executive Dashboard')->name('executive.dashboard');
});

require __DIR__.'/auth.php';