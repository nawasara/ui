{{--
    Tab switcher (underline style) — buat sebagai child di area konten yang punya
    multiple panel (Log / Headers / Body, dst).

    Pemakaian (declarative items + wire:click handler):
        <x-nawasara-ui::tab-switcher
            :items="[
                ['key' => 'log', 'label' => 'Delivery Log', 'icon' => 'lucide-activity'],
                ['key' => 'headers', 'label' => 'Headers', 'icon' => 'lucide-mail'],
                ['key' => 'body', 'label' => 'Body Preview', 'icon' => 'lucide-file-text'],
            ]"
            :active="$detailTab"
            wire-method="setDetailTab" />

    Atau pakai Alpine state (no wire roundtrip):
        <x-nawasara-ui::tab-switcher
            :items="[...]"
            x-model="tab" />
--}}
@props([
    'items' => [],          // [['key' => 'log', 'label' => 'Delivery Log', 'icon' => 'lucide-...'], ...]
    'active' => null,       // currently selected key (server-side)
    'wireMethod' => null,   // Livewire method untuk handle click (e.g. 'setDetailTab')
    'color' => 'primary',   // primary | success | warning | danger
])

@php
    $activeColors = [
        'primary' => 'border-emerald-700 text-emerald-700 dark:text-emerald-400',
        'success' => 'border-green-600 text-green-600 dark:text-green-400',
        'warning' => 'border-amber-600 text-amber-600 dark:text-amber-400',
        'danger' => 'border-rose-600 text-rose-600 dark:text-rose-400',
    ];
    $activeClass = $activeColors[$color] ?? $activeColors['primary'];
@endphp

<div class="border-b border-gray-200 dark:border-neutral-700">
    <nav class="flex -mb-px gap-4 text-sm overflow-x-auto" aria-label="Tabs">
        @foreach ($items as $item)
            @php
                $isActive = $active === ($item['key'] ?? null);
                $tabClass = 'py-2 px-1 border-b-2 font-medium inline-flex items-center gap-1.5 whitespace-nowrap transition-colors '
                    . ($isActive
                        ? $activeClass
                        : 'border-transparent text-gray-500 hover:text-gray-700 dark:text-neutral-400 dark:hover:text-neutral-300');
            @endphp
            <button type="button"
                @if ($wireMethod) wire:click="{{ $wireMethod }}('{{ $item['key'] }}')" @endif
                class="{{ $tabClass }}"
                @if ($isActive) aria-current="page" @endif>
                @if (! empty($item['icon']))
                    <x-dynamic-component :component="$item['icon']" class="size-4" />
                @endif
                {{ $item['label'] ?? $item['key'] }}
                @if (isset($item['badge']))
                    <span class="ml-1 inline-flex items-center px-1.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-700 dark:bg-neutral-700 dark:text-neutral-300">
                        {{ $item['badge'] }}
                    </span>
                @endif
            </button>
        @endforeach
    </nav>
</div>
