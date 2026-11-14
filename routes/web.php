<?php

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    if (! Auth::check()) {
        return Route::has('login') ? redirect()->route('login') : redirect('/login');
    }

    /** @var \App\Models\User $user */
    $user = Auth::user();

    if ($user && method_exists($user, 'homeRoute') && Route::has($user->homeRoute())) {
        return redirect()->route($user->homeRoute());
    }

    return match ($user->role ?? null) {
        'owner' => Route::has('owner.dashboard') ? redirect()->route('owner.dashboard') : abort(403),
        'cashier' => Route::has('cashier.dashboard') ? redirect()->route('cashier.dashboard') : abort(403),
        'customer' => Route::has('customer.dashboard') ? redirect()->route('customer.dashboard') : abort(403),
        default => abort(403),
    };
});

require __DIR__.'/auth.php';
require __DIR__.'/master.php';
require __DIR__.'/owner.php';
require __DIR__.'/transactions.php';
require __DIR__.'/payments.php';
require __DIR__.'/pos.php';
