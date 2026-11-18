<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class RoleMiddleware
{
    /**
     * Pemakaian di route: ->middleware(['auth', 'role:owner,cashier'])
     */
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $user = $request->user();

        // 1. Belum login
        if (! $user) {
            return redirect()->route('login');
        }

        // 2. Akun dinonaktifkan Owner: keluarkan, termasuk kalau sesinya masih aktif
        if (! $user->is_active) {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()->route('login')->with('error', 'Akun Anda telah dinonaktifkan');
        }

        // 3. Peran tidak cocok (daftar peran kosong juga ditolak)
        if (! in_array($user->role, $roles, true)) {
            abort(403, 'Akses ditolak');
        }

        return $next($request);
    }
}