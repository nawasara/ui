{{--
    Search input — kotak pencarian yang terikat ke properti Livewire.

    Mengikat lewat wire:model.live.debounce, jadi pencarian berjalan setelah
    pengetikan berhenti sejenak alih-alih pada tiap ketukan. Itu menjaga jumlah
    permintaan tetap wajar tanpa membuat pengguna menunggu sampai fokus lepas.

    Pemakaian:
        <x-nawasara-ui::search-input model="search" placeholder="Cari nama…" />
        <x-nawasara-ui::search-input model="q" :debounce="500" variant="inline" />
--}}
@props([
    /**
     * Wire model name on the parent Livewire component. The input binds via
     * wire:model.live.debounce.{debounce}ms so typing fires search after a
     * short pause (300ms default) — keeps query traffic bounded without
     * making the user wait for blur.
     */
    'model' => 'search',
    /** Placeholder text shown when the field is empty. */
    'placeholder' => 'Cari...',
    /** Debounce window in milliseconds. 300ms is the toolbar default. */
    'debounce' => 300,
    /**
     * Layout mode:
     *   'fill' (default) — expands to fill its flex slot (md:flex-1).
     *                       Use when the input sits in a toolbar row alongside
     *                       a filter-panel and action buttons.
     *   'block'          — full-width, no flex behaviour. Use when the input
     *                       owns its own row (e.g. server-switcher pattern).
     */
    'layout' => 'fill',
])

{{-- <x-search-input> — single-source-of-truth for the toolbar search field.

     Replaces 19+ inline copies of the same icon-prefix input markup that
     drifted slightly across pages (different placeholders, occasionally
     mismatched dark colors, inconsistent debounce). Centralising here
     guarantees the field reads the same everywhere AND a future style
     tweak (focus ring colour, height bump) lands in one file.

     Usage:
       <x-nawasara-ui::search-input model="search" placeholder="Cari nama..." />

     Custom debounce:
       <x-nawasara-ui::search-input model="query" debounce="500" />

     Standalone full-row layout:
       <x-nawasara-ui::search-input model="search" layout="block" /> --}}

@php
    // Width strategy: 'fill' joins a flex toolbar; 'block' is its own row.
    // min-w-0 is critical in fill mode so flex can actually shrink the
    // input below its content width when neighbours are large.
    $widthClass = $layout === 'block'
        ? 'w-full'
        : 'w-full md:flex-1 md:min-w-0';
@endphp

<div {{ $attributes->merge(['class' => 'relative '.$widthClass]) }}>
    <div class="absolute inset-y-0 start-0 flex items-center pointer-events-none ps-3.5">
        <x-lucide-search class="shrink-0 size-4 text-gray-400 dark:text-neutral-500" />
    </div>
    <input type="text"
        wire:model.live.debounce.{{ $debounce }}ms="{{ $model }}"
        placeholder="{{ $placeholder }}"
        class="h-10 ps-10 pe-4 block w-full border border-gray-200 rounded-lg text-sm focus:border-emerald-600 focus:ring-emerald-600 dark:bg-neutral-900 dark:border-neutral-700 dark:text-neutral-200 dark:placeholder-neutral-500 dark:focus:ring-neutral-600" />
</div>
