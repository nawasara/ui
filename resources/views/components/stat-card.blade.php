{{--
    Stat card — angka + label + icon, optional clickable filter behavior.

    Pemakaian (static):
        <x-nawasara-ui::stat-card
            label="Total Accounts"
            :value="$summary['total']"
            color="primary"
            icon="lucide-users" />

    Pemakaian (clickable filter card with active state):
        <x-nawasara-ui::stat-card
            label="Healthy"
            :value="$summary['ok']"
            color="success"
            icon="lucide-check-circle"
            :active="$stateFilter === 'ok'"
            wire:click="setStateFilter('ok')" />

    Color tokens (selaras dengan button):
        primary | success | warning | danger | neutral | info
--}}
@props([
    'label' => '',
    'value' => null,
    'icon' => null,
    'color' => 'primary',
    'active' => false,
    'href' => null,
    'trend' => null,         // ['direction' => 'up'|'down'|'flat', 'value' => '12%']
    'trendLabel' => null,    // 'vs minggu lalu'
    'description' => null,   // sub-label di bawah value (opsional)
    'accent' => false,       // true → tampilkan accent border-left berwarna (premium-look)
])

@php
    // Token mapping konsisten dengan button component.
    $colorTokens = [
        'primary' => [
            'icon' => 'bg-blue-50 text-blue-600 dark:bg-blue-900/30 dark:text-blue-400',
            'ring' => 'border-blue-500 ring-2 ring-blue-100 dark:ring-blue-900/30',
            'accent' => 'border-l-4 border-l-blue-500 dark:border-l-blue-400',
        ],
        'success' => [
            'icon' => 'bg-green-50 text-green-600 dark:bg-green-900/30 dark:text-green-400',
            'ring' => 'border-green-500 ring-2 ring-green-100 dark:ring-green-900/30',
            'accent' => 'border-l-4 border-l-green-500 dark:border-l-green-400',
        ],
        'warning' => [
            'icon' => 'bg-amber-50 text-amber-600 dark:bg-amber-900/30 dark:text-amber-400',
            'ring' => 'border-amber-500 ring-2 ring-amber-100 dark:ring-amber-900/30',
            'accent' => 'border-l-4 border-l-amber-500 dark:border-l-amber-400',
        ],
        'danger' => [
            'icon' => 'bg-rose-50 text-rose-600 dark:bg-rose-900/30 dark:text-rose-400',
            'ring' => 'border-rose-500 ring-2 ring-rose-100 dark:ring-rose-900/30',
            'accent' => 'border-l-4 border-l-rose-500 dark:border-l-rose-400',
        ],
        'info' => [
            'icon' => 'bg-cyan-50 text-cyan-600 dark:bg-cyan-900/30 dark:text-cyan-400',
            'ring' => 'border-cyan-500 ring-2 ring-cyan-100 dark:ring-cyan-900/30',
            'accent' => 'border-l-4 border-l-cyan-500 dark:border-l-cyan-400',
        ],
        'neutral' => [
            'icon' => 'bg-gray-100 text-gray-600 dark:bg-neutral-700 dark:text-neutral-400',
            'ring' => 'border-gray-500 ring-2 ring-gray-100 dark:ring-gray-900/30',
            'accent' => 'border-l-4 border-l-gray-500 dark:border-l-neutral-400',
        ],
    ];
    $tokens = $colorTokens[$color] ?? $colorTokens['primary'];

    // Card adalah `<button>` kalau ada wire:click / @click, `<a>` kalau href, `<div>` kalau plain.
    // Detect interactivity dari attribute bag — bukan props eksplisit, supaya developer
    // tidak perlu hint manual.
    $isInteractive = $href !== null
        || $attributes->wire('click')->value() !== null
        || $attributes->whereStartsWith('@click')->isNotEmpty()
        || $attributes->whereStartsWith('x-on:click')->isNotEmpty();

    $tag = $href ? 'a' : ($isInteractive ? 'button' : 'div');

    // Border state: active = colored ring, default = subtle gray.
    $borderClass = $active
        ? $tokens['ring']
        : 'border-gray-200 dark:border-neutral-700';

    // Accent left-border (opt-in via `accent` prop) — kasih visual hierarchy
    // yang lebih kuat tanpa mengorbankan keseragaman card. Cocok untuk hero
    // stats dashboard dimana 4 card berdampingan.
    $accentClass = $accent ? $tokens['accent'] : '';

    // Hover effect cuma untuk interactive card — mencegah static card terlihat clickable.
    $hoverClass = $isInteractive ? 'hover:shadow-md hover:-translate-y-0.5 transition-all duration-200' : '';

    $baseClass = trim(implode(' ', [
        'block w-full text-left',
        'bg-white dark:bg-neutral-800',
        'border rounded-xl p-5',
        $borderClass,
        $accentClass,
        $hoverClass,
        $isInteractive ? 'cursor-pointer focus:outline-none focus-visible:ring-2 focus-visible:ring-offset-2 focus-visible:ring-blue-500 dark:focus-visible:ring-offset-neutral-900' : '',
    ]));

    // Trend arrow + color
    $trendClasses = match ($trend['direction'] ?? null) {
        'up' => 'text-green-600 dark:text-green-400',
        'down' => 'text-rose-600 dark:text-rose-400',
        'flat' => 'text-gray-500 dark:text-neutral-400',
        default => null,
    };
    $trendIcon = match ($trend['direction'] ?? null) {
        'up' => 'lucide-trending-up',
        'down' => 'lucide-trending-down',
        'flat' => 'lucide-minus',
        default => null,
    };
@endphp

<{{ $tag }}
    @if ($href) href="{{ $href }}" @endif
    @if ($tag === 'button') type="button" @endif
    {{ $attributes->merge(['class' => $baseClass]) }}>
    <div class="flex items-start gap-3">
        @if ($icon)
            <div class="p-2.5 rounded-lg shrink-0 {{ $tokens['icon'] }}">
                <x-dynamic-component :component="$icon" class="size-5" />
            </div>
        @endif

        <div class="flex-1 min-w-0">
            <p class="text-xs font-medium text-gray-500 dark:text-neutral-400 truncate">
                {{ $label }}
            </p>

            <div class="flex items-baseline gap-2 mt-1">
                <p class="text-2xl font-bold text-gray-800 dark:text-neutral-100 tracking-tight">
                    {{ $value }}
                </p>

                @if ($trend && $trendIcon)
                    <span class="inline-flex items-center gap-0.5 text-xs font-medium {{ $trendClasses }}">
                        <x-dynamic-component :component="$trendIcon" class="size-3" />
                        {{ $trend['value'] ?? '' }}
                    </span>
                @endif
            </div>

            @if ($description)
                <p class="text-xs text-gray-500 dark:text-neutral-400 mt-0.5 truncate">
                    {{ $description }}
                </p>
            @elseif ($trendLabel)
                <p class="text-xs text-gray-400 dark:text-neutral-500 mt-0.5 truncate">
                    {{ $trendLabel }}
                </p>
            @endif
        </div>
    </div>
</{{ $tag }}>
