@extends('layouts.app')

@section('title', 'Kelola Pengguna')

@section('content')
    @php
        $roleStyles = [
            'owner' => 'bg-brand-100 text-brand-700',
            'cashier' => 'bg-sky-50 text-sky-700',
            'customer' => 'bg-slate-100 text-slate-600',
        ];
        $field = 'block w-full rounded-xl border-0 bg-surface-card py-2.5 text-sm text-ink-primary ring-1 ring-inset ring-surface-border placeholder:text-ink-muted focus:outline-none focus:ring-2 focus:ring-inset focus:ring-brand-600';
    @endphp

    <div class="space-y-6">
        <div class="flex flex-wrap items-start justify-between gap-3">
            <div>
                <h1 class="text-2xl font-semibold text-ink-primary">Kelola Pengguna</h1>
                <p class="mt-1 text-sm text-ink-muted">Tambah akun kasir atau pelanggan, ubah data, dan aktifkan atau nonaktifkan akun.</p>
            </div>
            <a href="{{ route('owner.users.create') }}"
                class="inline-flex items-center gap-2 rounded-xl bg-brand-600 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-brand-700 focus:outline-none focus-visible:ring-2 focus-visible:ring-brand-600 focus-visible:ring-offset-2">
                <x-lucide-plus class="size-4" />
                Tambah Pengguna
            </a>
        </div>

        @error('user')
            <div role="alert" class="rounded-xl bg-status-danger/10 px-4 py-3 text-sm text-status-danger">{{ $message }}</div>
        @enderror

        <form method="GET" action="{{ route('owner.users.index') }}" class="flex flex-col gap-3 sm:flex-row">
            <div class="relative flex-1">
                <x-lucide-search class="pointer-events-none absolute left-3.5 top-1/2 size-4 -translate-y-1/2 text-ink-muted" />
                <input type="search" name="search" value="{{ $search }}" maxlength="100"
                    placeholder="Cari nama atau email" class="{{ $field }} pl-10 pr-3.5">
            </div>

            <div
                x-data="{
                    open: false,
                    selected: @js($role ?? ''),
                    labels: @js(['' => 'Semua peran'] + $roleLabels)
                }"
                @click.outside="open = false"
                @keydown.escape.window="open = false"
                class="relative sm:w-48"
            >
                <input type="hidden" name="role" :value="selected">

                <button
                    type="button"
                    @click="open = !open"
                    :aria-expanded="open.toString()"
                    aria-haspopup="listbox"
                    class="{{ $field }} flex items-center justify-between px-3.5 text-left"
                >
                    <span x-text="labels[selected] ?? 'Semua peran'"></span>
                    <span class="inline-flex text-ink-muted transition-transform duration-200" :class="open ? 'rotate-180 text-brand-600' : ''">
                        <x-lucide-chevron-down class="size-4" />
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
                    <button
                        type="button"
                        role="option"
                        @click="selected = ''; open = false"
                        class="flex w-full items-center justify-between rounded-lg px-3 py-2 text-left text-sm transition"
                        :class="selected === '' ? 'bg-brand-50 font-semibold text-brand-700' : 'text-ink-primary hover:bg-surface-light'"
                    >
                        <span>Semua peran</span>
                        <span x-show="selected === ''" style="display: none" class="inline-flex text-brand-600">
                            <x-lucide-check class="size-4" />
                        </span>
                    </button>
                    @foreach ($roleLabels as $value => $label)
                        <button
                            type="button"
                            role="option"
                            @click="selected = @js($value); open = false"
                            class="flex w-full items-center justify-between rounded-lg px-3 py-2 text-left text-sm transition"
                            :class="selected === @js($value) ? 'bg-brand-50 font-semibold text-brand-700' : 'text-ink-primary hover:bg-surface-light'"
                        >
                            <span>{{ $label }}</span>
                            <span x-show="selected === @js($value)" style="display: none" class="inline-flex text-brand-600">
                                <x-lucide-check class="size-4" />
                            </span>
                        </button>
                    @endforeach
                </div>
            </div>

            <button type="submit"
                    class="rounded-xl bg-ink-primary px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-slate-800 focus:outline-none focus-visible:ring-2 focus-visible:ring-ink-primary focus-visible:ring-offset-2">
                Terapkan
            </button>
            @if ($search !== '' || $role)
                <a href="{{ route('owner.users.index') }}"
                    class="rounded-xl px-4 py-2.5 text-center text-sm font-semibold text-ink-muted ring-1 ring-inset ring-surface-border transition hover:bg-surface-light">
                    Reset
                </a>
            @endif
        </form>

        <div class="overflow-hidden rounded-2xl bg-surface-card ring-1 ring-surface-border">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-surface-border text-sm">
                    <thead class="bg-surface-light text-left text-xs font-semibold uppercase tracking-wide text-ink-muted">
                        <tr>
                            <th class="px-4 py-3">Nama</th>
                            <th class="px-4 py-3">Email</th>
                            <th class="px-4 py-3">Peran</th>
                            <th class="px-4 py-3">Status</th>
                            <th class="px-4 py-3 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-surface-border">
                        @forelse ($users as $user)
                            <tr>
                                <td class="px-4 py-3 font-medium text-ink-primary">
                                    {{ $user->name }}
                                    @if ($user->id === auth()->id())
                                        <span class="ml-1 text-xs font-normal text-ink-muted">(Anda)</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-ink-muted">{{ $user->email }}</td>
                                <td class="px-4 py-3">
                                    <span class="inline-flex rounded-full px-2.5 py-1 text-xs font-semibold {{ $roleStyles[$user->role] ?? 'bg-slate-100 text-slate-600' }}">
                                        {{ $roleLabels[$user->role] ?? $user->role }}
                                    </span>
                                </td>
                                <td class="px-4 py-3">
                                    @if ($user->is_active)
                                        <span class="inline-flex rounded-full bg-status-success/10 px-2.5 py-1 text-xs font-semibold text-emerald-700">Aktif</span>
                                    @else
                                        <span class="inline-flex rounded-full bg-status-danger/10 px-2.5 py-1 text-xs font-semibold text-red-700">Nonaktif</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3">
                                    <div class="flex items-center justify-end gap-2">
                                        <a href="{{ route('owner.users.edit', $user) }}"
                                            class="inline-flex items-center gap-1.5 rounded-lg px-3 py-1.5 text-xs font-semibold text-ink-primary ring-1 ring-inset ring-surface-border transition hover:bg-surface-light">
                                            <x-lucide-pencil class="size-3.5" />
                                            Ubah
                                        </a>
                                        @if ($user->id !== auth()->id())
                                            <form method="POST" action="{{ route('owner.users.toggle', $user) }}"
                                                    onsubmit="return confirm({{ Js::from(($user->is_active ? 'Nonaktifkan' : 'Aktifkan').' akun '.$user->name.'?') }})">
                                                @csrf
                                                @method('PATCH')
                                                <button type="submit"
                                                        class="rounded-lg px-3 py-1.5 text-xs font-semibold ring-1 ring-inset transition {{ $user->is_active ? 'text-red-700 ring-red-200 hover:bg-red-50' : 'text-emerald-700 ring-emerald-200 hover:bg-emerald-50' }}">
                                                    {{ $user->is_active ? 'Nonaktifkan' : 'Aktifkan' }}
                                                </button>
                                            </form>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-4 py-10 text-center text-ink-muted">
                                    Tidak ada pengguna yang cocok dengan pencarian Anda.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{ $users->links() }}
    </div>
@endsection