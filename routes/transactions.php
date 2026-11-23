<?php

use App\Http\Controllers\CashierDashboardController;
use App\Http\Controllers\TransactionController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'role:cashier'])->group(function (): void {
    Route::get('/cashier/dashboard', [CashierDashboardController::class, 'index'])
        ->name('cashier.dashboard');

    Route::post('/pos/orders', [TransactionController::class, 'storeFromPos'])
        ->name('pos.store');

    Route::patch('/transactions/{transaction}/status', [TransactionController::class, 'updateStatus'])
        ->name('transactions.status');
});

Route::middleware(['auth', 'role:owner,cashier'])->group(function (): void {
    Route::get('/transactions', [TransactionController::class, 'index'])
        ->name('transactions.index');

    Route::get('/transactions/{transaction}', [TransactionController::class, 'show'])
        ->name('transactions.show');
});
