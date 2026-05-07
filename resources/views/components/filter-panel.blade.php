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
    <div class="hs-dropdown relative inline-flex [--auto-close:false]"
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
                                    <input type="text" x-model="optionSearch"
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
</div>

@once
    @push('script')
        <script>
            // Alpine factory registered once globally; instances are created per
            // x-filter-panel component via x-data="filterPanel({...})".
            // State machine:
            //   draft ──(toggle)──> dirty ──(3s timer | apply | close panel)──> flush
            //                                                                     │
            //                                                                     ▼
            //                                                               server sync
            document.addEventListener('alpine:init', () => {
                window.Alpine.data('filterPanel', (config) => ({
                    // Configuration (immutable post-init)
                    multipleModels: config.multipleModels || [],
                    labels: config.labels || {},
                    dimensions: config.dimensions || {},
                    debounceMs: config.debounceMs ?? 3000,

                    // Mutable state — current draft values per model.
                    // Always stored as array for uniform handling; serialised to
                    // scalar on flush when model is single-select.
                    state: {},

                    // Snapshot of last-flushed state, used to compute isDirty
                    lastFlushed: {},

                    // UI state
                    activeDim: null,      // currently selected dimension model key (string)
                    optionSearch: '',
                    timerId: null,        // server-flush debounce timer
                    idleTimerId: null,    // panel idle-close timer
                    dimensions_: [],      // populated by registerDimension() from filter-group children
                    mobileView: 'list',   // 'list' = dimension list, 'values' = value picker (mobile only)

                    init() {
                        console.log('[filter-panel] init() called. config.initial =', JSON.parse(JSON.stringify(config.initial || {})));
                        // Normalise initial state to arrays
                        for (const [model, value] of Object.entries(config.initial || {})) {
                            this.state[model] = this.normaliseToArray(value);
                            this.lastFlushed[model] = [...this.state[model]];
                        }

                        // Wire up imperative listeners. Try via $nextTick first; if the
                        // dropdown DOM is not ready (Livewire/Preline timing), retry on
                        // a short interval until found, with a hard cap.
                        const wire = (root, menu) => {
                            // Bump idle timer on any user interaction inside the menu.
                            const bump = () => this.bumpIdle();
                            menu.addEventListener('click', bump);
                            menu.addEventListener('mouseenter', bump);
                            menu.addEventListener('mouseleave', bump);
                            menu.addEventListener('keydown', bump);
                            let lastMove = 0;
                            menu.addEventListener('mousemove', () => {
                                const now = Date.now();
                                if (now - lastMove > 100) { lastMove = now; bump(); }
                            });

                            // Start (or reset) the idle timer when Preline opens the panel.
                            root.addEventListener('open.hs.dropdown', bump);

                            // Class-mutation observer as a backup signal: when the root
                            // gains class 'open', start the idle timer; when it loses
                            // 'open', clear it. Catches the case where Preline mutates the
                            // class without firing the event for some reason.
                            const observer = new MutationObserver(() => {
                                if (root.classList.contains('open')) {
                                    if (!this.idleTimerId) bump();
                                } else if (this.idleTimerId) {
                                    clearTimeout(this.idleTimerId);
                                    this.idleTimerId = null;
                                }
                            });
                            observer.observe(root, { attributes: true, attributeFilter: ['class'] });
                            this._classObserver = observer;

                            // Outside-click closer (capture phase beats stopPropagation).
                            this._outsideClickHandler = (e) => {
                                if (!root.classList.contains('open')) return;
                                if (root.contains(e.target)) return;
                                this.closePanel();
                            };
                            document.addEventListener('click', this._outsideClickHandler, true);

                            // Esc closer (a11y). Listen on window in capture phase so
                            // we beat any handler that calls stopPropagation (Preline
                            // installs its own keydown handler on the dropdown that
                            // intercepts Esc when focus is on the toggle).
                            this._escHandler = (e) => {
                                if (e.key !== 'Escape' && e.code !== 'Escape' && e.keyCode !== 27) return;
                                if (!root.classList.contains('open')) return;
                                this.closePanel();
                            };
                            window.addEventListener('keydown', this._escHandler, true);
                        };

                        const tryWire = (attempt = 0) => {
                            const root = this.$el.querySelector('.hs-dropdown');
                            const menu = this.$el.querySelector('[data-fp-menu]');
                            if (root && menu) { wire(root, menu); return; }
                            if (attempt > 20) return;
                            setTimeout(() => tryWire(attempt + 1), 50);
                        };
                        this.$nextTick(() => tryWire());
                    },

                    destroy() {
                        // Cleanup global listeners on Alpine teardown (e.g. navigate).
                        if (this._outsideClickHandler) {
                            document.removeEventListener('click', this._outsideClickHandler, true);
                        }
                        if (this._escHandler) {
                            window.removeEventListener('keydown', this._escHandler, true);
                        }
                        if (this._classObserver) {
                            this._classObserver.disconnect();
                        }
                    },

                    normaliseToArray(value) {
                        if (value === null || value === undefined || value === '') return [];
                        return Array.isArray(value) ? value.map(String) : [String(value)];
                    },

                    isMultiple(model) {
                        return this.multipleModels.includes(model);
                    },

                    // Called by x-filter-group children to advertise themselves
                    registerDimension(dim) {
                        // Avoid duplicates on re-render
                        const existing = this.dimensions_.findIndex(d => d.model === dim.model);
                        if (existing >= 0) {
                            this.dimensions_[existing] = dim;
                        } else {
                            this.dimensions_.push(dim);
                        }
                        // Ensure state slot exists
                        if (!(dim.model in this.state)) {
                            this.state[dim.model] = [];
                            this.lastFlushed[dim.model] = [];
                        }
                        // Auto-select first registered dimension so the value picker
                        // shows immediately on panel open instead of an empty state.
                        if (!this.activeDim) {
                            this.activeDim = dim.model;
                        }
                    },

                    selectDimension(dim) {
                        // Store only the model key as activeDim; everything else is
                        // looked up from the dimensions_ registry on read. Avoids
                        // stale object references after Livewire morph (re-rendered
                        // filter-group elements re-call selectDimension with fresh
                        // object literals, but the registry stays canonical).
                        this.activeDim = typeof dim === 'string' ? dim : dim.model;
                        this.optionSearch = '';
                        // Mobile drill-down: opening a dimension switches to the value
                        // picker view. On desktop both columns are visible so this is
                        // a no-op (mobileView is ignored when isDesktop()).
                        this.mobileView = 'values';
                    },

                    activeDimObject() {
                        if (!this.activeDim) return null;
                        return this.dimensions_.find(d => d.model === this.activeDim) || null;
                    },

                    /**
                     * Match Tailwind sm breakpoint (640px). Used by mobile drill-down
                     * x-show conditions so the same panel template serves both layouts.
                     * Read at template eval time, not cached, so window resize works.
                     */
                    isDesktop() {
                        return window.matchMedia('(min-width: 640px)').matches;
                    },

                    isSelected(model, value) {
                        const arr = this.state[model] || [];
                        return arr.includes(String(value));
                    },

                    toggle(model, value) {
                        const v = String(value);
                        const current = this.state[model] || [];
                        let next;

                        if (this.isMultiple(model)) {
                            // Multi: add if absent, remove if present
                            next = current.includes(v)
                                ? current.filter(x => x !== v)
                                : [...current, v];
                        } else {
                            // Single: same value clears, different value replaces
                            next = current.includes(v) ? [] : [v];
                        }

                        this.state[model] = next;
                        this.scheduleFlush();
                    },

                    clearDimension(model) {
                        // Chip × clears all values in the dimension. Explicit user
                        // intent, so flush immediately rather than wait for debounce.
                        this.state[model] = [];
                        this.applyNow();
                    },

                    resetAll() {
                        for (const model of Object.keys(this.state)) {
                            this.state[model] = [];
                        }
                        this.applyNow();
                    },

                    filteredOptions(dim) {
                        if (!dim || !dim.items) return {};
                        const q = this.optionSearch.trim().toLowerCase();
                        if (!q) return dim.items;
                        const out = {};
                        for (const [k, v] of Object.entries(dim.items)) {
                            if (String(v).toLowerCase().includes(q) || String(k).toLowerCase().includes(q)) {
                                out[k] = v;
                            }
                        }
                        return out;
                    },

                    get hasActive() {
                        return Object.values(this.state).some(arr => arr && arr.length > 0);
                    },

                    get activeCount() {
                        // Count active dimensions (not values) — "Filter (3)" means
                        // 3 dimensions have at least one selected value
                        const c = Object.values(this.state).filter(arr => arr && arr.length > 0).length;
                        // DEBUG: log every time the badge re-evaluates
                        console.log('[filter-panel] activeCount =', c, 'state =', JSON.parse(JSON.stringify(this.state)));
                        return c;
                    },

                    get isDirty() {
                        // Compare current state to last-flushed snapshot
                        for (const model of Object.keys(this.state)) {
                            const cur = this.state[model] || [];
                            const last = this.lastFlushed[model] || [];
                            if (cur.length !== last.length) return true;
                            for (let i = 0; i < cur.length; i++) {
                                if (cur[i] !== last[i]) return true;
                            }
                        }
                        return false;
                    },

                    get chips() {
                        // One chip per active dimension (not per value). Multi-select
                        // values within a dimension are joined with ', '. The chip's
                        // close button clears the WHOLE dimension - to remove a single
                        // value the user re-opens the panel. This matches the design
                        // reference and stays compact when many values are selected.
                        const out = [];
                        for (const [model, values] of Object.entries(this.state)) {
                            if (!values || values.length === 0) continue;
                            // Dimension label resolution priority:
                            //   1. Explicit prop override (this.dimensions[model])
                            //   2. Registered group label (from filter-group child)
                            //   3. Model name itself (last-resort fallback)
                            let dimLabel = this.dimensions[model] || null;
                            if (!dimLabel) {
                                const reg = this.dimensions_.find(d => d.model === model);
                                dimLabel = reg?.label || model;
                            }
                            const labelMap = this.labels[model] || {};
                            const valueLabels = values.map(v => labelMap[v] ?? v);
                            out.push({
                                model,
                                label: `${dimLabel}: ${valueLabels.join(', ')}`,
                            });
                        }
                        return out;
                    },

                    scheduleFlush() {
                        if (this.timerId) clearTimeout(this.timerId);
                        this.timerId = setTimeout(() => this.applyNow(), this.debounceMs);
                    },

                    applyNow() {
                        if (this.timerId) {
                            clearTimeout(this.timerId);
                            this.timerId = null;
                        }
                        if (!this.isDirty) return;

                        // Push each model to Livewire. Use defer mode to batch into
                        // single request, then commit.
                        const wire = this.$wire;
                        for (const [model, values] of Object.entries(this.state)) {
                            // Convert array back to scalar for single-select models
                            const payload = this.isMultiple(model)
                                ? values
                                : (values[0] ?? '');
                            wire.set(model, payload, false);
                        }
                        wire.$commit();

                        // Snapshot
                        for (const model of Object.keys(this.state)) {
                            this.lastFlushed[model] = [...this.state[model]];
                        }
                    },

                    // Idle-close: 3s of no interaction inside the panel auto-closes
                    // it. Reset (bumped) on click/hover/keypress events bound to the
                    // menu element. Cleared on panel close so it doesn't fire again.
                    bumpIdle() {
                        if (this.idleTimerId) clearTimeout(this.idleTimerId);
                        this.idleTimerId = setTimeout(() => {
                            this._closeDropdown();
                        }, this.debounceMs);
                    },

                    closePanel() {
                        if (this.idleTimerId) {
                            clearTimeout(this.idleTimerId);
                            this.idleTimerId = null;
                        }
                        this._closeDropdown();
                    },

                    // Internal: close the Preline dropdown. HSDropdown.getInstance()
                    // lookup is done with the ROOT element (the .hs-dropdown div),
                    // not the toggle button id - the latter returns null because
                    // Preline's collection is keyed by root element.
                    _closeDropdown() {
                        const root = this.$el.querySelector('.hs-dropdown');
                        if (!root) return;
                        if (window.HSDropdown) {
                            const inst = window.HSDropdown.getInstance(root);
                            if (inst) {
                                inst.close();
                                return;
                            }
                        }
                        // Fallback: directly remove the 'open' class and hide the menu.
                        // Preline reacts to MutationObserver to clean up positioning.
                        root.classList.remove('open');
                        const menu = root.querySelector('.hs-dropdown-menu');
                        if (menu) {
                            menu.classList.add('hidden');
                            menu.classList.remove('opacity-100');
                        }
                    },

                    onPanelClose() {
                        // Stop the idle timer so it doesn't fire on a closed panel
                        if (this.idleTimerId) {
                            clearTimeout(this.idleTimerId);
                            this.idleTimerId = null;
                        }
                        // Reset mobile drill-down to the dimension list so reopening
                        // starts fresh instead of stranding the user on a value picker.
                        this.mobileView = 'list';
                        // Force flush any pending changes immediately
                        this.applyNow();
                    },

                    forceFlush() {
                        this.applyNow();
                    },
                }));
            });
        </script>
    @endpush
@endonce
