{{--
    Badge — pill kecil untuk status, kategori, atau metadata inline (di tabel,
    next to heading, dll.). Bukan untuk action — kalau clickable, pakai button
    variant="flat" size="sm" sebagai gantinya.

    Pemakaian:
        <x-nawasara-ui::badge color="success">Active</x-nawasara-ui::badge>
        <x-nawasara-ui::badge color="warning" icon="lucide-alert-triangle">Warning</x-nawasara-ui::badge>
        <x-nawasara-ui::badge color="danger" variant="solid">Suspended</x-nawasara-ui::badge>

    Variants:
        soft (default) — bg-X-50 + text-X-700, paling subtle, cocok untuk inline
        solid          — bg-X-600 + text-white, paling pop, cocok untuk emphasis
        outline        — border-X-300 + text-X-700, mid-emphasis

    Color tokens:
        primary | success | warning | danger | info | neutral
--}}
@props([
    'color' => 'neutral',
    'variant' => 'soft',     // soft | solid | outline
    'size' => 'md',          // sm | md
    'icon' => null,
    'dot' => false,          // show colored dot indicator (replaces icon)
])

@php
    // Hardcoded literal supaya Tailwind JIT detect.
    $colors = [
        'primary' => [
            'soft' => 'bg-blue-50 text-blue-700 dark:bg-blue-900/30 dark:text-blue-400',
            'solid' => 'bg-blue-600 text-white dark:bg-blue-500',
            'outline' => 'border border-blue-300 text-blue-700 dark:border-blue-700 dark:text-blue-300',
            'dot' => 'bg-blue-500',
        ],
        'success' => [
            'soft' => 'bg-green-50 text-green-700 dark:bg-green-900/30 dark:text-green-400',
            'solid' => 'bg-green-600 text-white dark:bg-green-500',
            'outline' => 'border border-green-300 text-green-700 dark:border-green-700 dark:text-green-300',
            'dot' => 'bg-green-500',
        ],
        'warning' => [
            'soft' => 'bg-amber-50 text-amber-700 dark:bg-amber-900/30 dark:text-amber-400',
            'solid' => 'bg-amber-600 text-white dark:bg-amber-500',
            'outline' => 'border border-amber-300 text-amber-700 dark:border-amber-700 dark:text-amber-300',
            'dot' => 'bg-amber-500',
        ],
        'danger' => [
            'soft' => 'bg-rose-50 text-rose-700 dark:bg-rose-900/30 dark:text-rose-400',
            'solid' => 'bg-rose-600 text-white dark:bg-rose-500',
            'outline' => 'border border-rose-300 text-rose-700 dark:border-rose-700 dark:text-rose-300',
            'dot' => 'bg-rose-500',
        ],
        'info' => [
            'soft' => 'bg-cyan-50 text-cyan-700 dark:bg-cyan-900/30 dark:text-cyan-400',
            'solid' => 'bg-cyan-600 text-white dark:bg-cyan-500',
            'outline' => 'border border-cyan-300 text-cyan-700 dark:border-cyan-700 dark:text-cyan-300',
            'dot' => 'bg-cyan-500',
        ],
        'neutral' => [
            'soft' => 'bg-gray-100 text-gray-700 dark:bg-neutral-700 dark:text-neutral-300',
            'solid' => 'bg-gray-600 text-white dark:bg-neutral-500',
            'outline' => 'border border-gray-300 text-gray-700 dark:border-neutral-600 dark:text-neutral-300',
            'dot' => 'bg-gray-400',
        ],
    ];

    $palette = $colors[$color] ?? $colors['neutral'];
    $variantClass = $palette[$variant] ?? $palette['soft'];

    $sizing = match ($size) {
        'sm' => 'px-1.5 py-0.5 text-[10px] gap-1',
        default => 'px-2 py-0.5 text-xs gap-1.5',
    };

    $base = trim(implode(' ', [
        'inline-flex items-center font-medium rounded-full',
        $sizing,
        $variantClass,
    ]));
@endphp

<span {{ $attributes->merge(['class' => $base]) }}>
    @if ($dot)
        <span class="size-1.5 rounded-full {{ $palette['dot'] }} shrink-0"></span>
    @elseif ($icon)
        <x-dynamic-component :component="$icon" class="size-3 shrink-0" />
    @endif
    {{ $slot }}
</span>
