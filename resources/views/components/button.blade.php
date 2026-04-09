{{-- resources/views/components/button.blade.php --}}
@props([
    'variant' => 'solid', // solid | outline | ghost | flat | link
    'color' => 'primary', // primary | secondary | success | warning | danger | neutral
    'size' => 'md', // sm | md | lg
    'as' => null, // 'a' | 'button' (auto dari href)
    'href' => null, // jika diisi otomatis render <a>
    'type' => 'button', // button | submit | reset
    'full' => false, // true -> w-full
    'disabled' => false, // disabled state
    'rounded' => 'md', // sm | md | lg | xl | 2xl | full
    'permission' => null,
])

@php
    // cek apakah ada teks di slot (untuk icon-only)
    $hasText = trim((string) $slot) !== '';

    // ukuran
    $sizeClasses =
        [
            'sm' => $hasText ? 'h-9 px-3 text-sm' : 'h-9 w-9 text-sm',
            'md' => $hasText ? 'h-10 px-4 text-sm' : 'h-10 w-10 text-sm',
            'lg' => $hasText ? 'h-12 px-5 text-base' : 'h-12 w-12 text-base',
        ][$size] ?? 'h-10 px-4 text-sm';

    // base class
    $base = implode(' ', [
        'inline-flex items-center justify-center gap-2 select-none',
        'font-medium transition',
        'focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-offset-2',
        'disabled:opacity-50 disabled:pointer-events-none',
        'rounded-' . $rounded,
        $full ? 'w-full' : '',
        'dark:focus-visible:ring-offset-gray-900',
    ]);

    // varian warna (hardcoded literal agar Tailwind bisa detect)
    $colors = [
        'primary' => [
            'solid' => 'bg-blue-600 hover:bg-blue-700 text-white dark:bg-blue-500 dark:hover:bg-blue-600',
            'outline' =>
                'border border-blue-600 text-blue-700 hover:bg-blue-50 dark:border-blue-400 dark:text-blue-300 dark:hover:bg-blue-950',
            'ghost' => 'text-blue-700 hover:bg-blue-50 dark:text-blue-300 dark:hover:bg-blue-950',
            'flat' =>
                'bg-blue-100 text-blue-800 hover:bg-blue-200 dark:bg-blue-950 dark:text-blue-200 dark:hover:bg-blue-900',
            'link' => 'text-blue-600 hover:underline dark:text-blue-400 p-0 h-auto align-baseline',
        ],
        'secondary' => [
            'solid' => 'bg-slate-600 hover:bg-slate-700 text-white dark:bg-slate-500 dark:hover:bg-slate-600',
            'outline' =>
                'border border-slate-600 text-slate-700 hover:bg-slate-50 dark:border-slate-400 dark:text-slate-300 dark:hover:bg-slate-950',
            'ghost' => 'text-slate-700 hover:bg-slate-50 dark:text-slate-300 dark:hover:bg-slate-950',
            'flat' =>
                'bg-slate-100 text-slate-800 hover:bg-slate-200 dark:bg-slate-950 dark:text-slate-200 dark:hover:bg-slate-900',
            'link' => 'text-slate-600 hover:underline dark:text-slate-400 p-0 h-auto align-baseline',
        ],
        'success' => [
            'solid' => 'bg-green-800 hover:bg-green-700 text-white dark:bg-green-800 dark:hover:bg-green-700',
            'outline' =>
                'border border-green-800 text-green-700 hover:bg-green-50 dark:border-green-400 dark:text-green-300 dark:hover:bg-green-950',
            'ghost' => 'text-green-700 hover:bg-green-50 dark:text-green-300 dark:hover:bg-green-950',
            'flat' =>
                'bg-green-100 text-green-800 hover:bg-green-200 dark:bg-green-950 dark:text-green-200 dark:hover:bg-green-900',
            'link' => 'text-green-800 hover:underline dark:text-green-400 p-0 h-auto align-baseline',
        ],
        'warning' => [
            'solid' => 'bg-amber-600 hover:bg-amber-700 text-white dark:bg-amber-500 dark:hover:bg-amber-600',
            'outline' =>
                'border border-amber-600 text-amber-700 hover:bg-amber-50 dark:border-amber-400 dark:text-amber-300 dark:hover:bg-amber-950',
            'ghost' => 'text-amber-700 hover:bg-amber-50 dark:text-amber-300 dark:hover:bg-amber-950',
            'flat' =>
                'bg-amber-100 text-amber-800 hover:bg-amber-200 dark:bg-amber-950 dark:text-amber-200 dark:hover:bg-amber-900',
            'link' => 'text-amber-600 hover:underline dark:text-amber-400 p-0 h-auto align-baseline',
        ],
        'danger' => [
            'solid' => 'bg-rose-600 hover:bg-rose-700 text-white dark:bg-rose-500 dark:hover:bg-rose-600',
            'outline' =>
                'border border-rose-600 text-rose-700 hover:bg-rose-50 dark:border-rose-400 dark:text-rose-300 dark:hover:bg-rose-950',
            'ghost' => 'text-rose-700 hover:bg-rose-50 dark:text-rose-300 dark:hover:bg-rose-950',
            'flat' =>
                'bg-rose-100 text-rose-800 hover:bg-rose-200 dark:bg-rose-950 dark:text-rose-200 dark:hover:bg-rose-900',
            'link' => 'text-rose-600 hover:underline dark:text-rose-400 p-0 h-auto align-baseline',
        ],
        'neutral' => [
            'solid' => 'bg-gray-100 hover:bg-gray-200 text-gray-800 dark:bg-gray-500 dark:hover:bg-gray-600',
            'outline' =>
                'border border-gray-600 text-gray-700 hover:bg-gray-50 dark:border-gray-400 dark:text-gray-300 dark:hover:bg-gray-950',
            'ghost' => 'text-gray-700 hover:bg-gray-50 dark:text-gray-300 dark:hover:bg-gray-950',
            'flat' =>
                'bg-gray-100 text-gray-800 hover:bg-gray-200 dark:bg-gray-950 dark:text-gray-200 dark:hover:bg-gray-900',
            'link' => 'text-gray-600 hover:underline dark:text-gray-400 p-0 h-auto align-baseline',
        ],
    ];

    $variantClasses = $colors[$color][$variant] ?? $colors['primary']['solid'];

    // padding/height final (untuk link -> jangan pakai height tetap)
    $sizing = $variant === 'link' ? '' : $sizeClasses;

    // kelas untuk ikon
    $iconSize =
        [
            'sm' => 'size-4',
            'md' => 'size-5',
            'lg' => 'size-5',
        ][$size] ?? 'size-5';

    // element tag
    $tag = $href ? 'a' : ($as ?: 'button');

    // merge classes eksternal
    $classes = trim($base . ' ' . $variantClasses . ' ' . $sizing);
@endphp

@if (is_null($permission))
    <{{ $tag }} @if ($href) href="{{ $href }}" @endif
        @if ($tag === 'button') type="{{ $type }}" @endif
        @if ($disabled) disabled @endif {{ $attributes->merge(['class' => $classes]) }}>
        @isset($icon)
            <span class="{{ $iconSize }} shrink-0">
                {{ $icon }}
            </span>
        @endisset

        @if ($hasText)
            <span>{{ $slot }}</span>
        @endif

        @isset($trailing)
            <span class="{{ $iconSize }} shrink-0">
                {{ $trailing }}
            </span>
        @endisset
        </{{ $tag }}>
    @else
        @can($permission)
            <{{ $tag }} @if ($href) href="{{ $href }}" @endif
                @if ($tag === 'button') type="{{ $type }}" @endif
                @if ($disabled) disabled @endif {{ $attributes->merge(['class' => $classes]) }}>
                @isset($icon)
                    <span class="{{ $iconSize }} shrink-0">
                        {{ $icon }}
                    </span>
                @endisset

                @if ($hasText)
                    <span>{{ $slot }}</span>
                @endif

                @isset($trailing)
                    <span class="{{ $iconSize }} shrink-0">
                        {{ $trailing }}
                    </span>
                @endisset
                </{{ $tag }}>
            @endcan
@endif
