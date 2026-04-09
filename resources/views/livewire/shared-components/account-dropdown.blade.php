<div class="hs-dropdown [--placement:bottom-right] relative inline-flex">
    <button id="hs-dropdown-account" type="button"
        class="size-9.5 inline-flex justify-center items-center gap-x-2 text-sm font-semibold rounded-full border border-transparent text-gray-800 focus:outline-hidden disabled:opacity-50 disabled:pointer-events-none dark:text-white"
        aria-haspopup="menu" aria-expanded="false" aria-label="Dropdown">
        <img class="shrink-0 size-9.5 rounded-full"
            src="https://images.unsplash.com/photo-1568602471122-7832951cc4c5?ixlib=rb-4.0.3&ixid=MnwxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8&auto=format&fit=facearea&facepad=2&w=320&h=320&q=80"
            alt="Avatar">
    </button>

    <div class="hs-dropdown-menu transition-[opacity,margin] duration hs-dropdown-open:opacity-100 opacity-0 hidden min-w-60 bg-white shadow-md rounded-lg mt-2 dark:bg-neutral-800 dark:border dark:border-neutral-700 dark:divide-neutral-700 after:h-4 after:absolute after:-bottom-4 after:start-0 after:w-full before:h-4 before:absolute before:-top-4 before:start-0 before:w-full"
        role="menu" aria-orientation="vertical" aria-labelledby="hs-dropdown-account">
        <div class="py-3 px-5 bg-gray-100 rounded-t-lg dark:bg-neutral-700">
            <p class="text-sm text-gray-500 dark:text-neutral-500">Signed in as</p>
            <p class="text-sm font-medium text-gray-800 dark:text-neutral-200">{{ auth()->user()->name ?? '-' }}</p>
        </div>
        <div class="p-1.5 space-y-0.5">
            @stack('profile-links')
            <a class="flex items-center gap-x-3.5 py-2 px-3 rounded-lg text-sm text-gray-800 hover:bg-gray-100 focus:outline-hidden focus:bg-gray-100 dark:text-neutral-400 dark:hover:bg-neutral-700 dark:hover:text-neutral-300 dark:focus:bg-neutral-700 dark:focus:text-neutral-300"
                href="#">
                <x-lucide-user-round class="shrink-0 size-4" />
                View Profile
            </a>
            <a class="flex items-center gap-x-3.5 py-2 px-3 rounded-lg text-sm text-gray-800 hover:bg-gray-100 focus:outline-hidden focus:bg-gray-100 dark:text-neutral-400 dark:hover:bg-neutral-700 dark:hover:text-neutral-300 dark:focus:bg-neutral-700 dark:focus:text-neutral-300"
                href="{{ route('nawasara-core.switch-role') }}" wire:navigate>
                <x-lucide-refresh-ccw-dot class="shrink-0 size-4" />
                Switch Role ({{ session('active_role', 'N/A') }})
            </a>

            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit"
                    class="w-full flex items-center gap-x-3.5 py-2 px-3 rounded-lg text-sm text-gray-800 hover:bg-gray-100 focus:outline-hidden focus:bg-gray-100 dark:text-neutral-400 dark:hover:bg-neutral-700 dark:hover:text-neutral-300 dark:focus:bg-neutral-700 dark:focus:text-neutral-300">
                    <x-lucide-log-out class="shrink-0 size-4" />
                    Logout
                </button>
            </form>
        </div>
    </div>
</div>
