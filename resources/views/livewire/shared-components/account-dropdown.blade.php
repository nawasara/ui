@php
    $user = auth()->user();
    $name = $user?->name ?? 'Guest';
    $email = $user?->email ?? '';
    $initials = collect(explode(' ', trim($name)))
        ->take(2)
        ->map(fn ($p) => mb_strtoupper(mb_substr($p, 0, 1)))
        ->implode('');
    $activeRole = session('active_role');
    $roleNames = $user?->roles?->pluck('name')->all() ?? [];
@endphp

<div class="hs-dropdown [--placement:bottom-right] relative inline-flex">
    <button id="hs-dropdown-account" type="button"
        class="size-9 inline-flex justify-center items-center text-sm font-semibold rounded-full bg-green-100 text-green-700 dark:bg-green-900/40 dark:text-green-300 hover:ring-2 hover:ring-green-500/30 focus:outline-hidden focus:ring-2 focus:ring-green-500/40 transition"
        aria-haspopup="menu" aria-expanded="false" :title="$name">
        {{ $initials ?: '?' }}
    </button>

    <div class="hs-dropdown-menu transition-[opacity,margin] duration hs-dropdown-open:opacity-100 opacity-0 hidden min-w-64 bg-white shadow-lg rounded-lg mt-2 dark:bg-neutral-800 border border-gray-200 dark:border-neutral-700 overflow-hidden after:h-4 after:absolute after:-bottom-4 after:start-0 after:w-full before:h-4 before:absolute before:-top-4 before:start-0 before:w-full"
        role="menu" aria-orientation="vertical" aria-labelledby="hs-dropdown-account">

        {{-- Profile header --}}
        <div class="px-4 py-3 bg-gray-50 dark:bg-neutral-700/50 border-b border-gray-200 dark:border-neutral-700">
            <div class="flex items-center gap-3">
                <div class="size-10 inline-flex items-center justify-center rounded-full bg-green-100 text-green-700 dark:bg-green-900/40 dark:text-green-300 font-semibold">
                    {{ $initials ?: '?' }}
                </div>
                <div class="min-w-0 flex-1">
                    <div class="text-sm font-semibold text-gray-800 dark:text-neutral-200 truncate">{{ $name }}</div>
                    @if ($email)
                        <div class="text-xs text-gray-500 dark:text-neutral-400 truncate">{{ $email }}</div>
                    @endif
                </div>
            </div>

            @if (! empty($roleNames))
                <div class="mt-2 flex items-center gap-1.5 text-xs">
                    @if ($activeRole)
                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-400 font-medium">
                            <x-lucide-check class="size-3" />
                            {{ $activeRole }}
                        </span>
                        <span class="text-gray-400">aktif</span>
                    @else
                        <span class="text-gray-500 dark:text-neutral-400">Roles:</span>
                        @foreach ($roleNames as $r)
                            <span class="inline-flex items-center px-1.5 py-0.5 rounded bg-gray-100 dark:bg-neutral-700 text-gray-600 dark:text-neutral-300">{{ $r }}</span>
                        @endforeach
                    @endif
                </div>
            @endif
        </div>

        {{-- Menu items --}}
        <div class="p-1.5 space-y-0.5">
            @stack('profile-links')

            @if (count($roleNames) > 1)
                <a class="flex items-center gap-3 py-2 px-3 rounded-lg text-sm text-gray-800 hover:bg-gray-100 dark:text-neutral-300 dark:hover:bg-neutral-700"
                    href="{{ route('nawasara-core.switch-role') }}" wire:navigate>
                    <x-lucide-refresh-ccw-dot class="shrink-0 size-4 text-gray-500" />
                    <span class="flex-1">Switch Role</span>
                </a>
            @endif

            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit"
                    class="w-full flex items-center gap-3 py-2 px-3 rounded-lg text-sm text-gray-800 hover:bg-red-50 hover:text-red-700 dark:text-neutral-300 dark:hover:bg-red-900/20 dark:hover:text-red-400">
                    <x-lucide-log-out class="shrink-0 size-4" />
                    <span class="flex-1 text-left">Logout</span>
                </button>
            </form>
        </div>
    </div>
</div>
