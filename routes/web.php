<?php

use App\Http\Controllers\AssetController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\InspectionChecklistController;
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
    Route::get('/dashboard', [DashboardController::class, 'supervisor'])->name('dashboard');
    Route::get('/assets', [AssetController::class, 'supervisorIndex'])->name('assets.index');
    Route::patch('/assets/{asset}/condition', [AssetController::class, 'updateCondition'])->name('assets.updateCondition');

    // Work Orders Route (Supervisor)
    Route::get('/work-orders', [WorkOrderController::class, 'index'])->name('work-orders.index');
    Route::get('/work-orders/create', [WorkOrderController::class, 'create'])->name('work-orders.create');
    Route::post('/work-orders', [WorkOrderController::class, 'store'])->name('work-orders.store');
    Route::get('/work-orders/{workOrder}', [WorkOrderController::class, 'show'])->name('work-orders.show');
    Route::patch('/work-orders/{workOrder}/assign', [WorkOrderController::class, 'assign'])->name('work-orders.assign');
    Route::patch('/work-orders/{workOrder}/approve', [WorkOrderController::class, 'approve'])->name('work-orders.approve');
    Route::patch('/work-orders/{workOrder}/reject', [WorkOrderController::class, 'reject'])->name('work-orders.reject');

    // Inspection Checklists Route (Supervisor)
    Route::get('/checklists', [InspectionChecklistController::class, 'index'])->name('checklists.index');
    Route::patch('/checklists/{checklist}/confirm', [InspectionChecklistController::class, 'confirm'])->name('checklists.confirm');
    Route::patch('/checklists/{checklist}/dismiss', [InspectionChecklistController::class, 'dismiss'])->name('checklists.dismiss');
});

// Technician Routes
Route::middleware(['auth', 'role:technician'])->prefix('technician')->name('technician.')->group(function () {
    Route::get('/assets', [AssetController::class, 'technicianIndex'])->name('assets.index');

    // Work Orders / Tasks Route (Technician)
    Route::get('/tasks', [WorkOrderController::class, 'index'])->name('tasks.index');
    Route::get('/tasks/{workOrder}', [WorkOrderController::class, 'show'])->name('tasks.show');
    Route::patch('/tasks/{workOrder}/transition', [WorkOrderController::class, 'transition'])->name('tasks.transition');

    // Inspection Checklists Route (Technician)
    Route::get('/checklists/create', [InspectionChecklistController::class, 'create'])->name('checklists.create');
    Route::post('/checklists', [InspectionChecklistController::class, 'store'])->name('checklists.store');
});

// Executive Routes
Route::middleware(['auth', 'role:plant_manager'])->prefix('executive')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'executive'])->name('executive.dashboard');
});

require __DIR__.'/auth.php';
