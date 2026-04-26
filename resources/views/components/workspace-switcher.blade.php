@php
    /** @var \Nawasara\Ui\Services\WorkspaceManager $ws */
    $ws = app('nawasara.workspaces');
    $current = $ws->current();
    $workspaces = $ws->accessible();
@endphp

<div x-data="{ open: false }" @keydown.escape.window="open = false" class="relative">
    {{-- Trigger button --}}
    <button type="button"
        @click="open = !open"
        class="inline-flex items-center gap-2 px-3 py-1.5 rounded-lg border border-gray-200 bg-white hover:bg-gray-50 text-sm font-medium text-gray-800 transition-colors dark:bg-neutral-800 dark:border-neutral-700 dark:text-neutral-200 dark:hover:bg-neutral-700">
        @if ($current)
            <x-dynamic-component :component="$current['icon']" class="size-4 text-green-600 dark:text-green-500" />
            <span>{{ $current['label'] }}</span>
        @else
            <x-lucide-layout-grid class="size-4 text-gray-500 dark:text-neutral-400" />
            <span class="text-gray-500 dark:text-neutral-400">Pilih Workspace</span>
        @endif
        <x-lucide-chevron-down class="size-3.5 text-gray-400" x-bind:class="{ 'rotate-180': open }" />
    </button>

    {{-- Dropdown panel --}}
    <div x-show="open" x-cloak
        x-transition:enter="transition ease-out duration-150"
        x-transition:enter-start="opacity-0 translate-y-1"
        x-transition:enter-end="opacity-100 translate-y-0"
        x-transition:leave="transition ease-in duration-75"
        x-transition:leave-start="opacity-100 translate-y-0"
        x-transition:leave-end="opacity-0 translate-y-1"
        @click.outside="open = false"
        class="absolute start-0 mt-2 w-72 bg-white dark:bg-neutral-800 rounded-xl shadow-lg border border-gray-200 dark:border-neutral-700 z-60 overflow-hidden">

        {{-- Home link --}}
        <a href="{{ url('/') }}" wire:navigate @click="open = false"
            class="flex items-center gap-3 px-4 py-2.5 text-sm border-b border-gray-100 dark:border-neutral-700
                {{ ! $current ? 'bg-green-50 text-green-700 font-semibold dark:bg-green-900/20 dark:text-green-400' : 'text-gray-700 hover:bg-gray-50 dark:text-neutral-300 dark:hover:bg-neutral-700' }}">
            <x-lucide-home class="size-4" />
            <span>Home / Dashboard</span>
            @if (! $current)
                <x-lucide-check class="size-4 ml-auto" />
            @endif
        </a>

        {{-- Workspace list --}}
        <div class="max-h-[60vh] overflow-y-auto py-1
            [color-scheme:light] dark:[color-scheme:dark]
            [&::-webkit-scrollbar]:w-1.5
            [&::-webkit-scrollbar]:bg-transparent
            [&::-webkit-scrollbar-track]:bg-transparent
            [&::-webkit-scrollbar-corner]:bg-transparent
            [&::-webkit-scrollbar-thumb]:rounded-full
            [&::-webkit-scrollbar-thumb]:bg-gray-300/60
            hover:[&::-webkit-scrollbar-thumb]:bg-gray-400/80
            dark:[&::-webkit-scrollbar-thumb]:bg-neutral-600/60
            dark:hover:[&::-webkit-scrollbar-thumb]:bg-neutral-500/80
            [scrollbar-width:thin]
            [scrollbar-color:rgb(209_213_219_/_0.6)_transparent]
            dark:[scrollbar-color:rgb(82_82_82_/_0.6)_transparent]">
            @forelse ($workspaces as $workspace)
                @php $isActive = $current && $current['id'] === $workspace['id']; @endphp
                <a href="{{ $workspace['first_url'] ?? '#' }}" wire:navigate @click="open = false"
                    class="flex items-center gap-3 px-4 py-2.5 text-sm
                        {{ $isActive
                            ? 'bg-green-50 text-green-700 font-semibold dark:bg-green-900/20 dark:text-green-400'
                            : 'text-gray-700 hover:bg-gray-50 dark:text-neutral-300 dark:hover:bg-neutral-700' }}">
                    <div class="flex items-center justify-center size-8 rounded-lg
                        {{ $isActive ? 'bg-green-100 text-green-600 dark:bg-green-900/30 dark:text-green-400' : 'bg-gray-100 text-gray-500 dark:bg-neutral-700 dark:text-neutral-400' }}">
                        <x-dynamic-component :component="$workspace['icon']" class="size-4" />
                    </div>
                    <div class="flex-1 min-w-0">
                        <div class="truncate">{{ $workspace['label'] }}</div>
                        <div class="text-xs text-gray-500 dark:text-neutral-500">{{ $workspace['submenu_count'] }} menu</div>
                    </div>
                    @if ($isActive)
                        <x-lucide-check class="size-4" />
                    @endif
                </a>
            @empty
                <div class="px-4 py-8 text-center text-sm text-gray-500 dark:text-neutral-400">
                    Tidak ada workspace yang bisa diakses
                </div>
            @endforelse
        </div>
    </div>
</div>
