@extends('layouts.guest')

@section('title', 'Masuk')

@section('content')
    <div class="rounded-4xl border border-surface-border bg-surface-card p-5">

        <form method="POST" action="{{ route('login.attempt') }}"
            x-data="{ show: false, loading: false }"
            @submit="loading = true"
            @pageshow.window="loading = false">
            @csrf

            <div class="mb-3.5">
                <label for="email" class="block text-[13px] font-medium text-ink-primary mb-1.5">
                    Email
                </label>
                <input
                    id="email"
                    name="email"
                    type="email"
                    value="{{ old('email') }}"
                    required
                    autofocus
                    autocomplete="username"
                    placeholder="masukkan email"
                    class="block w-full h-11 rounded-xl border px-3.5 text-[13px] bg-surface-light text-ink-primary placeholder:text-ink-muted/75 focus:border-brand-500 focus:bg-surface-card focus:outline-none transition {{ $errors->has('email') ? 'border-status-danger' : 'border-surface-border' }}"
                >
                @error('email')
                    <p class="mt-1 text-xs text-status-danger">{{ $message }}</p>
                @enderror
            </div>

            <div class="mb-3.5">
                <label for="password" class="block text-[13px] font-medium text-ink-primary mb-1.5">
                    Password
                </label>
                <div class="relative">
                    <input
                        id="password"
                        name="password"
                        type="password"
                        :type="show ? 'text' : 'password'"
                        required
                        autocomplete="current-password"
                        placeholder="masukkan password"
                        class="block w-full h-11 rounded-xl border pl-3.5 pr-10 text-[13px] bg-surface-light text-ink-primary placeholder:text-ink-muted/75 focus:border-brand-500 focus:bg-surface-card focus:outline-none transition {{ $errors->has('password') ? 'border-status-danger' : 'border-surface-border' }}"
                    >
                    <button
                        type="button"
                        @click="show = !show"
                        aria-label="Tampilkan atau sembunyikan password"
                        class="absolute inset-y-0 right-0 flex items-center pr-3.5 text-ink-primary hover:text-brand-700 focus:outline-none"
                    >
                        <svg x-show="!show" xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94"/>
                            <path d="M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19"/>
                            <line x1="1" y1="1" x2="23" y2="23"/>
                        </svg>
                        <svg x-show="show" x-cloak xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/>
                            <circle cx="12" cy="12" r="3"/>
                        </svg>
                    </button>
                </div>
                @error('password')
                    <p class="mt-1 text-xs text-status-danger">{{ $message }}</p>
                @enderror
            </div>

            <div class="flex items-center justify-between mb-4">
                <label for="remember" class="inline-flex items-center gap-2 cursor-pointer select-none">
                    <input
                        id="remember"
                        name="remember"
                        type="checkbox"
                        {{ old('remember') ? 'checked' : '' }}
                        class="h-3.5 w-3.5 rounded-[3px] border border-ink-primary/70 bg-surface-card text-brand-700 focus:ring-brand-500"
                    >
                    <span class="text-[12.5px] text-ink-primary">Ingat saya</span>
                </label>

                @if (Route::has('password.request'))
                    <a href="{{ route('password.request') }}" class="text-[12.5px] font-medium text-brand-700 hover:text-brand-900 hover:underline">
                        Lupa password?
                    </a>
                @else
                    <a href="#" class="text-[12.5px] font-medium text-brand-700 hover:text-brand-900 hover:underline">
                        Lupa password?
                    </a>
                @endif
            </div>

            <button
                type="submit"
                :disabled="loading"
                class="w-full h-11 rounded-xl bg-brand-900 px-4 text-[14px] font-medium text-surface-card hover:bg-brand-700 focus:outline-none focus:ring-2 focus:ring-brand-500/30 disabled:cursor-not-allowed disabled:opacity-60 transition"
                x-text="loading ? 'Memproses...' : 'Masuk'"
            >
                Masuk
            </button>

            <p class="mt-3.5 text-center text-[12.5px] text-ink-primary">
                Belum Punya Akun?
                <a href="{{ route('register') }}" class="font-medium text-brand-700 hover:text-brand-900 hover:underline">Daftar</a>
            </p>
        </form>
    </div>
@endsection