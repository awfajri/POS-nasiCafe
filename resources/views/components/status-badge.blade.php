@props([
    'status' => 'pending',
    'label' => null,
])

@php
    $normalizedStatus = strtolower(trim((string) $status));

    $config = match ($normalizedStatus) {
        'completed', 'selesai' => [
            'label' => $label ?? 'Selesai',
            'classes' => 'bg-emerald-50 text-emerald-700 border-emerald-200 ring-emerald-500/20',
            'dot' => 'bg-emerald-500',
        ],
        'processing', 'diproses' => [
            'label' => $label ?? 'Diproses',
            'classes' => 'bg-blue-50 text-blue-700 border-blue-200 ring-blue-500/20',
            'dot' => 'bg-blue-500',
        ],
        'cancelled', 'batal', 'dibatalkan' => [
            'label' => $label ?? 'Dibatalkan',
            'classes' => 'bg-rose-50 text-rose-700 border-rose-200 ring-rose-500/20',
            'dot' => 'bg-rose-500',
        ],
        default => [
            'label' => $label ?? 'Menunggu',
            'classes' => 'bg-amber-50 text-amber-700 border-amber-200 ring-amber-500/20',
            'dot' => 'bg-amber-500',
        ],
    };
@endphp

<span {{ $attributes->merge(['class' => 'inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-semibold border ring-1 ' . $config['classes']]) }}>
    <span class="w-1.5 h-1.5 rounded-full {{ $config['dot'] }}" aria-hidden="true"></span>
    <span>{{ $config['label'] }}</span>
</span>
