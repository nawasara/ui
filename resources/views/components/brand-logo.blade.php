@props([
    'height' => 'h-8',
    'showName' => true,
])

@php
    $logoLight = function_exists('brand') ? brand('logo') : null;
    $logoDark = function_exists('brand') ? brand('logo_dark') : null;
    $appName = function_exists('brand') ? brand('app_name', 'Nawasara') : 'Nawasara';
@endphp

<div class="inline-flex items-center gap-2">
    @if ($logoLight || $logoDark)
        {{-- Custom uploaded logo --}}
        @if ($logoLight)
            <img src="{{ $logoLight }}" alt="{{ $appName }}" class="{{ $height }} w-auto object-contain {{ $logoDark ? 'dark:hidden' : '' }}" />
        @endif
        @if ($logoDark)
            <img src="{{ $logoDark }}" alt="{{ $appName }}" class="{{ $height }} w-auto object-contain hidden dark:inline-block" />
        @endif
    @else
        {{-- Default Nawasara logo (SVG) --}}
        <svg class="{{ $height }} w-auto" viewBox="0 0 32 32" fill="none" xmlns="http://www.w3.org/2000/svg">
            <path d="M1 29.5V16.5C1 9.87258 6.37258 4.5 13 4.5C19.6274 4.5 25 9.87258 25 16.5C25 23.1274 19.6274 28.5 13 28.5H12"
                class="stroke-emerald-700 dark:stroke-emerald-500" stroke="currentColor" stroke-width="2" />
            <path d="M5 29.5V16.66C5 12.1534 8.58172 8.5 13 8.5C17.4183 8.5 21 12.1534 21 16.66C21 21.1666 17.4183 24.82 13 24.82H12"
                class="stroke-emerald-700 dark:stroke-emerald-500" stroke="currentColor" stroke-width="2" />
            <circle cx="13" cy="16.5214" r="5" class="fill-emerald-700 dark:fill-emerald-500" fill="currentColor" />
        </svg>

        @if ($showName)
            <span class="font-semibold text-gray-800 dark:text-neutral-100 text-lg">{{ $appName }}</span>
        @endif
    @endif
</div>
