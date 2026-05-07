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

    Pemakaian (compact — untuk 6+ cards horizontal):
        <x-nawasara-ui::stat-card compact
            label="Queued" :value="12" color="primary"
            :active="$status === 'queued'"
            wire:click="setStatusFilter('queued')" />

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
    /**
     * Compact mode: drops the big icon box, swaps it for a colored dot,
     * tightens padding, and stacks label+value in a tall-thin layout.
     * Designed for dashboards with 5-6 horizontal cards where the
     * default size makes the row too dominant. Trend / description
     * still render but at smaller sizes.
     */
    'compact' => false,
])

@php
    // Token mapping konsisten dengan button component.
    // 'dot' adalah accent kecil untuk compact mode (ganti icon-box besar).
    $colorTokens = [
        'primary' => [
            'icon' => 'bg-emerald-50 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-400',
            'ring' => 'border-emerald-600 ring-2 ring-emerald-100 dark:ring-emerald-900/30',
            'accent' => 'border-l-4 border-l-emerald-600 dark:border-l-emerald-400',
            'dot' => 'bg-emerald-500',
            'activeBg' => 'bg-emerald-50 dark:bg-emerald-900/20',
        ],
        'success' => [
            'icon' => 'bg-green-50 text-green-600 dark:bg-green-900/30 dark:text-green-400',
            'ring' => 'border-green-500 ring-2 ring-green-100 dark:ring-green-900/30',
            'accent' => 'border-l-4 border-l-green-500 dark:border-l-green-400',
            'dot' => 'bg-green-500',
            'activeBg' => 'bg-green-50 dark:bg-green-900/20',
        ],
        'warning' => [
            'icon' => 'bg-amber-50 text-amber-600 dark:bg-amber-900/30 dark:text-amber-400',
            'ring' => 'border-amber-500 ring-2 ring-amber-100 dark:ring-amber-900/30',
            'accent' => 'border-l-4 border-l-amber-500 dark:border-l-amber-400',
            'dot' => 'bg-amber-500',
            'activeBg' => 'bg-amber-50 dark:bg-amber-900/20',
        ],
        'danger' => [
            'icon' => 'bg-rose-50 text-rose-600 dark:bg-rose-900/30 dark:text-rose-400',
            'ring' => 'border-rose-500 ring-2 ring-rose-100 dark:ring-rose-900/30',
            'accent' => 'border-l-4 border-l-rose-500 dark:border-l-rose-400',
            'dot' => 'bg-rose-500',
            'activeBg' => 'bg-rose-50 dark:bg-rose-900/20',
        ],
        'info' => [
            'icon' => 'bg-cyan-50 text-cyan-600 dark:bg-cyan-900/30 dark:text-cyan-400',
            'ring' => 'border-cyan-500 ring-2 ring-cyan-100 dark:ring-cyan-900/30',
            'accent' => 'border-l-4 border-l-cyan-500 dark:border-l-cyan-400',
            'dot' => 'bg-cyan-500',
            'activeBg' => 'bg-cyan-50 dark:bg-cyan-900/20',
        ],
        'neutral' => [
            'icon' => 'bg-gray-100 text-gray-600 dark:bg-neutral-700 dark:text-neutral-400',
            'ring' => 'border-gray-500 ring-2 ring-gray-100 dark:ring-gray-900/30',
            'accent' => 'border-l-4 border-l-gray-500 dark:border-l-neutral-400',
            'dot' => 'bg-gray-400 dark:bg-neutral-500',
            'activeBg' => 'bg-gray-50 dark:bg-neutral-700/50',
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
    // Compact mode skips the ring (too noisy for tightly-packed rows) and
    // uses a tinted background instead - more subtle, easier to scan.
    if ($compact) {
        $borderClass = $active
            ? 'border-gray-300 dark:border-neutral-600 '.$tokens['activeBg']
            : 'border-gray-200 dark:border-neutral-700';
    } else {
        $borderClass = $active
            ? $tokens['ring']
            : 'border-gray-200 dark:border-neutral-700';
    }

    // Accent left-border (opt-in via `accent` prop) — kasih visual hierarchy
    // yang lebih kuat tanpa mengorbankan keseragaman card. Cocok untuk hero
    // stats dashboard dimana 4 card berdampingan.
    // Compact mode skips this: the colored dot already does the job at much
    // less visual cost.
    $accentClass = ($accent && ! $compact) ? $tokens['accent'] : '';

    // Hover effect cuma untuk interactive card — mencegah static card terlihat clickable.
    // Compact mode uses a lighter hover (smaller card = smaller motion).
    $hoverClass = '';
    if ($isInteractive) {
        $hoverClass = $compact
            ? 'hover:bg-gray-50 dark:hover:bg-neutral-700/50 transition-colors duration-150'
            : 'hover:shadow-md hover:-translate-y-0.5 transition-all duration-200';
    }

    // Padding rounds tighter in compact: p-3 vs p-5; rounded-lg vs rounded-xl.
    $paddingClass = $compact ? 'p-3' : 'p-5';
    $roundedClass = $compact ? 'rounded-lg' : 'rounded-xl';

    $baseClass = trim(implode(' ', [
        'block w-full text-left',
        'bg-white dark:bg-neutral-800',
        'border', $roundedClass, $paddingClass,
        $borderClass,
        $accentClass,
        $hoverClass,
        $isInteractive ? 'cursor-pointer focus:outline-none focus-visible:ring-2 focus-visible:ring-offset-2 focus-visible:ring-emerald-600 dark:focus-visible:ring-offset-neutral-900' : '',
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

    @if ($compact)
        {{-- Compact layout: label row (dot + text) on top, value row below.
             Designed to be ~60-64px tall so 6 cards fit in a single
             dashboard row without dominating the page. --}}
        <div class="flex items-center gap-1.5 mb-1.5">
            <span class="size-2 rounded-full shrink-0 {{ $tokens['dot'] }}" aria-hidden="true"></span>
            <span class="text-xs font-medium text-gray-600 dark:text-neutral-400 truncate">
                {{ $label }}
            </span>
        </div>
        <div class="flex items-baseline gap-1.5">
            <span class="text-xl font-bold text-gray-800 dark:text-neutral-100 tracking-tight tabular-nums">
                {{ $value }}
            </span>
            @if ($trend && $trendIcon)
                <span class="inline-flex items-center gap-0.5 text-[11px] font-medium {{ $trendClasses }}">
                    <x-dynamic-component :component="$trendIcon" class="size-3" />
                    {{ $trend['value'] ?? '' }}
                </span>
            @endif
        </div>
        @if ($description)
            <p class="mt-0.5 text-[11px] text-gray-500 dark:text-neutral-500 truncate">
                {{ $description }}
            </p>
        @endif
    @else
        {{-- Default layout: prominent icon-box + stacked label/value.
             Used by hero KPI dashboards where each card carries weight. --}}
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
    @endif
</{{ $tag }}>
