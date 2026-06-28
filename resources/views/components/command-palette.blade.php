@php
    /** Flat, permission-filtered nav items for the ⌘K palette. */
    $paletteItems = app('nawasara.workspaces')->navItems();
@endphp

{{-- Command palette (⌘K / Ctrl+K) — global, self-contained Alpine. Opens via
     the Alpine.store('palette') flag so the topbar button and the keyboard
     shortcut both toggle the same instance. Mounted once in the topbar. --}}
<div
    x-data="commandPalette(@js($paletteItems))"
    x-show="$store.palette.open"
    x-cloak
    @keydown.escape.window="close()"
    @keydown.down.prevent="move(1)"
    @keydown.up.prevent="move(-1)"
    @keydown.enter.prevent="go()"
    class="fixed inset-0 z-[80] flex items-start justify-center"
    role="dialog" aria-modal="true" aria-label="Pencarian navigasi">

    {{-- Overlay --}}
    <div class="absolute inset-0 bg-gray-900/40 dark:bg-black/60 backdrop-blur-sm"
        @click="close()"></div>

    {{-- Panel --}}
    <div x-show="$store.palette.open"
        x-transition:enter="transition ease-out duration-150"
        x-transition:enter-start="opacity-0 -translate-y-2 scale-95"
        x-transition:enter-end="opacity-100 translate-y-0 scale-100"
        class="relative mt-24 w-full max-w-xl mx-4 bg-white dark:bg-neutral-800 rounded-xl shadow-2xl ring-1 ring-black/5 dark:ring-white/10 overflow-hidden">

        {{-- Search input --}}
        <div class="flex items-center gap-3 px-4 border-b border-gray-100 dark:border-neutral-700">
            <x-lucide-search class="size-5 text-gray-400 dark:text-neutral-500 shrink-0" />
            <input x-ref="input" x-model="query" @input="onInput()" type="text"
                placeholder="Cari menu atau halaman..."
                class="flex-1 py-4 bg-transparent !border-0 !ring-0 !outline-none focus:!border-0 focus:!ring-0 focus:!outline-none shadow-none text-base text-gray-800 dark:text-neutral-100 placeholder-gray-400 dark:placeholder-neutral-500"
                autocomplete="off" spellcheck="false" />
            <kbd class="shrink-0 text-[11px] font-medium text-gray-400 dark:text-neutral-500 border border-gray-200 dark:border-neutral-600 rounded px-1.5 py-0.5">Esc</kbd>
        </div>

        {{-- Results --}}
        <div class="max-h-80 overflow-y-auto py-2">
            <template x-for="(item, idx) in results" :key="item.url">
                <a :href="item.url" wire:navigate
                    @click="close()"
                    @mouseenter="active = idx"
                    class="flex items-center gap-3 px-4 py-2.5 text-sm cursor-pointer"
                    :class="active === idx
                        ? 'bg-emerald-50 text-emerald-800 dark:bg-emerald-900/30 dark:text-emerald-300'
                        : 'text-gray-700 hover:bg-gray-50 dark:text-neutral-300 dark:hover:bg-neutral-700/50'">
                    <span class="flex items-center justify-center size-8 rounded-lg shrink-0 transition-colors"
                        :class="active === idx
                            ? 'bg-emerald-100 text-emerald-600 dark:bg-emerald-900/40 dark:text-emerald-400'
                            : 'bg-gray-100 text-gray-400 dark:bg-neutral-700 dark:text-neutral-500'">
                        {{-- Generic page glyph — lucide here is SVG-component only, so
                             a single inline SVG renders reliably for every item. --}}
                        <svg class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                            stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M15 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V7z"/>
                            <path d="M14 2v4a2 2 0 0 0 2 2h4"/>
                        </svg>
                    </span>
                    <span class="flex-1 min-w-0">
                        <span class="block truncate" x-text="item.label"></span>
                        <span class="block text-xs text-gray-400 dark:text-neutral-500 truncate" x-text="item.group"></span>
                    </span>
                    <x-lucide-corner-down-left class="size-3.5 text-gray-300 dark:text-neutral-600"
                        x-show="active === idx" />
                </a>
            </template>

            {{-- Empty state --}}
            <div x-show="results.length === 0" class="px-4 py-10 text-center">
                <x-lucide-search-x class="size-6 mx-auto text-gray-300 dark:text-neutral-600 mb-2" />
                <p class="text-sm text-gray-500 dark:text-neutral-400">
                    Tidak ada hasil untuk "<span x-text="query"></span>"
                </p>
            </div>
        </div>

        {{-- Footer hint --}}
        <div class="flex items-center justify-between gap-4 px-4 py-2 border-t border-gray-100 dark:border-neutral-700 bg-gray-50 dark:bg-neutral-800/60 text-[11px] text-gray-400 dark:text-neutral-500">
            <span class="flex items-center gap-1.5">
                <kbd class="border border-gray-200 dark:border-neutral-600 rounded px-1">↑</kbd>
                <kbd class="border border-gray-200 dark:border-neutral-600 rounded px-1">↓</kbd>
                pilih
            </span>
            <span class="flex items-center gap-1.5">
                <kbd class="border border-gray-200 dark:border-neutral-600 rounded px-1">↵</kbd>
                buka
            </span>
        </div>
    </div>
</div>

@once
    @push('script')
        <script>
            // Registered inside alpine:init (NOT top-level) so wire:navigate
            // re-evaluation never re-registers → no "duplicate" throw / SPA
            // freeze. See reference_alpine_magic_wire_navigate.
            document.addEventListener('alpine:init', () => {
                // Shared open flag — topbar button + ⌘K shortcut both flip this.
                window.Alpine.store('palette', { open: false });

                window.Alpine.data('commandPalette', (items) => ({
                    items: items || [],
                    query: '',
                    results: [],
                    active: 0,

                    init() {
                        this.results = this.rank('');

                        // Global ⌘K / Ctrl+K. Capture so we win before the
                        // browser's own find-bar on some platforms.
                        this._key = (e) => {
                            if ((e.metaKey || e.ctrlKey) && (e.key === 'k' || e.key === 'K')) {
                                e.preventDefault();
                                this.openPalette();
                            }
                        };
                        window.addEventListener('keydown', this._key);

                        // When the store flips open (via button or shortcut),
                        // focus the input + reset.
                        this.$watch('$store.palette.open', (open) => {
                            if (open) {
                                this.query = '';
                                this.active = 0;
                                this.results = this.rank('');
                                this.$nextTick(() => this.$refs.input && this.$refs.input.focus());
                            }
                        });
                    },

                    destroy() {
                        // Clean up the global listener on Alpine teardown so it
                        // doesn't accumulate across wire:navigate.
                        if (this._key) window.removeEventListener('keydown', this._key);
                    },

                    openPalette() { this.$store.palette.open = true; },
                    close() { this.$store.palette.open = false; },

                    onInput() {
                        this.results = this.rank(this.query);
                        this.active = 0;
                    },

                    /**
                     * Rank items against the query. Prefix matches on the label
                     * rank highest, then label substring, then group substring.
                     * Empty query returns the first N items as-is. Cap at 10.
                     */
                    rank(q) {
                        q = (q || '').trim().toLowerCase();
                        if (q === '') return this.items.slice(0, 10);

                        const scored = [];
                        for (const item of this.items) {
                            const label = (item.label || '').toLowerCase();
                            const group = (item.group || '').toLowerCase();
                            let score = 0;
                            if (label.startsWith(q)) score = 100;
                            else if (label.includes(q)) score = 60;
                            else if (group.includes(q)) score = 30;
                            else if ((group + ' ' + label).includes(q)) score = 20;
                            if (score > 0) scored.push({ item, score });
                        }
                        scored.sort((a, b) => b.score - a.score);
                        return scored.slice(0, 10).map((s) => s.item);
                    },

                    move(delta) {
                        if (this.results.length === 0) return;
                        this.active = (this.active + delta + this.results.length) % this.results.length;
                        // keep active row in view
                        this.$nextTick(() => {
                            const el = this.$el.querySelectorAll('[wire\\:navigate]')[this.active];
                            if (el) el.scrollIntoView({ block: 'nearest' });
                        });
                    },

                    go() {
                        const item = this.results[this.active];
                        if (!item) return;
                        this.close();
                        // SPA navigation — no full reload.
                        if (window.Livewire && window.Livewire.navigate) {
                            window.Livewire.navigate(item.url);
                        } else {
                            window.location.href = item.url;
                        }
                    },
                }));
            });
        </script>
    @endpush
@endonce
