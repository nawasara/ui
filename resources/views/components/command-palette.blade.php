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

        {{-- Results — one flat list (menu first, then data); a section header is
             rendered whenever the item's group changes. Keyboard nav (active
             index) runs over the flat `results` array. --}}
        <div class="max-h-96 overflow-y-auto py-2" x-ref="list">
            <template x-for="(item, idx) in results" :key="item._key">
                <div>
                    {{-- Section header when the group changes --}}
                    <template x-if="idx === 0 || results[idx - 1].group !== item.group">
                        <div class="px-4 pt-2 pb-1 text-[11px] font-semibold uppercase tracking-wider text-gray-400 dark:text-neutral-500"
                            x-text="item.group"></div>
                    </template>

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
                            {{-- Menu items get a page glyph; data items a database glyph.
                                 lucide here is SVG-component only, so inline SVG renders reliably. --}}
                            <svg x-show="item._type === 'menu'" class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M15 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V7z"/>
                                <path d="M14 2v4a2 2 0 0 0 2 2h4"/>
                            </svg>
                            <svg x-show="item._type === 'data'" class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <ellipse cx="12" cy="5" rx="9" ry="3"/>
                                <path d="M3 5v14a9 3 0 0 0 18 0V5"/>
                                <path d="M3 12a9 3 0 0 0 18 0"/>
                            </svg>
                        </span>
                        <span class="flex-1 min-w-0">
                            <span class="block truncate" x-text="item.label"></span>
                            <span class="block text-xs text-gray-400 dark:text-neutral-500 truncate" x-text="item.sublabel"></span>
                        </span>
                        <x-lucide-corner-down-left class="size-3.5 text-gray-300 dark:text-neutral-600"
                            x-show="active === idx" />
                    </a>
                </div>
            </template>

            {{-- Loading (data fetch in flight) --}}
            <div x-show="loading" class="px-4 py-3 flex items-center gap-2 text-xs text-gray-400 dark:text-neutral-500">
                <svg class="size-4 animate-spin" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M21 12a9 9 0 1 1-6.219-8.56" stroke-linecap="round"/>
                </svg>
                Mencari data…
            </div>

            {{-- Empty state --}}
            <div x-show="results.length === 0 && !loading" class="px-4 py-10 text-center">
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

{{-- The commandPalette Alpine.data + Alpine.store('palette') definitions live
     in resources/js/app.js (registered inside alpine:init), NOT in a
     @push('script') block here. @push scripts are not re-run after
     wire:navigate, which would leave the palette "not defined" on navigated
     pages. The search endpoint is fetched via a relative URL in app.js.
     See reference_alpine_magic_wire_navigate. --}}
