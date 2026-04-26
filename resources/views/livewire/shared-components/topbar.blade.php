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

        {{-- Right cluster --}}
        <div class="ms-auto flex items-center gap-1">
            <x-nawasara-ui::dark-mode-toggle />
            @livewire('nawasara-ui.shared-components.account-dropdown')
        </div>
    </nav>
</header>
