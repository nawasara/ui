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
     * Optional preset overrides. Default = today / 7d / 30d.
     * Pass an array of [key => label] to customise. The 'custom' option is
     * appended automatically.
     */
    'presets' => [
        'today' => 'Hari ini',
        '7d' => '7 hari',
        '30d' => '30 hari',
    ],
    /**
     * Optional inline label rendered before the segmented pills.
     * Pass null/false to omit. Default 'Periode:'.
     */
    'label' => 'Periode:',
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
    class="inline-flex items-center gap-2 flex-wrap">

    @if ($label)
        <span class="text-xs font-medium text-gray-500 dark:text-neutral-400 shrink-0">
            {{ $label }}
        </span>
    @endif

    {{-- Segmented pill group. Visually one unit (rounded outer + flat inner
         borders) with an emerald background on the active pill. Inspired by
         the reference screenshot (Window: 1h | 24h | 7d). --}}
    <div role="group" aria-label="{{ $label ?? 'Periode' }}"
        class="inline-flex items-center rounded-lg border border-gray-200 dark:border-neutral-700 bg-white dark:bg-neutral-800 shadow-sm overflow-hidden">
        @foreach ($presets as $key => $presetLabel)
            <button type="button"
                x-on:click="select(@js($key))"
                x-bind:class="active === @js($key)
                    ? 'bg-emerald-600 text-white hover:bg-emerald-700'
                    : 'bg-transparent text-gray-700 hover:bg-gray-50 dark:text-neutral-300 dark:hover:bg-neutral-700'"
                x-bind:aria-pressed="active === @js($key)"
                class="h-8 px-3 text-xs font-semibold transition-colors focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:ring-offset-0 border-r border-gray-200 dark:border-neutral-700 last:border-r-0">
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
            class="h-8 px-3 text-xs font-semibold transition-colors focus:outline-none focus:ring-2 focus:ring-emerald-500 inline-flex items-center gap-1.5 border-l border-gray-200 dark:border-neutral-700">
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

@once
    @push('script')
        <script>
            document.addEventListener('alpine:init', () => {
                window.Alpine.data('timeWindow', (config) => ({
                    active: config.active || '7d',
                    from: config.from || '',
                    to: config.to || '',
                    model: config.model,
                    fromModel: config.fromModel,
                    toModel: config.toModel,
                    _fp: null,

                    init() {
                        // Initialise flatpickr in range mode on the hidden input.
                        // Defer the import-side window.flatpickr lookup to runtime
                        // so the component doesn't crash if the asset bundle hasn't
                        // loaded yet (rare, but defensive).
                        const mount = () => {
                            if (! window.flatpickr) {
                                // Asset bundle not ready - retry on next tick.
                                setTimeout(mount, 50);
                                return;
                            }

                            this._fp = window.flatpickr(this.$refs.picker, {
                                mode: 'range',
                                dateFormat: 'Y-m-d',
                                allowInput: false,
                                // Mount calendar to <body> so it escapes
                                // any ancestor stacking context (sticky
                                // tables, modals, overflow:hidden cards).
                                appendTo: document.body,
                                // Anchor positioning to the Custom *button* -
                                // not the hidden storage input - so the
                                // calendar pops out immediately under the
                                // user's click target. Without this, flatpickr
                                // measures from the (visually hidden, 0x0)
                                // <input> and the calendar floats off-screen
                                // (lower-left of viewport in most browsers).
                                positionElement: this.$refs.customBtn,
                                // 'auto' lets flatpickr flip vertically if
                                // there isn't room below the button (typical
                                // when the toolbar sits near the page bottom).
                                position: 'auto',
                                // Sync defaultDate from custom values if present.
                                defaultDate: (this.from && this.to) ? [this.from, this.to] : null,
                                onChange: (dates) => {
                                    if (dates.length !== 2) return;
                                    this.from = this._fmt(dates[0]);
                                    this.to = this._fmt(dates[1]);
                                    this.active = 'custom';
                                    this._commit();
                                },
                                onOpen: () => this._applyDarkTheme(),
                            });
                        };
                        mount();
                    },

                    destroy() {
                        if (this._fp) this._fp.destroy();
                    },

                    /**
                     * YYYY-MM-DD formatter (no time). Server side parses these
                     * via Carbon::parse() so timezone doesn't matter much - the
                     * date is interpreted in app timezone.
                     */
                    _fmt(d) {
                        const y = d.getFullYear();
                        const m = String(d.getMonth() + 1).padStart(2, '0');
                        const day = String(d.getDate()).padStart(2, '0');
                        return `${y}-${m}-${day}`;
                    },

                    /**
                     * Tell flatpickr's portaled calendar to follow our explicit
                     * .dark class on <html>. Without this the picker would always
                     * render with the light theme since it lives outside the
                     * Tailwind dark-mode tree.
                     */
                    _applyDarkTheme() {
                        if (! this._fp || ! this._fp.calendarContainer) return;
                        const isDark = document.documentElement.classList.contains('dark');
                        this._fp.calendarContainer.setAttribute(
                            'data-theme',
                            isDark ? 'dark' : 'light'
                        );
                    },

                    select(key) {
                        if (this.active === key) return;
                        this.active = key;
                        // Clear custom range when switching to a preset; server
                        // ignores from/to anyway when active != 'custom' but
                        // clearing keeps URL/state clean.
                        this.from = '';
                        this.to = '';
                        if (this._fp) this._fp.clear();
                        this._commit();
                    },

                    openCustom() {
                        if (! this._fp) return;
                        this._fp.open();
                    },

                    _commit() {
                        const w = this.$wire;
                        if (! w) return;
                        // Defer the model write so all three values land in one
                        // request. We pin the active key + (custom only) the
                        // from/to bounds; the trait's updatedWindow() hook
                        // computes preset bounds server-side.
                        w.set(this.model, this.active, false);
                        w.set(this.fromModel, this.from, false);
                        w.set(this.toModel, this.to, false);
                        w.$commit();
                    },

                    /**
                     * Dynamic label for the Custom pill — shows the picked
                     * range when active, otherwise just 'Custom'.
                     */
                    get customLabel() {
                        if (this.active === 'custom' && this.from && this.to) {
                            return `${this.from} → ${this.to}`;
                        }
                        return 'Custom';
                    },
                }));
            });
        </script>
    @endpush
@endonce
