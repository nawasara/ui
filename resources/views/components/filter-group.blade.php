@props([
    'label' => '',
    'model' => null,
    'items' => [],
    'icon' => null,
])

@php
    // Normalise items: array values become value=>label maps
    $normalised = [];
    foreach ($items as $k => $v) {
        $normalised[(string) $k] = $v;
    }
@endphp

{{-- Filter group row in the left column of <x-filter-panel>. Registers itself
     with the parent Alpine instance via x-init, so the parent knows about this
     dimension and can render its options in the right-hand value picker. --}}
<button type="button"
    x-init="registerDimension({ model: @js($model), label: @js($label), items: @js((object) $normalised) })"
    x-on:click="selectDimension({ model: @js($model), label: @js($label), items: @js((object) $normalised) })"
    x-on:mouseenter="selectDimension({ model: @js($model), label: @js($label), items: @js((object) $normalised) })"
    x-bind:class="activeDim === @js($model)
        ? 'bg-emerald-50 text-emerald-800 dark:bg-emerald-900/30 dark:text-emerald-400'
        : 'text-gray-700 dark:text-neutral-300 hover:bg-gray-50 dark:hover:bg-neutral-700/50'"
    class="w-full flex items-center justify-between gap-x-2 px-3 py-2 text-sm transition-colors">
    <span class="flex items-center gap-x-2 truncate">
        @if ($icon)
            <x-dynamic-component :component="$icon" class="size-4 shrink-0 text-gray-400 dark:text-neutral-500" />
        @endif
        <span class="truncate">{{ $label }}</span>
    </span>
    <span class="flex items-center gap-x-1.5 shrink-0">
        {{-- Per-dimension selected count --}}
        <span x-show="(state[@js($model)] || []).length > 0" x-cloak
            x-text="(state[@js($model)] || []).length"
            class="inline-flex items-center justify-center min-w-[1.25rem] h-4 px-1 rounded-full text-[10px] font-semibold bg-emerald-600 text-white"></span>
        <x-lucide-chevron-right class="size-3.5 text-gray-400 dark:text-neutral-500" />
    </span>
</button>
