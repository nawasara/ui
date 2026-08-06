{{--
    Bulk action bar — baris aksi yang muncul saat ada baris tabel terpilih.

    Pemakaian:
        <x-nawasara-ui::bulk-action-bar :count="count($selected)">
            <x-nawasara-ui::button color="danger" size="sm" wire:click="deleteSelected">
                Hapus terpilih
            </x-nawasara-ui::button>
        </x-nawasara-ui::bulk-action-bar>
--}}
@props([
    /**
     * Server-side count (Livewire). Used in PHP-driven mode where the
     * consumer keeps selection state in a Livewire property.
     */
    'count' => 0,
    /**
     * Alpine-driven count expression. When set, the bar reads its visibility
     * and count from this Alpine expression instead of the server-side $count.
     * Use this for tables that keep selection in Alpine local state for
     * instant feedback (no server roundtrip per checkbox toggle). Example:
     *     xCount="selectedIds.length"
     * The expression must be valid in the parent x-data scope.
     */
    'xCount' => null,
    /**
     * wire:click expression to clear server-side selection. Ignored when
     * xClear is provided (Alpine-driven mode).
     */
    'clearAction' => null,
    /**
     * Alpine x-on:click expression to clear selection. Use with xCount.
     * Example: xClear="selectedIds = []"
     */
    'xClear' => null,
    'label' => 'dipilih',
])

@if ($xCount !== null)
    {{-- Alpine-driven: visibility + count read from parent x-data scope.
         Lets the consumer manage selection state purely in Alpine for zero
         server roundtrips per toggle. --}}
    <div x-show="{{ $xCount }} > 0" x-cloak
        class="mb-3 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 px-4 py-2.5 rounded-lg border border-blue-200 bg-blue-50 dark:bg-blue-900/20 dark:border-blue-800">
        <div class="flex items-center gap-2 text-sm text-blue-800 dark:text-blue-200">
            <x-lucide-check-square class="size-4 shrink-0" />
            <span><strong x-text="{{ $xCount }}"></strong> {{ $label }}</span>
            @if ($xClear)
                <button type="button" x-on:click="{{ $xClear }}" class="ml-2 text-xs underline hover:no-underline opacity-75 hover:opacity-100">
                    Bersihkan
                </button>
            @elseif ($clearAction)
                <button type="button" wire:click="{{ $clearAction }}" class="ml-2 text-xs underline hover:no-underline opacity-75 hover:opacity-100">
                    Bersihkan
                </button>
            @endif
        </div>
        <div class="flex flex-wrap items-center gap-2">
            {{ $slot }}
        </div>
    </div>
@elseif ($count > 0)
    <div class="mb-3 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 px-4 py-2.5 rounded-lg border border-blue-200 bg-blue-50 dark:bg-blue-900/20 dark:border-blue-800">
        <div class="flex items-center gap-2 text-sm text-blue-800 dark:text-blue-200">
            <x-lucide-check-square class="size-4 shrink-0" />
            <span><strong>{{ $count }}</strong> {{ $label }}</span>
            @if ($clearAction)
                <button type="button" wire:click="{{ $clearAction }}" class="ml-2 text-xs underline hover:no-underline opacity-75 hover:opacity-100">
                    Bersihkan
                </button>
            @endif
        </div>
        <div class="flex flex-wrap items-center gap-2">
            {{ $slot }}
        </div>
    </div>
@endif
