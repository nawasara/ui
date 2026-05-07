{{-- Custom Tailwind pagination view for Laravel paginator + Livewire.

     Wired via Paginator::defaultView('nawasara-ui::components.pagination')
     in UiServiceProvider, so any {{ $paginator->links() }} call uses this
     template.

     Visual design:
     - Numbered buttons are individual rounded pills (gap-1), not joined.
     - Active page: emerald-700 fill, white text (matches brand).
     - Idle page: white bg, gray border, hover-tint emerald-50 + emerald-200
       border.
     - Prev/next: icon-only chevrons. Disabled when at start/end.
     - Ellipsis: plain '...' span, no border (consistent with active being
       the only solid pill).
     - Mobile (<sm): collapses to "Prev | page X of Y | Next" so it fits a
       narrow viewport.
     - Above sm: full numbered pagination + "Showing X to Y of Z results"
       on the left.

     Compatible with both Livewire wire:navigate (links retain href) and
     bare Laravel pagination. Livewire automatically intercepts clicks. --}}

@if ($paginator->hasPages())
    @php
        $btnIdle = 'inline-flex items-center justify-center min-w-9 h-9 px-3 text-sm font-medium rounded-lg border border-gray-200 bg-white text-gray-700 hover:bg-emerald-50 hover:border-emerald-200 hover:text-emerald-800 dark:bg-neutral-800 dark:border-neutral-700 dark:text-neutral-200 dark:hover:bg-emerald-900/20 dark:hover:border-emerald-800/50 dark:hover:text-emerald-400 transition-colors';
        $btnActive = 'inline-flex items-center justify-center min-w-9 h-9 px-3 text-sm font-semibold rounded-lg border border-emerald-700 bg-emerald-700 text-white dark:bg-emerald-600 dark:border-emerald-600 shadow-sm cursor-default';
        $btnDisabled = 'inline-flex items-center justify-center min-w-9 h-9 px-3 text-sm font-medium rounded-lg border border-gray-200 bg-gray-50 text-gray-400 dark:bg-neutral-900 dark:border-neutral-700 dark:text-neutral-600 cursor-not-allowed';
        $iconBtnIdle = 'inline-flex items-center justify-center size-9 rounded-lg border border-gray-200 bg-white text-gray-600 hover:bg-emerald-50 hover:border-emerald-200 hover:text-emerald-700 dark:bg-neutral-800 dark:border-neutral-700 dark:text-neutral-300 dark:hover:bg-emerald-900/20 dark:hover:border-emerald-800/50 dark:hover:text-emerald-400 transition-colors';
        $iconBtnDisabled = 'inline-flex items-center justify-center size-9 rounded-lg border border-gray-200 bg-gray-50 text-gray-300 dark:bg-neutral-900 dark:border-neutral-700 dark:text-neutral-700 cursor-not-allowed';
    @endphp

    <nav role="navigation" aria-label="{{ __('Pagination Navigation') }}"
         class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">

        {{-- Result counter (desktop only — saves precious vertical space on mobile) --}}
        <p class="hidden sm:block text-sm text-gray-600 dark:text-neutral-400">
            Menampilkan
            @if ($paginator->firstItem())
                <span class="font-semibold text-gray-800 dark:text-neutral-200">{{ $paginator->firstItem() }}</span>
                –
                <span class="font-semibold text-gray-800 dark:text-neutral-200">{{ $paginator->lastItem() }}</span>
            @else
                {{ $paginator->count() }}
            @endif
            dari
            <span class="font-semibold text-gray-800 dark:text-neutral-200">{{ $paginator->total() }}</span>
            {{ __('results') }}
        </p>

        {{-- Mobile: simplified prev / current page / next.
             Hides the numbered list entirely so the whole control fits in
             one row of a narrow viewport. --}}
        <div class="flex sm:hidden items-center justify-between gap-2 w-full">
            @if ($paginator->onFirstPage())
                <span class="{{ $iconBtnDisabled }}" aria-disabled="true">
                    <x-lucide-chevron-left class="size-4" />
                </span>
            @else
                <a href="{{ $paginator->previousPageUrl() }}" rel="prev" class="{{ $iconBtnIdle }}"
                    aria-label="{{ __('pagination.previous') }}">
                    <x-lucide-chevron-left class="size-4" />
                </a>
            @endif

            <span class="text-sm text-gray-600 dark:text-neutral-400">
                Halaman <span class="font-semibold text-gray-800 dark:text-neutral-200">{{ $paginator->currentPage() }}</span>
                dari <span class="font-semibold text-gray-800 dark:text-neutral-200">{{ $paginator->lastPage() }}</span>
            </span>

            @if ($paginator->hasMorePages())
                <a href="{{ $paginator->nextPageUrl() }}" rel="next" class="{{ $iconBtnIdle }}"
                    aria-label="{{ __('pagination.next') }}">
                    <x-lucide-chevron-right class="size-4" />
                </a>
            @else
                <span class="{{ $iconBtnDisabled }}" aria-disabled="true">
                    <x-lucide-chevron-right class="size-4" />
                </span>
            @endif
        </div>

        {{-- Desktop: full numbered pagination --}}
        <div class="hidden sm:flex items-center gap-1">
            {{-- Previous --}}
            @if ($paginator->onFirstPage())
                <span class="{{ $iconBtnDisabled }}" aria-disabled="true" aria-label="{{ __('pagination.previous') }}">
                    <x-lucide-chevron-left class="size-4" />
                </span>
            @else
                <a href="{{ $paginator->previousPageUrl() }}" rel="prev" class="{{ $iconBtnIdle }}"
                    aria-label="{{ __('pagination.previous') }}">
                    <x-lucide-chevron-left class="size-4" />
                </a>
            @endif

            {{-- Numbered pages + ellipsis --}}
            @foreach ($elements as $element)
                {{-- Ellipsis separator --}}
                @if (is_string($element))
                    <span class="inline-flex items-center justify-center min-w-9 h-9 px-2 text-sm text-gray-400 dark:text-neutral-600 select-none"
                          aria-hidden="true">{{ $element }}</span>
                @endif

                {{-- Array of page links --}}
                @if (is_array($element))
                    @foreach ($element as $page => $url)
                        @if ($page == $paginator->currentPage())
                            <span class="{{ $btnActive }}" aria-current="page">{{ $page }}</span>
                        @else
                            <a href="{{ $url }}" class="{{ $btnIdle }}"
                                aria-label="{{ __('Go to page :page', ['page' => $page]) }}">{{ $page }}</a>
                        @endif
                    @endforeach
                @endif
            @endforeach

            {{-- Next --}}
            @if ($paginator->hasMorePages())
                <a href="{{ $paginator->nextPageUrl() }}" rel="next" class="{{ $iconBtnIdle }}"
                    aria-label="{{ __('pagination.next') }}">
                    <x-lucide-chevron-right class="size-4" />
                </a>
            @else
                <span class="{{ $iconBtnDisabled }}" aria-disabled="true" aria-label="{{ __('pagination.next') }}">
                    <x-lucide-chevron-right class="size-4" />
                </span>
            @endif
        </div>
    </nav>
@endif
