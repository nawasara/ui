{{--
    Time window — pemilih rentang waktu (hari ini, 7 hari, 30 hari, kustom).

    Mode kustom memakai flatpickr. Dipasangkan dengan trait HasTimeWindow di
    komponen Livewire, yang menyediakan properti $window, $from, dan $to.

    Pemakaian:
        <x-nawasara-ui::time-window />
--}}
@props([
    /**
     * Currently active preset key. One of:
     *   'today'  → from = today 00:00, to = now
     *   '7d'     → from = 7 days ago,  to = now           [default]
     *   '30d'    → from = 30 days ago, to = now
     *   'custom' → from / to are user-selected via flatpickr
     *
     * Bind to the Livewire component's `$window` property.
     */
    'window' => '7d',
    /**
     * Wire model name for the selected preset key. The Livewire side flips
     * the actual `from`/`to` values inside `updatedWindow()` (provided by
     * HasTimeWindow trait). Default 'window'.
     */
    'model' => 'window',
    /**
     * Wire model names for the custom from/to ISO date strings. Only sent
     * when window === 'custom'. Defaults match the trait's property names.
     */
    'fromModel' => 'from',
    'toModel' => 'to',
    /**
     * Custom from/to values, only used when window === 'custom'.
     */
    'from' => null,
    'to' => null,
    /**
     * Optional preset overrides. Default = today / 7d / 30d with short
     * English labels - keeps the segmented control compact so it fits
     * cleanly next to a page title even on narrow viewports. Consumers
     * who want full Indonesian labels can pass:
     *   :presets="['today' => 'Hari ini', '7d' => '7 hari', '30d' => '30 hari']"
     * The 'custom' option is appended automatically.
     */
    'presets' => [
        'today' => 'today',
        '7d' => '7d',
        '30d' => '30d',
    ],
    /**
     * Optional inline label rendered before the segmented pills.
     * Pass null/false to omit. Default null — by default we render an
     * icon-only calendar glyph instead of text (set via $showIcon).
     * Pass an explicit string ('Periode:', 'Range:', etc.) to override.
     */
    'label' => null,
    /**
     * Whether to render a small calendar icon before the pills as a
     * silent visual scope cue. Suppressed when `label` is set (text
     * label takes priority). Default true so consumers get the cue
     * without having to opt in.
     */
    'showIcon' => true,
])

{{-- <x-time-window> — segmented preset selector with optional custom range.

     UX:
     - Pill row: [Hari ini] [7 hari] [30 hari] [Custom ▾]
     - Active pill: emerald background.
     - Tapping 'Custom' opens a flatpickr range picker; selecting a range
       sets from/to and switches active to 'custom'.
     - Tapping a preset pill switches active back to that preset and clears
       the custom range.

     Wiring:
     - Selecting a preset is INSTANT (no debounce) because window changes
       are explicit user intent and shouldn't accumulate. Custom range
       requires both endpoints before flushing.
     - The Livewire side (HasTimeWindow trait) computes the actual
       from/to bounds from the preset key in updatedWindow(); the Blade
       side only owns the *preset key* and *custom range* — never the
       computed bounds.

     Mobile: pills wrap to multiple rows; flatpickr opens as a modal-style
     overlay (its default mobile behaviour). --}}

@php
    // Stable id per instance so multiple time-window components on one page
    // don't clash. Different label hashes = different ids.
    $id = 'tw-'.substr(md5(serialize([$model, $fromModel, $toModel])), 0, 8);
@endphp

<div x-data="timeWindow({
        active: @js($window),
        from: @js($from),
        to: @js($to),
        model: @js($model),
        fromModel: @js($fromModel),
        toModel: @js($toModel),
    })"
    wire:ignore.self
    wire:key="{{ $id }}"
    class="inline-flex items-center gap-2 shrink-0">

    @if ($label)
        <span class="text-xs font-medium text-gray-500 dark:text-neutral-400 shrink-0">
            {{ $label }}
        </span>
    @elseif ($showIcon)
        {{-- Icon-only scope cue. Sized to match the surrounding text-sm
             pill content; muted gray so it reads as ambient hint, not as
             interactive control. aria-hidden because the role="group"
             aria-label below already names the control for screen readers. --}}
        <x-lucide-calendar-days
            class="size-4 text-gray-400 dark:text-neutral-500 shrink-0"
            aria-hidden="true" />
    @endif

    {{-- Segmented pill group. Height (h-9 = 36px) intentionally one notch
         smaller than the toolbar Filter button (h-10) so the time-window
         reads as a tighter, page-level scope chrome rather than another
         filter. Short English labels (today/7d/30d/custom) keep the
         control compact next to a page title even on narrow viewports. --}}
    <div role="group" aria-label="{{ $label ?? 'Periode' }}"
        class="inline-flex items-center rounded-lg border border-gray-200 dark:border-neutral-700 bg-white dark:bg-neutral-800 shadow-sm overflow-hidden">
        @foreach ($presets as $key => $presetLabel)
            <button type="button"
                x-on:click="select(@js($key))"
                x-bind:class="active === @js($key)
                    ? 'bg-emerald-600 text-white hover:bg-emerald-700'
                    : 'bg-transparent text-gray-700 hover:bg-gray-50 dark:text-neutral-300 dark:hover:bg-neutral-700'"
                x-bind:aria-pressed="active === @js($key)"
                class="h-9 px-3 text-xs font-semibold transition-colors focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:ring-offset-0 border-r border-gray-200 dark:border-neutral-700 last:border-r-0">
                {{ $presetLabel }}
            </button>
        @endforeach

        {{-- Custom mode trigger. The flatpickr instance is anchored to
             this button (positionElement) so the calendar pops out
             immediately below it. The hidden <input> below is just where
             flatpickr stores the formatted value; it has no visible role. --}}
        <button type="button" x-ref="customBtn"
            x-on:click="openCustom()"
            x-bind:class="active === 'custom'
                ? 'bg-emerald-600 text-white hover:bg-emerald-700'
                : 'bg-transparent text-gray-700 hover:bg-gray-50 dark:text-neutral-300 dark:hover:bg-neutral-700'"
            x-bind:aria-pressed="active === 'custom'"
            class="h-9 px-3 text-xs font-semibold transition-colors focus:outline-none focus:ring-2 focus:ring-emerald-500 inline-flex items-center gap-1.5 border-l border-gray-200 dark:border-neutral-700">
            <x-lucide-calendar class="size-3.5" />
            <span x-text="customLabel"></span>
        </button>
    </div>

    {{-- Storage input — flatpickr writes the formatted range string here.
         Hidden + sized 0 so it doesn't grab focus or take layout space.
         We DO NOT use display:none / hidden attribute because flatpickr
         skips initialisation on truly-hidden inputs in some configs.
         Positioning is anchored to the Custom button via positionElement
         in the Alpine init below, not to this element. --}}
    <input type="text" x-ref="picker" tabindex="-1" aria-hidden="true"
        class="absolute opacity-0 pointer-events-none w-0 h-0">
</div>

{{-- The Alpine.data('timeWindow', ...) definition lives in resources/js/app.js
     (registered inside alpine:init), NOT in a @push('script') block here.
     @push scripts are only injected on the initial full render and do NOT
     re-run after wire:navigate, so a @push-registered component is "not
     defined" on any page reached by navigation. app.js registers it once,
     globally, surviving every wire:navigate. See reference_alpine_magic_wire_navigate. --}}
