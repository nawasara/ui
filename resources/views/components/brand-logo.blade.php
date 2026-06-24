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
        {{-- Default Nawasara logomark — "nawa" (9) dots evenly ringed around an
             N. Ring + dots + N use currentColor via Tailwind classes, so one
             markup serves light and dark (emerald-700 / emerald-500). The 9 dots
             encode "nawa" (nine) in Nawasara; positions are 40° apart starting
             from top (computed, not eyeballed). Keep in sync with the static
             favicon/OG assets in public/ (see brand/nawasara-mark.svg). --}}
        <svg class="{{ $height }} w-auto" viewBox="0 0 48 48" fill="none" xmlns="http://www.w3.org/2000/svg">
            <circle cx="24" cy="24" r="19" fill="none" stroke="currentColor" stroke-width="1.4"
                class="stroke-emerald-700 dark:stroke-emerald-500" opacity="0.4" />
            <g class="fill-emerald-700 dark:fill-emerald-500" fill="currentColor">
                <circle cx="24" cy="5" r="2.5" />
                <circle cx="36.21" cy="9.45" r="2.5" />
                <circle cx="42.71" cy="20.7" r="2.5" />
                <circle cx="40.45" cy="33.5" r="2.5" />
                <circle cx="30.5" cy="41.85" r="2.5" />
                <circle cx="17.5" cy="41.85" r="2.5" />
                <circle cx="7.55" cy="33.5" r="2.5" />
                <circle cx="5.29" cy="20.7" r="2.5" />
                <circle cx="11.79" cy="9.45" r="2.5" />
            </g>
            <path d="M17 31V17L31 31V17" stroke="currentColor" stroke-width="3.6"
                stroke-linecap="round" stroke-linejoin="round" fill="none"
                class="stroke-emerald-700 dark:stroke-emerald-500" />
        </svg>

        @if ($showName)
            <span class="font-semibold text-gray-800 dark:text-neutral-100 text-lg">{{ $appName }}</span>
        @endif
    @endif
</div>
