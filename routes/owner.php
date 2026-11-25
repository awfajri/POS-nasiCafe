<?php

use App\Http\Controllers\OwnerDashboardController;
use Illuminate\Support\Facades\Route;

// Route Dashboard Owner & Laporan Keuangan
Route::middleware(['auth', 'role:owner'])
    ->prefix('owner')
    ->name('owner.')
    ->group(function (): void {
        Route::get('/dashboard', [OwnerDashboardController::class, 'index'])
            ->name('dashboard');
    });
