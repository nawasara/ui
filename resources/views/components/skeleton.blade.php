{{--
    Skeleton — kotak abu-abu berdenyut sebagai pengganti isi yang sedang dimuat.

    Lebih baik daripada spinner untuk isi yang bentuknya sudah diketahui:
    tata letak tidak melompat saat data tiba.

    Pemakaian:
        <x-nawasara-ui::skeleton class="h-4 w-3/4" />
--}}
@props([
    'shape' => 'box',     // box | text | circle | card
    'width' => 'full',    // full | 1/2 | 1/3 | etc tailwind fractions, or arbitrary like '32'
    'height' => null,     // tailwind h- value, e.g. '4', '8', '32'
    'rounded' => 'md',    // sm | md | lg | full
])

@php
    $widthClass = match (true) {
        $width === 'full' => 'w-full',
        str_contains($width, '/') => 'w-'.$width,
        default => 'w-'.$width,
    };

    $defaultHeight = match ($shape) {
        'text' => '4',
        'circle' => '8',
        'card' => '32',
        default => '4',
    };
    $heightClass = 'h-'.($height ?? $defaultHeight);

    $roundedClass = match ($shape) {
        'circle' => 'rounded-full',
        default => 'rounded-'.$rounded,
    };

    $widthForCircle = $shape === 'circle' ? 'w-'.($height ?? $defaultHeight) : $widthClass;
@endphp

<div {{ $attributes->merge(['class' => "$widthForCircle $heightClass $roundedClass bg-gray-200 dark:bg-neutral-700 animate-pulse"]) }}></div>
