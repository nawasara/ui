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
     Multi-select models add/remove independently. --}}
<div
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

        {{-- Cascading panel: left = dimension list, right = value picker.
             Width fixed (left 14rem + right 16rem = 30rem) so layout is
             stable regardless of which dimension is active. --}}
        <div class="hs-dropdown-menu transition-[opacity,margin] duration hs-dropdown-open:opacity-100 opacity-0 hidden z-50 mt-2 bg-white shadow-xl rounded-xl border border-gray-200 dark:bg-neutral-800 dark:border-neutral-700 overflow-hidden"
            role="menu" aria-orientation="vertical" aria-labelledby="{{ $id }}-toggle">

            <div class="flex" style="max-height: 480px; width: 30rem;">
                {{-- Left: dimensions (rendered by filter-group children) --}}
                <div class="w-56 shrink-0 border-r border-gray-200 dark:border-neutral-700 overflow-y-auto py-2 bg-white dark:bg-neutral-800">
                    {{ $slot }}
                </div>

                {{-- Right: value picker — always rendered to keep layout stable;
                     shows placeholder until a dimension is selected/hovered. --}}
                <div class="flex-1 overflow-y-auto py-2 bg-white dark:bg-neutral-800">
                    {{-- Placeholder when no dimension active --}}
                    <div x-show="!activeDim" class="px-4 py-8 text-center">
                        <div class="text-xs text-gray-400 dark:text-neutral-500">
                            Pilih kategori filter di sebelah kiri
                        </div>
                    </div>

                    {{-- Value picker for active dimension --}}
                    <template x-if="activeDim">
                        <div>
                            <div class="px-3 pb-2 text-[11px] font-semibold uppercase tracking-wider text-gray-500 dark:text-neutral-400"
                                 x-text="activeDim?.label"></div>

                            {{-- Search inside value list (shown when more than 7 options) --}}
                            <div x-show="activeDim && activeDim.items && Object.keys(activeDim.items).length > 7"
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
                                <template x-for="(text, value) in filteredOptions(activeDim)" :key="value">
                                    <button type="button"
                                        x-on:click="toggle(activeDim.model, value)"
                                        class="w-full flex items-center gap-x-2.5 py-1.5 px-2.5 rounded-lg text-sm transition-colors"
                                        x-bind:class="isSelected(activeDim.model, value)
                                            ? 'bg-emerald-50 text-emerald-800 dark:bg-emerald-900/30 dark:text-emerald-300 font-medium hover:bg-emerald-100 dark:hover:bg-emerald-900/50'
                                            : 'text-gray-700 dark:text-neutral-300 hover:bg-gray-100 dark:hover:bg-neutral-700'">
                                        <template x-if="isSelected(activeDim.model, value)">
                                            <x-lucide-check-circle-2 class="size-4 shrink-0 text-emerald-600 dark:text-emerald-400" />
                                        </template>
                                        <template x-if="!isSelected(activeDim.model, value)">
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

                <button type="button" x-on:click="applyNow(); HSDropdown.getInstance(`#{{ $id }}-toggle`)?.close()"
                    x-show="isDirty" x-cloak
                    class="inline-flex items-center gap-1.5 py-1.5 px-3 rounded-lg text-xs font-semibold bg-emerald-600 text-white hover:bg-emerald-700 transition-colors">
                    <x-lucide-check class="size-3.5" />
                    Terapkan
                </button>
            </div>
        </div>
    </div>

    {{-- Active filter chips: rendered from Alpine state (instant remove) --}}
    <template x-if="chips.length > 0">
        <div class="flex flex-wrap items-center gap-2 mt-2 basis-full">
            <template x-for="chip in chips" :key="chip.model + ':' + chip.value">
                <span class="inline-flex items-center gap-x-1.5 py-1 px-2.5 rounded-full text-xs font-medium bg-emerald-50 text-emerald-700 dark:bg-emerald-900/20 dark:text-emerald-400">
                    <span x-text="chip.label"></span>
                    <button type="button"
                        x-on:click="removeChip(chip.model, chip.value)"
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
                    activeDim: null,      // currently selected dimension object {model,label,items}
                    hoverDim: null,       // hover preview (not yet committed)
                    optionSearch: '',
                    timerId: null,
                    dimensions_: [],      // populated by registerDimension() from filter-group children

                    init() {
                        // Normalise initial state to arrays
                        for (const [model, value] of Object.entries(config.initial || {})) {
                            this.state[model] = this.normaliseToArray(value);
                            this.lastFlushed[model] = [...this.state[model]];
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
                            this.activeDim = dim;
                        }
                    },

                    selectDimension(dim) {
                        this.activeDim = dim;
                        this.optionSearch = '';
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

                    removeChip(model, value) {
                        const v = String(value);
                        this.state[model] = (this.state[model] || []).filter(x => x !== v);
                        // Chip remove is explicit user action — flush immediately
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
                        return Object.values(this.state).filter(arr => arr && arr.length > 0).length;
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
                        const out = [];
                        for (const [model, values] of Object.entries(this.state)) {
                            if (!values || values.length === 0) continue;
                            const dimLabel = this.dimensions[model] || null;
                            const labelMap = this.labels[model] || {};
                            for (const v of values) {
                                const valLabel = labelMap[v] ?? v;
                                out.push({
                                    model,
                                    value: v,
                                    label: dimLabel ? `${dimLabel}: ${valLabel}` : valLabel,
                                });
                            }
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

                    onPanelClose() {
                        // Force flush on panel close per spec
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
