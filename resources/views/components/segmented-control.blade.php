{{--
    Segmented control — pilihan opsi mutual-exclusive yang ringkas (mirip iOS
    segmented control). Pakai untuk time-range picker, view-mode toggle, kategori
    filter pendek (3-7 opsi).

    Untuk opsi >7 atau punya icon kompleks → pakai <x-nawasara-ui::tab-switcher>
    (underline) atau dropdown filter.

    Pemakaian (numeric range):
        <x-nawasara-ui::segmented-control
            :options="['3' => '3d', '7' => '7d', '14' => '14d', '30' => '30d']"
            :active="$trendDays"
            wire-method="setTrendDays" />

    Pemakaian (label values):
        <x-nawasara-ui::segmented-control
            :options="['today' => 'Hari ini', '24h' => '24 jam']"
            :active="$range"
            wire-method="setQuickRange"
            size="sm" />

    Untuk pilih warna highlight:
        color="primary" (default) | "success" | "warning" | "danger"

    Untuk full width:
        full
--}}
@props([
    'options' => [],         // ['key' => 'label', ...] atau [['key', 'label', 'icon']]
    'active' => null,
    'wireMethod' => null,    // Livewire method untuk handle click (e.g. 'setTrendDays')
    'color' => 'primary',
    'size' => 'md',          // sm | md
    'full' => false,         // full width (split equally)
])

@php
    $activeColors = [
        'primary' => 'bg-blue-600 text-white shadow-sm',
        'success' => 'bg-green-600 text-white shadow-sm',
        'warning' => 'bg-amber-600 text-white shadow-sm',
        'danger' => 'bg-rose-600 text-white shadow-sm',
    ];
    $activeClass = $activeColors[$color] ?? $activeColors['primary'];

    $sizing = match ($size) {
        'sm' => 'px-2.5 py-1 text-xs',
        default => 'px-3 py-1.5 text-sm',
    };

    // Normalize options: support assoc array (key => label) atau list of [key, label, icon]
    $normalized = [];
    foreach ($options as $key => $val) {
        if (is_array($val)) {
            // ['key' => 'log', 'label' => 'Log', 'icon' => '...']
            $normalized[] = [
                'key' => (string) ($val['key'] ?? $key),
                'label' => $val['label'] ?? $key,
                'icon' => $val['icon'] ?? null,
            ];
        } else {
            // 'key' => 'label'
            $normalized[] = ['key' => (string) $key, 'label' => $val, 'icon' => null];
        }
    }

    $wrapperClass = 'inline-flex items-center gap-0.5 p-0.5 rounded-lg bg-gray-100 dark:bg-neutral-800 border border-gray-200 dark:border-neutral-700'
        . ($full ? ' w-full' : '');
@endphp

<div {{ $attributes->merge(['class' => $wrapperClass]) }} role="tablist">
    @foreach ($normalized as $opt)
        @php
            $isActive = (string) $active === $opt['key'];
            $btnClass = trim(implode(' ', [
                'inline-flex items-center justify-center gap-1.5 rounded-md font-medium transition-colors',
                $sizing,
                $full ? 'flex-1' : '',
                $isActive
                    ? $activeClass
                    : 'text-gray-700 hover:bg-white hover:text-gray-900 dark:text-neutral-300 dark:hover:bg-neutral-700 dark:hover:text-neutral-100',
                'focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-blue-500',
            ]));
        @endphp
        <button type="button"
            @if ($wireMethod) wire:click="{{ $wireMethod }}('{{ $opt['key'] }}')" @endif
            class="{{ $btnClass }}"
            role="tab"
            @if ($isActive) aria-selected="true" @endif>
            @if (! empty($opt['icon']))
                <x-dynamic-component :component="$opt['icon']" class="size-3.5" />
            @endif
            {{ $opt['label'] }}
        </button>
    @endforeach
</div>
