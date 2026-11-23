<?php

use App\Http\Controllers\PaymentController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'role:cashier'])->group(function () {
    Route::get('/payments/{transaction}/checkout', [PaymentController::class, 'checkout'])
        ->name('payments.checkout');

    Route::post('/payments/{transaction}/pay-full', [PaymentController::class, 'payFull'])
        ->name('payments.pay-full');

    Route::post('/payments/{transaction}/pay-split', [PaymentController::class, 'paySplit'])
        ->name('payments.pay-split');

    Route::post('/payments/{transaction}/confirm-qris', [PaymentController::class, 'confirmQris'])
        ->name('payments.confirm-qris');
});

Route::middleware(['auth', 'role:owner,cashier'])->group(function () {
    Route::get('/payments/{payment}/receipt', [PaymentController::class, 'receipt'])
        ->name('payments.receipt');
});