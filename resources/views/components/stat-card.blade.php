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
    <!-- Top Pill Banner (Design Presisi) -->
    <div class="rounded-xl px-3.5 py-2.5 flex items-center gap-2.5 {{ $theme['headerBg'] }}">
        <div class="shrink-0 {{ $theme['iconColor'] }}">
            @if ($icon === 'money' || $icon === 'cash' || $icon === 'revenue' || $icon === 'income')
                <x-lucide-circle-dollar-sign class="w-5 h-5" />
            @elseif ($icon === 'shopping-bag' || $icon === 'orders' || $icon === 'transactions' || $icon === 'pos')
                <x-lucide-shopping-bag class="w-5 h-5" />
            @elseif ($icon === 'chart' || $icon === 'average' || $icon === 'trend' || $icon === 'trending-up')
                <x-lucide-trending-up class="w-5 h-5" />
            @elseif ($icon === 'warning' || $icon === 'alert' || $icon === 'stock' || $icon === 'alert-triangle')
                <x-lucide-alert-triangle class="w-5 h-5" />
            @elseif ($icon === 'cart' || $icon === 'shopping-cart')
                <x-lucide-shopping-cart class="w-5 h-5" />
            @elseif ($icon === 'users' || $icon === 'user')
                <x-lucide-users class="w-5 h-5" />
            @elseif ($icon === 'receipt' || $icon === 'file' || $icon === 'file-text')
                <x-lucide-file-text class="w-5 h-5" />
            @elseif ($icon === 'credit-card' || $icon === 'card')
                <x-lucide-credit-card class="w-5 h-5" />
            @else
                <x-lucide-layout-grid class="w-5 h-5" />
            @endif
        </div>
        <span class="font-semibold text-sm {{ $theme['headerText'] }}">{{ $title }}</span>
    </div>

    <!-- Main Value (Proporsional & Rapi untuk Nominal Rupiah) -->
    <div>
        <p class="text-2xl sm:text-3xl font-semibold tracking-tight text-slate-900">{{ $value }}</p>
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
