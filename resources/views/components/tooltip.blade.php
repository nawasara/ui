{{--
    Tooltip — wrap any element to add a hover-revealed text label.

    Pure CSS via Tailwind group-hover — tidak depend ke Preline JS atau
    custom variant. Lebih reliable cross-build dan tidak butuh init.

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

    Keyboard accessibility: gunakan group-focus-within selain group-hover,
    supaya tooltip muncul juga saat user tab ke trigger element.

    Note: Tooltip tidak boleh dipakai di mobile-only flows — hover tidak
    available di touch. Untuk tablet+, tooltip muncul instan (tidak ada
    hover delay) tapi opacity transition kasih halus visual.
--}}
@props([
    'text' => '',
    /**
     * Tooltip position relative ke trigger.
     *   top | bottom | left | right     → center-aligned ke trigger
     *   top-end | bottom-end             → right-aligned ke trigger
     *                                      (extend ke kiri — pakai untuk
     *                                      icon button di pojok kanan
     *                                      toolbar supaya tooltip text
     *                                      gak overflow ke luar viewport)
     *   top-start | bottom-start         → left-aligned ke trigger
     *                                      (mirror dari -end, untuk button
     *                                      di pojok kiri)
     *
     * Konvensi naming: mirror Floating UI / Popper API supaya familiar.
     */
    'placement' => 'top',
])

@if (trim($text) === '')
    {{-- Empty label = degrade gracefully ke plain passthrough --}}
    {{ $slot }}
@else
@php
    // Position classes berdasarkan placement. Pakai absolute positioning
    // di parent group, dengan inset auto-calculation.
    //
    // Untuk -end / -start variant: tooltip nempel ke edge trigger
    // (right-0 / left-0) dan extend ke arah berlawanan, supaya tooltip
    // panjang gak overflow viewport saat trigger di pojok layout.
    $positionClass = match ($placement) {
        'bottom' => 'top-full left-1/2 -translate-x-1/2 mt-2',
        'bottom-end' => 'top-full right-0 mt-2',
        'bottom-start' => 'top-full left-0 mt-2',
        'left' => 'right-full top-1/2 -translate-y-1/2 mr-2',
        'right' => 'left-full top-1/2 -translate-y-1/2 ml-2',
        'top-end' => 'bottom-full right-0 mb-2',
        'top-start' => 'bottom-full left-0 mb-2',
        default => 'bottom-full left-1/2 -translate-x-1/2 mb-2', // top
    };
@endphp
<span class="group relative inline-block">
    {{ $slot }}
    <span role="tooltip"
        class="pointer-events-none absolute z-50 px-2 py-1 whitespace-nowrap
            text-xs font-medium text-white bg-gray-900 dark:bg-neutral-700
            rounded-md shadow-lg
            opacity-0 group-hover:opacity-100 group-focus-within:opacity-100
            transition-opacity duration-150
            {{ $positionClass }}">
        {{ $text }}
    </span>
</span>
@endif
