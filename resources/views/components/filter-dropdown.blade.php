@props([
    'label' => 'Filter',
    'items' => [],
    'model' => null,
])

@php
    $currentValue = $this->{$model} ?? '';
    $isActive = !empty($currentValue);
@endphp

<div class="hs-dropdown relative inline-flex [--auto-close:inside]">
    <button id="hs-filter-{{ $model }}" type="button"
        class="hs-dropdown-toggle py-2.5 px-4 inline-flex items-center gap-x-2 text-sm font-medium rounded-lg border shadow-sm focus:outline-none disabled:opacity-50 disabled:pointer-events-none transition-colors
        {{ $isActive
            ? 'border-green-200 bg-green-50 text-green-700 hover:bg-green-100 dark:bg-green-900/20 dark:border-green-800/50 dark:text-green-400'
            : 'border-gray-200 bg-white text-gray-800 hover:bg-gray-50 dark:bg-neutral-800 dark:border-neutral-700 dark:text-white dark:hover:bg-neutral-700' }}"
        aria-haspopup="menu" aria-expanded="false">
        {{ $label }}
        <svg class="hs-dropdown-open:rotate-180 size-4 transition-transform" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <path d="m6 9 6 6 6-6"></path>
        </svg>
    </button>

    <div class="hs-dropdown-menu transition-[opacity,margin] duration hs-dropdown-open:opacity-100 opacity-0 min-w-48 bg-white shadow-md rounded-lg mt-2 dark:bg-neutral-800 dark:border dark:border-neutral-700 hidden z-20"
        role="menu" aria-orientation="vertical" aria-labelledby="hs-filter-{{ $model }}">
        <div class="p-1 space-y-0.5">
            @foreach ($items as $value => $text)
                @php
                    $isSelected = ($value === 'all' && empty($currentValue)) || $currentValue === (string) $value;
                @endphp
                <button type="button"
                    wire:click="$set('{{ $model }}', '{{ $value === 'all' ? '' : $value }}')"
                    class="w-full flex items-center gap-x-3 py-2 px-3 rounded-lg text-sm hover:bg-gray-100 focus:outline-none focus:bg-gray-100 dark:hover:bg-neutral-700 dark:hover:text-neutral-300 dark:focus:bg-neutral-700 transition-colors
                    {{ $isSelected ? 'bg-green-50 text-green-700 dark:bg-green-900/30 dark:text-green-400' : 'text-gray-800 dark:text-neutral-400' }}">
                    @if ($isSelected)
                        <svg class="shrink-0 size-4 text-green-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12c0 1.268-.63 2.39-1.593 3.068a3.745 3.745 0 0 1-1.043 3.296 3.745 3.745 0 0 1-3.296 1.043A3.745 3.745 0 0 1 12 21c-1.268 0-2.39-.63-3.068-1.593a3.746 3.746 0 0 1-3.296-1.043 3.745 3.745 0 0 1-1.043-3.296A3.745 3.745 0 0 1 3 12c0-1.268.63-2.39 1.593-3.068a3.745 3.745 0 0 1 1.043-3.296 3.746 3.746 0 0 1 3.296-1.043A3.746 3.746 0 0 1 12 3c1.268 0 2.39.63 3.068 1.593a3.746 3.746 0 0 1 3.296 1.043 3.746 3.746 0 0 1 1.043 3.296A3.745 3.745 0 0 1 21 12Z" />
                        </svg>
                    @else
                        <span class="shrink-0 size-4"></span>
                    @endif
                    {{ $text }}
                </button>
            @endforeach
        </div>
    </div>
</div>
