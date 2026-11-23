@extends('layouts.app')

@section('title', $user->exists ? 'Ubah Pengguna' : 'Tambah Pengguna')

@section('content')
    @php
        $input = 'mt-1.5 block w-full rounded-xl border-0 bg-surface-light px-3.5 py-2.5 text-base text-ink-primary ring-1 ring-inset placeholder:text-ink-muted focus:bg-white focus:outline-none focus:ring-2 focus:ring-inset focus:ring-brand-600';
        $label = 'block text-sm font-medium text-ink-primary';
    @endphp

    <div class="mx-auto max-w-xl space-y-6">
        <a href="{{ route('owner.users.index') }}"
            class="inline-flex items-center gap-1.5 text-sm text-ink-muted transition hover:text-ink-primary">
            <x-lucide-arrow-left class="size-4" />
            Kembali ke daftar
        </a>

        <div>
            <h1 class="text-2xl font-semibold text-ink-primary">{{ $user->exists ? 'Ubah Pengguna' : 'Tambah Pengguna' }}</h1>
            <p class="mt-1 text-sm text-ink-muted">
                {{ $user->exists ? 'Perbarui data akun. Kosongkan kata sandi kalau tidak ingin mengubahnya.' : 'Buat akun baru untuk kasir atau pelanggan.' }}
            </p>
        </div>

        <form method="POST"
                action="{{ $user->exists ? route('owner.users.update', $user) : route('owner.users.store') }}"
                x-data="{ show: false, loading: false }"
                @submit="loading = true"
                @pageshow.window="loading = false"
                class="space-y-4 rounded-2xl bg-surface-card p-6 ring-1 ring-surface-border">
            @csrf
            @if ($user->exists)
                @method('PUT')
            @endif

            <div>
                <label for="name" class="{{ $label }}">Nama</label>
                <input id="name" name="name" type="text" value="{{ old('name', $user->name) }}"
                        required maxlength="100" autocomplete="off" placeholder="Nama lengkap"
                        class="{{ $input }} {{ $errors->has('name') ? 'ring-status-danger' : 'ring-surface-border' }}">
                @error('name')
                    <p class="mt-1.5 text-sm text-status-danger">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="email" class="{{ $label }}">Email</label>
                <input id="email" name="email" type="email" value="{{ old('email', $user->email) }}"
                        required maxlength="255" autocomplete="off" inputmode="email"
                        autocapitalize="none" spellcheck="false" placeholder="nama@email.com"
                        class="{{ $input }} {{ $errors->has('email') ? 'ring-status-danger' : 'ring-surface-border' }}">
                @error('email')
                    <p class="mt-1.5 text-sm text-status-danger">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="password" class="{{ $label }}">
                    Kata sandi
                    @if ($user->exists)
                        <span class="font-normal text-ink-muted">(opsional)</span>
                    @endif
                </label>
                <div class="relative">
                    <input id="password" name="password" type="password" :type="show ? 'text' : 'password'"
                            @if (! $user->exists) required @endif
                            minlength="8" autocomplete="new-password"
                            placeholder="{{ $user->exists ? 'Kosongkan jika tidak diubah' : 'Minimal 8 karakter' }}"
                            class="{{ $input }} pr-12 {{ $errors->has('password') ? 'ring-status-danger' : 'ring-surface-border' }}">
                    <button type="button" @click="show = !show"
                            :aria-label="show ? 'Sembunyikan kata sandi' : 'Tampilkan kata sandi'"
                            aria-label="Tampilkan kata sandi"
                            class="absolute inset-y-0 right-0 flex items-center px-3.5 text-ink-muted transition hover:text-ink-primary">
                        <x-lucide-eye-off x-show="!show" class="size-5" />
                        <x-lucide-eye x-show="show" class="size-5" style="display: none" />
                    </button>
                </div>
                @error('password')
                    <p class="mt-1.5 text-sm text-status-danger">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="role" class="{{ $label }}">Peran</label>
                @if ($isSelf)
                    <input type="hidden" name="role" value="{{ $user->role }}">
                    <p class="mt-1.5 rounded-xl bg-surface-light px-3.5 py-2.5 text-base text-ink-primary ring-1 ring-inset ring-surface-border">
                        {{ $roleLabels[$user->role] ?? $user->role }}
                        <span class="block text-xs text-ink-muted">Peran akun sendiri tidak dapat diubah.</span>
                    </p>
                @else
                    <div
                        x-data="{
                            open: false,
                            selected: @js(old('role', $user->role ?? 'cashier')),
                            labels: @js($roleLabels)
                        }"
                        @click.outside="open = false"
                        @keydown.escape.window="open = false"
                        class="relative mt-1.5"
                    >
                        <input type="hidden" id="role" name="role" :value="selected">

                        <button
                            type="button"
                            @click="open = !open"
                            :aria-expanded="open.toString()"
                            aria-haspopup="listbox"
                            class="flex w-full items-center justify-between rounded-xl border-0 bg-surface-light py-2.5 pl-3.5 pr-3.5 text-left text-base text-ink-primary ring-1 ring-inset transition focus:bg-white focus:outline-none focus:ring-2 focus:ring-inset focus:ring-brand-600 {{ $errors->has('role') ? 'ring-status-danger' : 'ring-surface-border' }}"
                        >
                            <span x-text="labels[selected] ?? selected"></span>
                            <span class="inline-flex text-ink-muted transition-transform duration-200" :class="open ? 'rotate-180 text-brand-600' : ''">
                                <x-lucide-chevron-down class="size-5" />
                            </span>
                        </button>

                        <div
                            x-show="open"
                            x-transition:enter="transition ease-out duration-150"
                            x-transition:enter-start="opacity-0 -translate-y-1"
                            x-transition:enter-end="opacity-100 translate-y-0"
                            x-transition:leave="transition ease-in duration-100"
                            x-transition:leave-start="opacity-100 translate-y-0"
                            x-transition:leave-end="opacity-0 -translate-y-1"
                            style="display: none"
                            role="listbox"
                            class="absolute z-20 mt-1.5 w-full overflow-hidden rounded-xl bg-surface-card p-1 shadow-lg ring-1 ring-surface-border"
                        >
                            @foreach ($roles as $value)
                                <button
                                    type="button"
                                    role="option"
                                    :aria-selected="(selected === @js($value)).toString()"
                                    @click="selected = @js($value); open = false"
                                    class="flex w-full items-center justify-between rounded-lg px-3 py-2 text-left text-sm transition"
                                    :class="selected === @js($value)
                                        ? 'bg-brand-50 font-semibold text-brand-700'
                                        : 'text-ink-primary hover:bg-surface-light'"
                                >
                                    <span>{{ $roleLabels[$value] ?? $value }}</span>
                                    <span x-show="selected === @js($value)" style="display: none" class="inline-flex text-brand-600">
                                        <x-lucide-check class="size-4" />
                                    </span>
                                </button>
                            @endforeach
                        </div>
                    </div>
                @endif
                @error('role')
                    <p class="mt-1.5 text-sm text-status-danger">{{ $message }}</p>
                @enderror
            </div>

            <div class="flex flex-col-reverse gap-3 pt-2 sm:flex-row sm:justify-end">
                <a href="{{ route('owner.users.index') }}"
                    class="rounded-xl px-5 py-2.5 text-center text-sm font-semibold text-ink-muted ring-1 ring-inset ring-surface-border transition hover:bg-surface-light">
                    Batal
                </a>
                <button type="submit" :disabled="loading"
                        class="rounded-xl bg-brand-600 px-5 py-2.5 text-sm font-semibold text-white transition hover:bg-brand-700 focus:outline-none focus-visible:ring-2 focus-visible:ring-brand-600 focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-60"
                        x-text="loading ? 'Menyimpan...' : '{{ $user->exists ? 'Simpan Perubahan' : 'Buat Akun' }}'">
                    {{ $user->exists ? 'Simpan Perubahan' : 'Buat Akun' }}
                </button>
            </div>
        </form>
    </div>
@endsection