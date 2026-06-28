@php $inWorkspace = app('nawasara.workspaces')->current() !== null; @endphp

<header
    class="sticky top-0 inset-x-0 z-48 w-full bg-white border-b border-gray-200 text-sm dark:bg-neutral-800 dark:border-neutral-700 {{ $inWorkspace ? 'lg:ps-65' : '' }}">
    <nav class="px-4 sm:px-6 h-14 flex items-center gap-3 mx-auto">
        {{-- Logo: always on mobile, on desktop only when not in workspace --}}
        <div class="flex items-center {{ $inWorkspace ? 'lg:hidden' : '' }}">
            <a href="{{ url('/') }}" wire:navigate aria-label="Home"
                class="flex-none rounded-md text-xl inline-block font-semibold focus:outline-hidden focus:opacity-80">
                <x-nawasara-ui::brand-logo height="h-7" />
            </a>
        </div>

        {{-- Workspace switcher (desktop) --}}
        <div class="hidden md:block">
            <x-nawasara-ui::workspace-switcher />
        </div>

        {{-- Command palette trigger (⌘K). Full pill on sm+, icon-only on mobile. --}}
        <button type="button" @click="$store.palette.open = true"
            class="ms-2 inline-flex items-center gap-2 rounded-lg border border-gray-200 bg-gray-50 px-2.5 py-1.5 text-sm text-gray-500 hover:bg-gray-100 hover:text-gray-700 transition-colors dark:bg-neutral-900/50 dark:border-neutral-700 dark:text-neutral-400 dark:hover:bg-neutral-700 dark:hover:text-neutral-200"
            aria-label="Cari (Ctrl+K)">
            <x-lucide-search class="size-4" />
            <span class="hidden sm:inline">Cari…</span>
            <kbd class="hidden sm:inline-flex items-center gap-0.5 text-[11px] font-medium border border-gray-200 dark:border-neutral-600 rounded px-1 ms-1">
                <span class="text-xs">⌘</span>K
            </kbd>
        </button>

        {{-- Right cluster --}}
        <div class="ms-auto flex items-center gap-1">
            <x-nawasara-ui::dark-mode-toggle />
            @livewire('nawasara-ui.shared-components.account-dropdown')
        </div>
    </nav>

    {{-- Global ⌘K command palette (mounted once). --}}
    <x-nawasara-ui::command-palette />
</header>
