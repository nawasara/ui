{{--
    Skeleton tabel — kerangka baris tabel selama data dimuat.

    Pemakaian:
        <div wire:loading.delay>
            <x-nawasara-ui::skeleton-table :rows="5" :cols="4" />
        </div>
--}}
@props([
    'rows' => 5,
    'cols' => 5,
])

<div class="rounded-xl border border-gray-200 dark:border-neutral-700 bg-white dark:bg-neutral-800 overflow-hidden">
    <div class="px-6 py-4 border-b border-gray-200 dark:border-neutral-700">
        <x-nawasara-ui::skeleton width="40" height="5" />
    </div>
    <div class="divide-y divide-gray-100 dark:divide-neutral-700">
        @for ($r = 0; $r < $rows; $r++)
            <div class="px-6 py-3 flex items-center gap-4">
                @for ($c = 0; $c < $cols; $c++)
                    <x-nawasara-ui::skeleton class="flex-1" height="3" />
                @endfor
            </div>
        @endfor
    </div>
</div>
