<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpdatePasswordRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Str;
use Illuminate\View\View;

class PasswordController extends Controller
{
    public function edit(): View
    {
        return view('profile.change-password');
    }

    public function update(UpdatePasswordRequest $request): RedirectResponse
    {
        $user = $request->user();

        // Cast hashed yang melakukan hash, jangan hash dua kali.
        $user->password = $request->validated('password');

        // Ganti token "ingat saya" supaya perangkat lain harus login ulang.
        $user->setRememberToken(Str::random(60));
        $user->save();

        // Ganti ID sesi setelah perubahan kredensial.
        $request->session()->regenerate();

        return redirect()->route('password.edit')
            ->with('success', 'Kata sandi berhasil diperbarui.');
    }
}