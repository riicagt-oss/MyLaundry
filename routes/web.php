<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\StaffController;
use App\Http\Controllers\LaundryController;
use App\Http\Controllers\ReportController; 
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\OrderListController;

// 1. Landing Page langsung ke Login
Route::get('/', function () {
    return redirect()->route('login');
});

// 3. Route yang memerlukan Login DAN harus ROLE OWNER
Route::middleware(['auth', 'verified', 'owner'])->group(function () {

    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/orders/status/{status}', [OrderListController::class, 'showByStatus'])->name('orders.by_status');
    Route::delete('/orders/bulk-delete', [OrderListController::class, 'bulkDelete'])->name('orders.bulkDelete');

    // --- MANAJEMEN LAUNDRY ---
    Route::get('/laundry', [LaundryController::class, 'index'])->name('laundry');
    Route::get('/laundry/create', [LaundryController::class, 'create'])->name('laundry.create');
    Route::post('/laundry', [LaundryController::class, 'store'])->name('laundry.store');
    Route::get('/laundry/{id}/edit', [LaundryController::class, 'edit'])->name('laundry.edit');
    Route::put('/laundry/{id}', [LaundryController::class, 'update'])->name('laundry.update');
    Route::delete('/laundry/{id}', [LaundryController::class, 'destroy'])->name('laundry.destroy');
    Route::post('/laundry/status/{id}', [LaundryController::class, 'updateStatus'])->name('laundry.updateStatus');

    // --- LAPORAN PENDAPATAN ---
    Route::get('/reports', [ReportController::class, 'index'])->name('reports.index');
    Route::get('/reports/export', [ReportController::class, 'export'])->name('reports.export');
    Route::post('/reports/export-bulk', [ReportController::class, 'exportBulk'])->name('reports.exportBulk');
    Route::delete('/reports/bulk-delete', [ReportController::class, 'bulkDelete'])->name('reports.bulkDelete');

    // --- MANAJEMEN STAF & PENGATURAN ---
    Route::get('/settings', [UserController::class, 'settings'])->name('settings');
    Route::put('/settings/shop', [UserController::class, 'updateShopSettings'])->name('shop.settings.update');
    Route::get('/staff/create', [StaffController::class, 'create'])->name('staff.create');
    Route::post('/staff/store', [StaffController::class, 'store'])->name('staff.store');
    Route::get('/staff/{id}/edit', [StaffController::class, 'edit'])->name('staff.edit');
    Route::put('/staff/{id}', [StaffController::class, 'update'])->name('staff.update');
    Route::delete('/staff/{id}', [StaffController::class, 'destroy'])->name('staff.destroy');

    // Profile
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__ . '/auth.php';