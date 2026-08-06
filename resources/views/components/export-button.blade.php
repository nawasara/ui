{{--
    Export button — tombol unduh dengan pilihan format.

    Pemakaian:
        <x-nawasara-ui::export-button :formats="['csv', 'xlsx']" wire:click="export" />
--}}
@props([
    /**
     * Wire method on the parent Livewire component to call. The method receives
     * the chosen format ('xlsx' | 'csv' | 'json') as its only argument.
     * Example signature on the consumer:
     *     public function export(string $format) { ... }
     */
    'action' => 'export',
    /** Permission gate (Spatie). When null, no permission check. */
    'permission' => null,
    /** Tooltip text shown on hover. */
    'tooltip' => 'Ekspor data',
    /** Button label visible inside the dropdown menu items. */
    'formats' => [
        'xlsx' => ['label' => 'Excel (.xlsx)', 'desc' => 'Format spreadsheet, support styling'],
        'csv' => ['label' => 'CSV (.csv)', 'desc' => 'Plain text, universal'],
        'json' => ['label' => 'JSON (.json)', 'desc' => 'Untuk developer / integrasi'],
    ],
])

{{-- Export-button — dropdown picker for spreadsheet/csv/json export.

     The trigger is icon-only so it slots cleanly into compact toolbars
     alongside other action icons. Click reveals a Preline dropdown of
     export formats; picking one calls $wire.{action}(format) and the
     consumer is responsible for streaming back the file (typically via
     Maatwebsite\Excel\Facades\Excel::download(...) returned from the
     Livewire action).

     The full dataset (not the filtered view) is exported by convention —
     consumers implement that in the action body. The button itself is
     dataset-agnostic. --}}

@php
    $renderButton = $permission === null || auth()->check() && auth()->user()->can($permission);
@endphp

@if ($renderButton)
    {{-- placement="bottom-end": tooltip anchored ke kanan trigger,
         extend ke kiri. Export button hampir selalu di pojok kanan
         toolbar, jadi tooltip "bottom" (centered) sering overflow
         viewport edge dan trigger horizontal scroll. --}}
    <x-nawasara-ui::tooltip :text="$tooltip" placement="bottom-end">
        <div class="hs-dropdown relative inline-flex [--placement:bottom-end]">
            <button type="button"
                aria-label="{{ $tooltip }}"
                class="hs-dropdown-toggle inline-flex items-center justify-center size-10 rounded-lg border border-gray-200 bg-white text-gray-700 hover:bg-gray-50 dark:bg-neutral-800 dark:border-neutral-700 dark:text-neutral-200 dark:hover:bg-neutral-700 shadow-sm transition-colors disabled:opacity-50 disabled:pointer-events-none"
                wire:loading.attr="disabled" wire:target="{{ $action }}">
                <x-lucide-download class="size-4" wire:loading.remove wire:target="{{ $action }}" />
                <x-lucide-loader-circle class="size-4 animate-spin" wire:loading wire:target="{{ $action }}" />
            </button>

            <div class="hs-dropdown-menu transition-[opacity,margin] duration hs-dropdown-open:opacity-100 opacity-0 hidden z-30 min-w-64 bg-white shadow-xl rounded-xl mt-2 dark:bg-neutral-800 border border-gray-200 dark:border-neutral-700 overflow-hidden"
                role="menu" aria-orientation="vertical">
                <div class="px-3 py-2 border-b border-gray-200 dark:border-neutral-700">
                    <span class="text-[11px] font-semibold uppercase tracking-wider text-gray-500 dark:text-neutral-400">
                        Pilih Format Ekspor
                    </span>
                </div>
                <div class="p-1">
                    @foreach ($formats as $key => $meta)
                        <button type="button"
                            wire:click="{{ $action }}('{{ $key }}')"
                            class="w-full text-left flex items-start gap-3 px-3 py-2 rounded-lg hover:bg-gray-50 dark:hover:bg-neutral-700 transition-colors group">
                            <span class="shrink-0 mt-0.5 inline-flex items-center justify-center size-8 rounded-lg bg-gray-100 group-hover:bg-emerald-50 dark:bg-neutral-700 dark:group-hover:bg-emerald-900/30 transition-colors">
                                @switch($key)
                                    @case('xlsx')
                                        <x-lucide-file-spreadsheet class="size-4 text-gray-600 group-hover:text-emerald-700 dark:text-neutral-300 dark:group-hover:text-emerald-400" />
                                    @break
                                    @case('csv')
                                        <x-lucide-file-text class="size-4 text-gray-600 group-hover:text-emerald-700 dark:text-neutral-300 dark:group-hover:text-emerald-400" />
                                    @break
                                    @case('json')
                                        <x-lucide-braces class="size-4 text-gray-600 group-hover:text-emerald-700 dark:text-neutral-300 dark:group-hover:text-emerald-400" />
                                    @break
                                    @default
                                        <x-lucide-download class="size-4 text-gray-600 dark:text-neutral-300" />
                                @endswitch
                            </span>
                            <span class="flex flex-col min-w-0">
                                <span class="text-sm font-medium text-gray-800 dark:text-neutral-200">
                                    {{ $meta['label'] ?? ucfirst($key) }}
                                </span>
                                @if (! empty($meta['desc']))
                                    <span class="text-xs text-gray-500 dark:text-neutral-400">
                                        {{ $meta['desc'] }}
                                    </span>
                                @endif
                            </span>
                        </button>
                    @endforeach
                </div>
            </div>
        </div>
    </x-nawasara-ui::tooltip>
@endif
