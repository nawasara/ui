@props([
    'label' => 'Filter',
    /**
     * Initial state per model. Shape:
     *   ['typeFilter' => ['A', 'AAAA'], 'sort' => 'newest']
     * Single-select values are scalar (string/int). Multi-select values are array.
     * The Alpine state always normalises to array internally; flush converts back
     * to scalar when the option is declared as single-select.
     */
    'state' => [],
    /**
     * Multi-select model names. Models NOT in this list are treated as single-select
     * (radio behaviour: picking a value replaces previous, picking same value clears).
     */
    'multiple' => [],
    /**
     * Optional: human-readable labels for chips. Shape:
     *   ['typeFilter' => ['A' => 'A record', 'AAAA' => 'AAAA record']]
     * Falls back to value itself when missing.
     */
    'labels' => [],
    /**
     * Optional dimension labels for ambiguous values. Shape:
     *   ['sort' => 'Urutkan']
     * When set, chip renders as "Urutkan: Terbaru ×" instead of just "Terbaru ×".
     */
    'dimensions' => [],
    'debounceMs' => 3000,
])

@php
    // Stable id for ARIA + Alpine ref scoping
    $id = 'fp-'.substr(md5(serialize([$label, array_keys($state)])), 0, 8);
@endphp

{{-- Filter panel: Alpine-driven cascading filter with deferred server sync.

     Architecture:
     - Local Alpine state holds DRAFT values; UI mutations (toggle option,
       remove chip) are instant. No server roundtrip per click.
     - 3s debounce after last mutation triggers $wire.set + $commit, batching
       all changes into one request.
     - Force flush triggers: panel close, Apply button click, chip remove,
       beforeunload (best-effort to avoid data loss).
     - Single-select models clear when same value re-clicked (radio toggle).
       Multi-select models add/remove independently.

     wire:ignore on root because Livewire morph after $wire.set/commit would
     otherwise tear down the Alpine instance (initial @js() prop changes →
     attribute value changes → element replaced → state lost). The component
     is fully Alpine-managed; server only learns about state via $wire.set
     calls from inside the panel itself, never the other way around within
     a single page lifecycle. --}}
<div wire:ignore wire:key="{{ $id }}"
    x-data="filterPanel({
        initial: @js((object) $state),
        multipleModels: @js(array_values($multiple)),
        labels: @js((object) $labels),
        dimensions: @js((object) $dimensions),
        debounceMs: {{ (int) $debounceMs }},
    })"
    x-on:beforeunload.window="forceFlush()"
    class="contents">

    {{-- Trigger button: Filter (n) ▾ with dirty dot indicator --}}
    {{-- Auto-close strategy:
         - Preline's [--auto-close:false] turns OFF its built-in auto-close
           because in 3.2.3 the option-click filter is unreliable - any click
           inside the menu was closing the panel even on <button> options.
         - We replace it with our own idle-timer: 3s of no interaction inside
           the panel triggers close. Reset on click/mouseenter/keydown.
         - Outside click: handled explicitly via x-on:click.outside (Alpine
           ignores nested children of the listening element so options inside
           don't fire it).
         - Esc key closes (a11y).
         - Both close paths fire hide.hs.dropdown → onPanelClose() → flush. --}}
    {{-- Paksa Preline membuka panel ke bawah, matikan auto-flip ke atas.

         --placement: bottom-start  → preferred placement
         --flip: false              → matikan auto-flip (saat ruang bawah
                                       kurang, Preline default flip ke atas;
                                       panel ini 480px sering bertabrakan
                                       dengan header pada scroll position
                                       default karena ada stat-cards di atas
                                       toolbar)
         --strategy: fixed          → positioning relatif viewport, bukan
                                       container (mencegah container yang
                                       di-clip memotong panel)

         Properti CSS custom Preline dibaca dari computed style, jadi inline
         `style=""` di-honor; attribute `data-hs-dropdown-*` (yang sempat
         saya coba) bukan API resmi dan diabaikan. --}}
    <div class="hs-dropdown relative inline-flex [--auto-close:false]"
         style="--placement: bottom-start; --flip: false; --strategy: fixed;"
         x-on:hide.hs.dropdown="onPanelClose()">
        <button id="{{ $id }}-toggle" type="button"
            class="hs-dropdown-toggle py-2.5 px-4 inline-flex items-center gap-x-2 text-sm font-medium rounded-lg border shadow-sm focus:outline-none disabled:opacity-50 disabled:pointer-events-none transition-colors"
            x-bind:class="hasActive
                ? 'border-emerald-200 bg-emerald-50 text-emerald-800 hover:bg-emerald-100 dark:bg-emerald-900/20 dark:border-emerald-800/50 dark:text-emerald-400 dark:hover:bg-emerald-900/40'
                : 'border-gray-200 bg-white text-gray-800 hover:bg-gray-50 dark:bg-neutral-800 dark:border-neutral-700 dark:text-white dark:hover:bg-neutral-700'"
            aria-haspopup="menu" aria-expanded="false">
            <x-lucide-list-filter class="size-4" />
            <span>{{ $label }}</span>
            <span x-show="activeCount > 0" x-cloak
                class="inline-flex items-center justify-center min-w-[1.25rem] h-5 px-1.5 rounded-full text-[11px] font-semibold bg-emerald-600 text-white">
                <span x-text="activeCount"></span>
            </span>
            {{-- Dirty indicator: pulsing dot when there are unflushed changes --}}
            <span x-show="isDirty" x-cloak
                class="inline-block size-2 rounded-full bg-amber-500 animate-pulse"
                title="Perubahan belum diterapkan"></span>
            <svg class="hs-dropdown-open:rotate-180 size-4 transition-transform" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="m6 9 6 6 6-6"></path>
            </svg>
        </button>

        {{-- Cascading panel.

             Desktop (sm+): 2-column side-by-side. Left = dimension list
             (14rem), right = value picker (16rem), total 30rem. Both
             columns visible at once.

             Mobile (<sm): drill-down pattern. Width = calc(100vw - 2rem).
             Show dimension list by default; tapping a dimension switches
             the panel to the value picker with a back button. mobileView
             Alpine state controls which view is shown.

             Idle/outside/esc handlers are attached imperatively in init()
             via DOM listeners (NOT Alpine x-on) because wire:ignore on the
             root inhibits Alpine's per-element directive scanning for some
             nested elements. Imperative listeners always fire. --}}
        <div data-fp-menu
            class="hs-dropdown-menu transition-[opacity,margin] duration hs-dropdown-open:opacity-100 opacity-0 hidden z-50 mt-2 bg-white shadow-xl rounded-xl border border-gray-200 dark:bg-neutral-800 dark:border-neutral-700 overflow-hidden w-[calc(100vw-2rem)] max-w-[30rem] sm:w-[30rem]"
            role="menu" aria-orientation="vertical" aria-labelledby="{{ $id }}-toggle">

            <div class="flex flex-col sm:flex-row" style="max-height: 70vh; max-height: 480px;">
                {{-- Left: dimensions list. Desktop: always visible as left column.
                     Mobile: hidden when value picker is active (mobileView == 'values'). --}}
                <div class="sm:w-56 sm:shrink-0 sm:border-r border-gray-200 dark:border-neutral-700 overflow-y-auto py-2 bg-white dark:bg-neutral-800"
                     x-show="mobileView === 'list' || isDesktop()"
                     x-cloak>
                    {{ $slot }}
                </div>

                {{-- Right: value picker. Desktop: always visible. Mobile: only
                     visible when activeDim selected (after user taps a dimension). --}}
                <div class="flex-1 overflow-y-auto py-2 bg-white dark:bg-neutral-800"
                     x-show="mobileView === 'values' || isDesktop()"
                     x-cloak>
                    {{-- Placeholder when no dimension active (desktop only) --}}
                    <div x-show="!activeDim && isDesktop()" class="px-4 py-8 text-center">
                        <div class="text-xs text-gray-400 dark:text-neutral-500">
                            Pilih kategori filter di sebelah kiri
                        </div>
                    </div>

                    {{-- Value picker for active dimension. Reads from activeDimObject()
                         (registry lookup) instead of cached object, so it survives
                         Livewire morph cycles where the original object reference
                         passed to selectDimension() may be stale. --}}
                    <template x-if="activeDimObject()">
                        <div>
                            {{-- Mobile-only back button --}}
                            <button type="button" x-on:click="mobileView = 'list'"
                                x-show="!isDesktop()"
                                class="sm:hidden w-full flex items-center gap-1.5 px-3 py-2 text-xs text-gray-500 hover:text-gray-700 dark:text-neutral-400 dark:hover:text-neutral-200 border-b border-gray-200 dark:border-neutral-700 mb-1">
                                <x-lucide-chevron-left class="size-3.5" />
                                Kembali
                            </button>

                            <div class="px-3 pb-2 text-[11px] font-semibold uppercase tracking-wider text-gray-500 dark:text-neutral-400"
                                 x-text="activeDimObject()?.label"></div>

                            {{-- Search inside value list (shown when more than 7 options) --}}
                            <div x-show="(() => { const d = activeDimObject(); return d && d.items && Object.keys(d.items).length > 7 })()"
                                 class="px-3 pb-2">
                                <div class="relative">
                                    <div class="absolute inset-y-0 start-0 flex items-center pointer-events-none ps-2.5">
                                        <x-lucide-search class="size-3.5 text-gray-400 dark:text-neutral-500" />
                                    </div>
                                    {{-- Focus retention: tiap kali user ketik,
                                         `optionSearch` berubah → x-for
                                         (value list buttons) re-render → focus
                                         browser default melompat ke button
                                         pertama yang baru di-mount. $nextTick
                                         + .focus() di @input handler nge-restore
                                         focus ke search input setelah re-render
                                         frame berikutnya. --}}
                                    {{-- Preline HSAccessibilityObserver hijack:
                                         A singleton listens on document.keydown
                                         in bubble phase. For every single-letter
                                         A-Z keypress while focus is inside any
                                         .hs-dropdown wrapper, it calls
                                         dropdown.onFirstLetter(key) which:
                                           1. focuses the first matching button
                                              (or the first button when no match)
                                              inside the dropdown menu
                                           2. calls e.preventDefault() so the
                                              letter never reaches the input
                                         Net effect on us: typing in the search
                                         box yanks focus to the (often
                                         display:none on desktop) mobile back
                                         button → activeElement falls back to
                                         BODY → @input never fires.
                                         `@keydown.stop` stops propagation in
                                         the bubble path before the document
                                         listener sees it. Default browser
                                         action (text insertion) still happens
                                         because .stop is stopPropagation only,
                                         not preventDefault — so x-model's input
                                         event still fires. The component's own
                                         Esc-to-close uses window capture phase
                                         (init() → _escHandler), so it still
                                         fires before this listener and Esc
                                         continues to close the panel. --}}
                                    <input type="text" x-model="optionSearch"
                                        x-ref="optionSearchInput"
                                        @keydown.stop
                                        @input="$nextTick(() => $refs.optionSearchInput.focus())"
                                        placeholder="Cari..."
                                        class="py-1.5 ps-8 pe-2 block w-full border border-gray-200 rounded-md text-xs focus:border-emerald-600 focus:ring-emerald-600 dark:bg-neutral-900 dark:border-neutral-700 dark:text-neutral-300 dark:placeholder-neutral-500" />
                                </div>
                            </div>

                            <div class="px-1 space-y-0.5">
                                <template x-for="(text, value) in filteredOptions(activeDimObject())" :key="value">
                                    <button type="button"
                                        x-on:click="toggle(activeDim, value)"
                                        class="w-full flex items-center gap-x-2.5 py-1.5 px-2.5 rounded-lg text-sm transition-colors"
                                        x-bind:class="isSelected(activeDim, value)
                                            ? 'bg-emerald-50 text-emerald-800 dark:bg-emerald-900/30 dark:text-emerald-300 font-medium hover:bg-emerald-100 dark:hover:bg-emerald-900/50'
                                            : 'text-gray-700 dark:text-neutral-300 hover:bg-gray-100 dark:hover:bg-neutral-700'">
                                        <template x-if="isSelected(activeDim, value)">
                                            <x-lucide-check-circle-2 class="size-4 shrink-0 text-emerald-600 dark:text-emerald-400" />
                                        </template>
                                        <template x-if="!isSelected(activeDim, value)">
                                            <span class="size-4 shrink-0 rounded-full border border-gray-300 dark:border-neutral-600"></span>
                                        </template>
                                        <span x-text="text" class="text-left truncate"></span>
                                    </button>
                                </template>
                            </div>
                        </div>
                    </template>
                </div>
            </div>

            {{-- Footer: Apply (force flush) + Reset --}}
            <div class="flex items-center justify-between border-t border-gray-200 dark:border-neutral-700 px-3 py-2 bg-gray-50 dark:bg-neutral-800/60">
                <button type="button" x-on:click="resetAll()"
                    x-show="hasActive" x-cloak
                    class="text-xs text-gray-500 hover:text-gray-700 dark:text-neutral-400 dark:hover:text-neutral-200 transition-colors">
                    Reset semua
                </button>
                <span x-show="!hasActive" class="text-xs text-gray-400 dark:text-neutral-500">&nbsp;</span>

                <button type="button" x-on:click="applyNow(); closePanel()"
                    x-show="isDirty" x-cloak
                    class="inline-flex items-center gap-1.5 py-1.5 px-3 rounded-lg text-xs font-semibold bg-emerald-600 text-white hover:bg-emerald-700 transition-colors">
                    <x-lucide-check class="size-3.5" />
                    Terapkan
                </button>
            </div>
        </div>
    </div>

    {{-- Active filter chips: one chip per dimension. Click × clears the whole
         dimension (all selected values within it). To remove a single value,
         user re-opens the panel and unchecks the option.

         Teleported into [data-filter-chips] target so chips render below the
         toolbar instead of inline with the filter button. Consumer must
         provide a target element somewhere in its template:
             <div data-filter-chips></div>
         If no target exists, chips fall back to rendering inline (after the
         filter button) which preserves the previous behaviour. --}}
    {{-- Only mount the teleport when a [data-filter-chips] target actually
         exists. x-teleport has no null guard — a missing target throws
         "Cannot read properties of null (reading 'appendChild')" during
         Alpine initTree and freezes wire:navigate. hasChipTarget is set in
         init(). See reference_alpine_magic_wire_navigate. --}}
    <template x-if="hasChipTarget">
    <template x-teleport="[data-filter-chips]">
        <template x-if="chips.length > 0">
            <div class="flex flex-wrap items-center gap-2">
                <template x-for="chip in chips" :key="chip.model">
                    <span class="inline-flex items-center gap-x-1.5 py-1 px-2.5 rounded-full text-xs font-medium bg-emerald-50 text-emerald-700 dark:bg-emerald-900/20 dark:text-emerald-400 max-w-md">
                        <span x-text="chip.label" class="truncate" x-bind:title="chip.label"></span>
                        <button type="button"
                            x-on:click="clearDimension(chip.model)"
                            class="shrink-0 size-3.5 inline-flex items-center justify-center rounded-full text-emerald-600 hover:text-emerald-800 hover:bg-emerald-100 focus:outline-none dark:hover:bg-emerald-800 transition-colors">
                            <svg class="size-2.5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M18 6 6 18"></path>
                                <path d="m6 6 12 12"></path>
                            </svg>
                        </button>
                    </span>
                </template>
            </div>
        </template>
    </template>
    </template>
</div>

{{-- The Alpine.data('filterPanel', ...) definition lives in resources/js/app.js
     (registered inside alpine:init), NOT in a @push('script') block here.
     @push scripts are not re-run after wire:navigate, which left filterPanel
     "not defined" (with isDirty/chips errors) on any page reached by
     navigation. See reference_alpine_magic_wire_navigate. --}}
