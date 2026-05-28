<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\ServiceController;
use App\Http\Controllers\Api\DashboardController;
use App\Http\Controllers\Api\TransactionController;

// Rute Publik (Bisa diakses tanpa login)
Route::post('/login', [AuthController::class, 'login']);

// Rute Terproteksi (Hanya bisa diakses jika Flutter mengirimkan TOKEN)
Route::middleware('auth:sanctum')->group(function () {

    // User Profile (Opsional, untuk cek siapa yang login)
    Route::get('/user', function (Request $request) {
        return $request->user();
    });

    // Jasa (Otomatis terfilter per Owner berkat Global Scope)
    Route::get('/services', [ServiceController::class, 'index']);

    // Dashboard & Stats
    Route::get('/dashboard/stats', [DashboardController::class, 'getStats']);
    Route::get('/dashboard/activities', [DashboardController::class, 'getActivities']);

    // Driver & Pickup Tasks (Integrated into TransactionController)
    Route::post('/pickups', [TransactionController::class, 'requestPickup']);
    Route::get('/driver/tasks', [TransactionController::class, 'getDriverTasks']);
    Route::put('/pickups/{id}/status', [TransactionController::class, 'updateStatus']);
    Route::get('/pickups/arrived', [TransactionController::class, 'getOrdersByStatus'])->defaults('status', 'JEMPUTAN TIBA');

    // Transaksi & Order
    Route::get('/transactions', [TransactionController::class, 'index']);
    Route::post('/transactions', [TransactionController::class, 'store']);
    Route::get('/orders/status/{status}', [TransactionController::class, 'getOrdersByStatus']);
    Route::put('/orders/{id}/update-status', [TransactionController::class, 'updateStatus']);
    Route::post('/send-whatsapp', [TransactionController::class, 'sendManualWhatsApp']);
    Route::post('/check-delivery-fee', [TransactionController::class, 'checkDeliveryFee']);

    // Pengaturan Toko (diakses oleh Staf dari aplikasi mobile)
    Route::get('/shop/settings', [\App\Http\Controllers\Api\ShopSettingsController::class, 'getSettings']);
    Route::put('/shop/settings', [\App\Http\Controllers\Api\ShopSettingsController::class, 'updateSettings']);
});
