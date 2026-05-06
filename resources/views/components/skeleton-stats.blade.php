@props([
    'cards' => 4,        // jumlah card yang diskeleton (default match hero stats grid)
    'cols' => 4,         // kolom grid lg (1..6 valid)
])

@php
    // Tailwind 4 tidak compile dinamik `lg:grid-cols-{{ $cols }}` — compiler
    // butuh literal class. Branch ke set kelas statis yang sudah compiled.
    // Default cols=4 untuk match hero stats pattern di Phase B pages.
    $lgCols = max(1, min(6, (int) $cols));

    $gridClass = match ($lgCols) {
        2 => 'grid grid-cols-1 sm:grid-cols-2 gap-4',
        3 => 'grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4',
        4 => 'grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4',
        5 => 'grid grid-cols-2 md:grid-cols-3 lg:grid-cols-5 gap-3',
        6 => 'grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-3',
        default => 'grid grid-cols-1 gap-4',
    };
@endphp

<div class="{{ $gridClass }}">
    @for ($i = 0; $i < $cards; $i++)
        <div class="p-5 rounded-xl border border-gray-200 dark:border-neutral-700 bg-white dark:bg-neutral-800">
            <div class="flex items-start gap-3">
                <x-nawasara-ui::skeleton shape="circle" class="size-10 shrink-0" />
                <div class="flex-1 space-y-2">
                    <x-nawasara-ui::skeleton width="20" height="3" />
                    <x-nawasara-ui::skeleton width="16" height="6" />
                </div>
            </div>
        </div>
    @endfor
</div>
