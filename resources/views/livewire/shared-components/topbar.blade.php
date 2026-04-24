@php $inWorkspace = app('nawasara.workspaces')->current() !== null; @endphp

<!-- ========== HEADER ========== -->
<header
    class="sticky top-0 inset-x-0 flex flex-wrap md:justify-start md:flex-nowrap z-48 w-full bg-white border-b border-gray-200 text-sm py-2.5 {{ $inWorkspace ? 'lg:ps-65' : '' }} dark:bg-neutral-800 dark:border-neutral-700">
    <nav class="px-4 sm:px-6 flex basis-full items-center w-full mx-auto">
        {{-- Logo: always visible on mobile. Visible on desktop only when NOT in workspace (i.e. at Home) --}}
        <div class="me-5 flex items-center {{ $inWorkspace ? 'lg:hidden' : '' }}">
            <a href="{{ url('/') }}" wire:navigate aria-label="Home"
                class="flex-none rounded-md text-xl inline-block font-semibold focus:outline-hidden focus:opacity-80">
                <x-nawasara-ui::brand-logo height="h-7" />
            </a>
        </div>

        <div class="w-full flex items-center justify-end ms-auto md:justify-between gap-x-1 md:gap-x-3">

            <!-- Workspace Switcher -->
            <div class="hidden md:block">
                <x-nawasara-ui::workspace-switcher />
            </div>
            <!-- End Workspace Switcher -->

            <div class="flex flex-row items-center justify-end gap-1">
                <x-nawasara-ui::dark-mode-toggle />
                <button type="button"
                    class="size-9.5 relative inline-flex justify-center items-center gap-x-2 text-sm font-semibold rounded-full border border-transparent text-gray-800 hover:bg-gray-100 focus:outline-hidden focus:bg-gray-100 disabled:opacity-50 disabled:pointer-events-none dark:text-white dark:hover:bg-neutral-700 dark:focus:bg-neutral-700">
                    <x-lucide-bell class="shrink-0 size-4" />
                    <span class="sr-only">Notifications</span>
                </button>

                @livewire('nawasara-ui.shared-components.account-dropdown')

            </div>
        </div>
    </nav>
</header>
<!-- ========== END HEADER ========== -->
