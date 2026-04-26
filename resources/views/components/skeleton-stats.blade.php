@props([
    'cards' => 6,        // jumlah card yang diskeleton
    'cols' => 6,         // kolom grid lg
])

<div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-{{ $cols }} gap-3">
    @for ($i = 0; $i < $cards; $i++)
        <div class="p-4 rounded-xl border border-gray-200 dark:border-neutral-700 bg-white dark:bg-neutral-800">
            <div class="flex items-center gap-2 mb-2">
                <x-nawasara-ui::skeleton shape="circle" height="4" />
                <x-nawasara-ui::skeleton width="20" height="3" />
            </div>
            <x-nawasara-ui::skeleton width="16" height="6" />
        </div>
    @endfor
</div>
