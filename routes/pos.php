<?php

use App\Http\Controllers\CustomerOrderController;
use App\Http\Controllers\PosController;
use Illuminate\Support\Facades\Route;

// Grup Layar Kasir POS
Route::middleware(['auth', 'role:cashier'])->group(function () {
    Route::get('/pos', [PosController::class, 'index'])->name('pos.index');
});

// Grup Self-Order Pelanggan
Route::middleware(['auth', 'role:customer'])->group(function () {
    Route::get('/customer/order', [CustomerOrderController::class, 'index'])->name('customer.order');
    Route::post('/customer/order', [CustomerOrderController::class, 'store'])->name('customer.order.store');
});
