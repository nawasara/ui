{{--
    Tooltip — wrap any element to add a hover-revealed text label.

    Pakai Preline tooltip (`hs-tooltip*` classes) yang sudah tersedia via
    `@vite` bundle. Tidak perlu init manual — Preline auto-binds saat DOM
    ready dan saat livewire navigate (lihat init-preline component).

    Pemakaian dasar:
        <x-nawasara-ui::tooltip text="Refresh data">
            <button wire:click="refresh">
                <x-lucide-refresh-cw class="size-4" />
            </button>
        </x-nawasara-ui::tooltip>

    Placement (top default):
        <x-nawasara-ui::tooltip text="..." placement="bottom">...</x-nawasara-ui::tooltip>

    Disabled state — kalau text kosong, tooltip tidak render (cuma slot
    di-passthrough). Kasih conditional kalau label dynamic.

    Note: Tooltip tidak boleh dipakai di mobile-only flows — hover tidak
    available di touch. Untuk tablet+, tooltip baru muncul setelah hover
    delay ~200ms (Preline default).
--}}
@props([
    'text' => '',
    'placement' => 'top', // top | bottom | left | right
])

@if (trim($text) === '')
    {{-- Empty label = degrade gracefully ke plain passthrough --}}
    {{ $slot }}
@else
@php
    // Preline placement classes — pakai data-placement attribute supaya
    // Popper.js kalkulasi posisi otomatis (avoid clipping di edge layar).
    $placementMap = [
        'top' => '[--placement:top]',
        'bottom' => '[--placement:bottom]',
        'left' => '[--placement:left]',
        'right' => '[--placement:right]',
    ];
    $placementClass = $placementMap[$placement] ?? $placementMap['top'];
@endphp
<div class="hs-tooltip inline-block {{ $placementClass }}">
    <div class="hs-tooltip-toggle inline-block">
        {{ $slot }}
        <span
            class="hs-tooltip-content hs-tooltip-shown:opacity-100 hs-tooltip-shown:visible
                opacity-0 invisible transition-opacity inline-block absolute
                z-50 px-2 py-1 text-xs font-medium text-white whitespace-nowrap
                bg-gray-900 dark:bg-neutral-700 rounded-md shadow-lg
                pointer-events-none"
            role="tooltip">
            {{ $text }}
        </span>
    </div>
</div>
@endif
