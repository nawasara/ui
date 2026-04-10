@props([
    'searchPlaceholder' => 'Cari ...',
    'searchModel' => null,
])

<div class="space-y-2 mb-4">
    {{-- Filter row --}}
    <div class="flex flex-wrap items-center gap-2">
        {{-- Filter dropdown slots --}}
        {{ $slot }}

        {{-- Search --}}
        @if ($searchModel)
            <div class="relative flex-1 min-w-48">
                <div class="absolute inset-y-0 start-0 flex items-center pointer-events-none ps-3.5">
                    <x-lucide-search class="shrink-0 size-4 text-gray-400 dark:text-neutral-500" />
                </div>
                <input type="text"
                    wire:model.live.debounce.300ms="{{ $searchModel }}"
                    placeholder="{{ $searchPlaceholder }}"
                    class="py-2.5 ps-10 pe-4 block w-full border border-gray-200 rounded-lg text-sm focus:border-green-500 focus:ring-green-500 dark:bg-neutral-900 dark:border-neutral-700 dark:text-neutral-400 dark:placeholder-neutral-500 dark:focus:ring-neutral-600" />
            </div>
        @endif

        {{-- Extra actions slot --}}
        @if (isset($actions))
            {{ $actions }}
        @endif
    </div>

    {{-- Active filter chips --}}
    @if (isset($chips))
        <div class="flex flex-wrap items-center gap-2">
            {{ $chips }}
        </div>
    @endif
</div>
