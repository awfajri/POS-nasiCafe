@props([
    'title' => '',
    'value' => '0',
    'subtitle' => null,
    'description' => null,
    'badge' => null,
    'trend' => null,
    'icon' => null,
    'color' => 'brand',
])

@php
    $bottomText = $subtitle ?? $description;
    $badgeText = $badge ?? $trend;

    $theme = match ($color) {
        'success', 'emerald', 'green' => [
            'headerBg' => 'bg-[#e8f5e9]',
            'headerText' => 'text-[#1b5e20]',
            'iconColor' => 'text-[#2e7d32]',
            'badgeBg' => 'bg-[#dcfce7] text-[#15803d]',
        ],
        'info', 'sky', 'blue' => [
            'headerBg' => 'bg-[#e0f2fe]',
            'headerText' => 'text-[#075985]',
            'iconColor' => 'text-[#0284c7]',
            'badgeBg' => 'bg-[#e0f2fe] text-[#0369a1]',
        ],
        'warning', 'amber', 'orange' => [
            'headerBg' => 'bg-[#fef3c7]/90',
            'headerText' => 'text-[#92400e]',
            'iconColor' => 'text-[#b45309]',
            'badgeBg' => 'bg-[#fef3c7] text-[#92400e]',
        ],
        'danger', 'rose', 'red' => [
            'headerBg' => 'bg-[#ffe4e6]',
            'headerText' => 'text-[#9f1239]',
            'iconColor' => 'text-[#e11d48]',
            'badgeBg' => 'bg-[#ffe4e6] text-[#9f1239]',
        ],
        default => [
            'headerBg' => 'bg-[#e8ebff]',
            'headerText' => 'text-[#2e3477]',
            'iconColor' => 'text-[#3b4399]',
            'badgeBg' => 'bg-[#e0e7ff] text-[#3730a3]',
        ],
    };
@endphp

<div {{ $attributes->merge(['class' => 'bg-white rounded-3xl border border-slate-200/90 p-5 shadow-xs flex flex-col justify-between space-y-4 hover:shadow-sm transition']) }}>
    <!-- Top Pill Banner (Matching Design Image) -->
    <div class="rounded-xl px-3.5 py-2.5 flex items-center gap-2.5 {{ $theme['headerBg'] }}">
        <div class="shrink-0 {{ $theme['iconColor'] }}">
            @if ($icon === 'money' || $icon === 'cash' || $icon === 'revenue' || $icon === 'income')
                {{-- Coin/Money Icon with Trend --}}
                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    <path stroke-linecap="round" stroke-linejoin="round" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9"/>
                </svg>
            @elseif ($icon === 'shopping-bag' || $icon === 'orders' || $icon === 'transactions' || $icon === 'pos')
                {{-- Shopping Bag Icon --}}
                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/>
                </svg>
            @elseif ($icon === 'chart' || $icon === 'average' || $icon === 'trend')
                {{-- Line Trend / Chart Icon --}}
                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 13l4-4 4 4 7-7m2 0h-5m5 0v5M3 21h18"/>
                </svg>
            @elseif ($icon === 'warning' || $icon === 'alert' || $icon === 'stock')
                {{-- Warning Triangle Icon --}}
                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                </svg>
            @elseif ($icon === 'cart' || $icon === 'shopping-cart')
                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/>
                </svg>
            @elseif ($icon === 'users' || $icon === 'user')
                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                </svg>
            @elseif ($icon)
                {!! $icon !!}
            @else
                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <circle cx="12" cy="12" r="9"/>
                </svg>
            @endif
        </div>
        <span class="font-semibold text-sm {{ $theme['headerText'] }}">{{ $title }}</span>
    </div>

    <!-- Main Value -->
    <div>
        <p class="text-3xl sm:text-4xl font-extrabold tracking-tight text-slate-900">{{ $value }}</p>
    </div>

    <!-- Bottom Footer (Subtitle & Badge) -->
    @if ($bottomText || $badgeText || !empty(trim($slot)))
        <div class="flex items-center justify-between gap-3 text-xs pt-1">
            <div class="text-slate-500 font-medium truncate">
                {{ $bottomText }}
                @if (!empty(trim($slot)))
                    {{ $slot }}
                @endif
            </div>

            @if ($badgeText)
                <span class="inline-flex items-center gap-1 font-bold px-3 py-1.5 rounded-lg shrink-0 text-xs {{ $theme['badgeBg'] }}">
                    {{ $badgeText }}
                </span>
            @endif
        </div>
    @endif
</div>
