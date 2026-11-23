@extends('layouts.app')

@section('title', 'Ubah Kata Sandi')

@section('content')
    @php
        $input = 'block w-full rounded-xl border-0 bg-surface-light py-2.5 pl-3.5 pr-12 text-base text-ink-primary ring-1 ring-inset placeholder:text-ink-muted focus:bg-white focus:outline-none focus:ring-2 focus:ring-inset focus:ring-brand-600';
        $label = 'mb-1.5 block text-sm font-medium text-ink-primary';
        $toggle = 'absolute inset-y-0 right-0 flex items-center px-3.5 text-ink-muted transition hover:text-ink-primary';
    @endphp

    <div class="mx-auto max-w-xl space-y-6">
        <div>
            <h1 class="text-2xl font-semibold text-ink-primary">Ubah Kata Sandi</h1>
            <p class="mt-1 text-sm text-ink-muted">Gunakan kata sandi baru yang berbeda dan minimal 8 karakter.</p>
        </div>

        <x-alert />

        <form method="POST" action="{{ route('password.update') }}"
                x-data="{ showCurrent: false, showNew: false, loading: false }"
                @submit="loading = true"
                @pageshow.window="loading = false"
                class="space-y-4 rounded-2xl bg-surface-card p-6 ring-1 ring-surface-border">
            @csrf
            @method('PUT')

            <div>
                <label for="current_password" class="{{ $label }}">Kata sandi saat ini</label>
                <div class="relative">
                    <input id="current_password" name="current_password" type="password"
                            :type="showCurrent ? 'text' : 'password'" required autocomplete="current-password"
                            class="{{ $input }} {{ $errors->has('current_password') ? 'ring-status-danger' : 'ring-surface-border' }}">
                    <button type="button" @click="showCurrent = !showCurrent" class="{{ $toggle }}"
                            :aria-label="showCurrent ? 'Sembunyikan kata sandi' : 'Tampilkan kata sandi'"
                            aria-label="Tampilkan kata sandi">
                        <x-lucide-eye-off x-show="!showCurrent" class="size-5" />
                        <x-lucide-eye x-show="showCurrent" class="size-5" style="display: none" />
                    </button>
                </div>
                @error('current_password')
                    <p class="mt-1.5 text-sm text-status-danger">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="password" class="{{ $label }}">Kata sandi baru</label>
                <div class="relative">
                    <input id="password" name="password" type="password"
                            :type="showNew ? 'text' : 'password'" required minlength="8" autocomplete="new-password"
                            placeholder="Minimal 8 karakter"
                            class="{{ $input }} {{ $errors->has('password') ? 'ring-status-danger' : 'ring-surface-border' }}">
                    <button type="button" @click="showNew = !showNew" class="{{ $toggle }}"
                            :aria-label="showNew ? 'Sembunyikan kata sandi' : 'Tampilkan kata sandi'"
                            aria-label="Tampilkan kata sandi">
                        <x-lucide-eye-off x-show="!showNew" class="size-5" />
                        <x-lucide-eye x-show="showNew" class="size-5" style="display: none" />
                    </button>
                </div>
                @error('password')
                    <p class="mt-1.5 text-sm text-status-danger">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="password_confirmation" class="{{ $label }}">Ulangi kata sandi baru</label>
                <input id="password_confirmation" name="password_confirmation" type="password"
                        :type="showNew ? 'text' : 'password'" required autocomplete="new-password"
                        class="{{ $input }} ring-surface-border">
            </div>

            <button type="submit" :disabled="loading"
                    class="w-full rounded-xl bg-brand-600 px-5 py-2.5 text-sm font-semibold text-white transition hover:bg-brand-700 focus:outline-none focus-visible:ring-2 focus-visible:ring-brand-600 focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-60"
                    x-text="loading ? 'Menyimpan...' : 'Simpan Kata Sandi'">Simpan Kata Sandi</button>
        </form>
    </div>
@endsection