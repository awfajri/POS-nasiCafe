<?php

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    if (! Auth::check()) {
        return Route::has('login') ? redirect()->route('login') : redirect('/login');
    }

    /** @var \App\Models\User|null $user */
    $user = Auth::user();

    if ($user && method_exists($user, 'homeRoute')) {
        $homeRoute = $user->homeRoute();

        if (is_string($homeRoute) && $homeRoute !== '' && Route::has($homeRoute)) {
            return redirect()->route($homeRoute);
        }
    }

    $targetRoute = match ($user->role ?? null) {
        'owner' => 'owner.dashboard',
        'cashier' => 'cashier.dashboard',
        'customer' => 'customer.dashboard',
        default => abort(403, 'Peran pengguna tidak memiliki akses.'),
    };

    return Route::has($targetRoute)
        ? redirect()->route($targetRoute)
        : abort(503, "Rute [{$targetRoute}] belum tersedia.");
});

require __DIR__.'/auth.php';
require __DIR__.'/master.php';
require __DIR__.'/owner.php';
require __DIR__.'/transactions.php';
require __DIR__.'/payments.php';
require __DIR__.'/pos.php';