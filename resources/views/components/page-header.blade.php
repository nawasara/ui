@props([
    /**
     * Page title (left side, large heading).
     * Required for the component to render anything visible.
     */
    'title' => '',
    /**
     * Optional small description rendered under the title in muted gray.
     * Useful for one-line context ("Riwayat semua percobaan login user").
     */
    'description' => null,
    /**
     * Optional count badge shown next to the title (e.g. total items).
     * Pass an int or string. Renders as a subtle pill.
     */
    'count' => null,
])

{{-- <x-page-header> — opinionated page-level header row.

     Layout: title (+ optional count badge + description) on the left,
     freeform action zone on the right via the default `$slot`. The
     right side is meant for page-scoped controls like time-window,
     primary action ("+ Tambah"), or status indicators. Filter +
     search + secondary actions stay in the toolbar BELOW the header.

     Why a dedicated component instead of inline divs per page?
     - Consistent vertical rhythm across modules (the visual offset
       between title and toolbar should be one number, not five).
     - Centralised mobile breakpoint behaviour: stacks vertically on
       <md, side-by-side on md+, with the action zone wrapping its
       own content on tighter widths.
     - Future-proof: when we add things like breadcrumb, beta-feature
       chips, or page-status indicators, only this file changes.

     Usage:
       <x-nawasara-ui::page-header title="Login History" count="78">
           <x-nawasara-ui::time-window :window="$window" :from="$from" :to="$to" />
           @can('audit.login.create')
               <x-nawasara-ui::button color="primary">+ Tambah</x-nawasara-ui::button>
           @endcan
       </x-nawasara-ui::page-header> --}}

<div class="mb-4 flex flex-col gap-3 md:flex-row md:items-center md:justify-between">
    {{-- Left: title + optional badge + description.
         min-w-0 lets the title truncate gracefully when the action zone
         is wide enough to push it. --}}
    <div class="min-w-0">
        <div class="flex items-center gap-2 flex-wrap">
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white truncate">
                {{ $title }}
            </h1>
            @if ($count !== null)
                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-700 dark:bg-neutral-700 dark:text-neutral-300">
                    {{ $count }}
                </span>
            @endif
        </div>
        @if ($description)
            <p class="mt-0.5 text-sm text-gray-500 dark:text-neutral-400">
                {{ $description }}
            </p>
        @endif
    </div>

    {{-- Right: action zone. flex-wrap so multiple controls (time-window
         + primary button + secondary buttons) wrap cleanly when the
         viewport narrows. shrink-0 on the wrapper so the action zone
         doesn't get squeezed by a long title; instead the title
         truncates first. --}}
    @if (trim($slot) !== '')
        <div class="flex items-center flex-wrap gap-2 md:shrink-0">
            {{ $slot }}
        </div>
    @endif
</div>
