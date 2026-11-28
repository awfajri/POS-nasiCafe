<?php

use App\Http\Controllers\OwnerDashboardController;
use App\Http\Controllers\ReportController;
use Illuminate\Support\Facades\Route;

// Route Dashboard Owner & Laporan Keuangan
Route::middleware(['auth', 'role:owner'])
    ->prefix('owner')
    ->name('owner.')
    ->group(function (): void {
        Route::get('/dashboard', [OwnerDashboardController::class, 'index'])
            ->name('dashboard');

        Route::get('/reports', [ReportController::class, 'index'])
            ->name('reports.index');

        Route::get('/reports/export/excel', [ReportController::class, 'exportExcel'])
            ->name('reports.excel');

        Route::get('/reports/export/pdf', [ReportController::class, 'exportPdf'])
            ->name('reports.pdf');
    });

