<?php

namespace App\Http\Controllers;

use App\Http\Requests\LoginRequest;
use App\Http\Requests\RegisterRequest;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AuthController extends Controller
{
    public function showLogin(): View
    {
        return view('auth.login');
    }

    public function login(LoginRequest $request): RedirectResponse
    {
        // is_active => true: akun nonaktif gagal login dengan pesan yang sama
        // seperti sandi salah, jadi status akun tidak bocor.
        $credentials = $request->validated() + ['is_active' => true];

        if (! Auth::attempt($credentials, $request->boolean('remember'))) {
            return back()
                ->withInput($request->only('email'))
                ->with('error', 'Email atau kata sandi salah, atau akun nonaktif');
        }

        // Ganti ID sesi setelah login (mencegah session fixation).
        $request->session()->regenerate();

        return redirect()->route(Auth::user()->homeRoute());
    }

    public function showRegister(): View
    {
        return view('auth.register');
    }

    public function register(RegisterRequest $request): RedirectResponse
    {
        // validated() hanya berisi name, email, password. Peran tidak pernah dari request.
        $user = new User($request->validated());
        $user->role = User::ROLE_CUSTOMER;
        $user->is_active = true;
        $user->save();

        Auth::login($user);
        $request->session()->regenerate();

        return redirect()->route('customer.dashboard');
    }

    public function logout(Request $request): RedirectResponse
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login')->with('success', 'Anda sudah keluar.');
    }
}