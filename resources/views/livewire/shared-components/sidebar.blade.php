<!-- Sidebar -->
<div id="hs-application-sidebar"
    class="hs-overlay  [--auto-close:lg]
  hs-overlay-open:translate-x-0
  -translate-x-full transition-all duration-300 transform
  w-65 h-full
  hidden
  fixed inset-y-0 start-0 z-60
  bg-white border-e border-gray-200
  lg:block lg:translate-x-0 lg:end-auto lg:bottom-0
  dark:bg-neutral-800 dark:border-neutral-700"
    role="dialog" tabindex="-1" aria-label="Sidebar">
    <div class="relative flex flex-col h-full max-h-full">
        <div class="px-8 pt-4 pb-4 flex items-center border-b border-gray-200 dark:border-neutral-700">
            <a href="{{ url('/') }}" wire:navigate aria-label="Home"
                class="flex-none rounded-xl text-xl inline-block font-semibold focus:outline-hidden focus:opacity-80">
                <x-nawasara-ui::brand-logo height="h-8" />
            </a>
        </div>

        <!-- Content -->
        <div x-data x-init="
                $el.scrollTop = sessionStorage.getItem('sidebar-scroll') || 0;
                $el.addEventListener('scroll', () => sessionStorage.setItem('sidebar-scroll', $el.scrollTop));
            "
            class="h-full overflow-y-auto [&::-webkit-scrollbar]:w-2 [&::-webkit-scrollbar-thumb]:rounded-full [&::-webkit-scrollbar-track]:bg-gray-100 [&::-webkit-scrollbar-thumb]:bg-gray-300 dark:[&::-webkit-scrollbar-track]:bg-neutral-700 dark:[&::-webkit-scrollbar-thumb]:bg-neutral-500">
            <nav class="hs-accordion-group relative space-y-8 pt-5 pb-10 sm:pt-7 px-4 sm:px-8 lg:my-0"
                data-hs-accordion-always-open>
                <ul class="space-y-1">
                    <li>
                        <a href="{{ url('/') }}" wire:navigate
                            class="flex items-center gap-2 text-sm font-semibold text-gray-700 hover:text-green-600 focus:outline-hidden focus:text-green-600 dark:text-neutral-300 dark:hover:text-green-500">
                            <x-lucide-home class="shrink-0 size-4 text-green-600 dark:text-green-500" />
                            Home
                        </a>
                    </li>
                </ul>
                @php
                    $currentUrl = url()->current();
                    $workspaces = app('nawasara.workspaces');
                    $currentWorkspaceMenu = $workspaces->currentMenu();
                    // If not in a workspace (Home/root), show all accessible workspace headings.
                    $menusToRender = $currentWorkspaceMenu ? [$currentWorkspaceMenu] : app('nawasara.menu');
                @endphp

                <ul class="space-y-3" x-data="{ show: false }" x-init="setTimeout(() => show = true, 10)"
                    x-show="show"
                    x-transition:enter="transition ease-out duration-200"
                    x-transition:enter-start="opacity-0 translate-x-1"
                    x-transition:enter-end="opacity-100 translate-x-0">
                    @foreach ($menusToRender as $menu)
                        @if (!empty($menu['submenu']))
                            <!-- Section heading -->
                            <li>
                                <div class="mb-1 flex items-center gap-2 text-sm font-semibold text-gray-700 dark:text-neutral-300">
                                    @if (! empty($menu['icon']))
                                        <x-dynamic-component :component="$menu['icon']" class="size-4 text-green-600 dark:text-green-500" />
                                    @endif
                                    {{ $menu['label'] }}
                                </div>
                                <ul class="space-y-1 border-l border-gray-200 dark:border-gray-700">
                                    @foreach ($menu['submenu'] as $submenu)
                                        @php $isActive = $currentUrl === url($submenu['url']); @endphp
                                        @if (empty($submenu['permission']) || optional(auth()->user())->can($submenu['permission']))
                                            <li>
                                                <a href="{{ url($submenu['url']) }}"
                                                    @isset($submenu['navigate']) @if ($submenu['navigate']) wire:navigate.hover @endif  @endisset)
                                                    @class([
                                                        'flex items-center gap-2 px-4 py-1.5 text-sm rounded-none border-l-3 transition',
                                                        'border-transparent text-gray-700 dark:text-gray-300 hover:border-green-600 hover:text-green-700 dark:hover:text-gray-100' => !$isActive,
                                                        'border-green-600 text-green-700 dark:text-green-400 font-semibold' => $isActive,
                                                    ])>
                                                    @if (!empty($submenu['icon']))
                                                        <i class="{{ $submenu['icon'] }} text-base"></i>
                                                    @endif
                                                    <span>{{ $submenu['label'] }}</span>
                                                </a>
                                            </li>
                                        @endif
                                    @endforeach
                                </ul>
                            </li>
                        @else
                            @php $isActive = $currentUrl === url($menu['url']); @endphp
                            <li>
                                <a href="{{ url($menu['url']) }}" @class([
                                    'flex items-center gap-2 px-4 py-1.5 text-sm font-medium rounded-none border-l-3 transition',
                                    'border-transparent text-gray-700 dark:text-gray-300 hover:border-green-600 hover:text-green-700 dark:hover:text-gray-100' => !$isActive,
                                    'border-green-600 text-green-700 dark:text-green-400 font-semibold' => $isActive,
                                ])>
                                    @if (!empty($menu['icon']))
                                        <i class="{{ $menu['icon'] }} text-base"></i>
                                    @endif
                                    <span>{{ $menu['label'] }}</span>
                                </a>
                            </li>
                        @endif
                    @endforeach
                </ul>
            </nav>
        </div>
        <!-- End Content -->
    </div>
</div>
<!-- End Sidebar -->
