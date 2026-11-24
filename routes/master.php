<?php

use App\Http\Controllers\CategoryController;
use App\Http\Controllers\MenuController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'role:owner'])->prefix('owner')->name('owner.')->group(function () {
    Route::resource('categories', CategoryController::class)->except('show');
    Route::resource('menus', MenuController::class)->except('show');
    Route::patch('menus/{menu}/stock', [MenuController::class, 'updateStock'])->name('menus.stock');
});
