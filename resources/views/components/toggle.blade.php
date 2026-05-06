{{--
    Toggle (switch) — boolean on/off control. Pakai untuk feature flag, mode
    enable/disable yang bersifat instan (tidak butuh konfirmasi). Untuk action
    destruktif yang butuh konfirmasi, lebih cocok pakai button + wire:confirm.

    Pemakaian (Livewire):
        <x-nawasara-ui::toggle
            :active="$detailSecurityLevel === 'under_attack'"
            wire:click="setSecurityLevel('{{ $detailSecurityLevel === 'under_attack' ? 'medium' : 'under_attack' }}')"
            wire:confirm="Aktifkan Under Attack Mode?"
            color="danger" />

    Pemakaian (Alpine):
        <x-nawasara-ui::toggle x-on:click="enabled = !enabled" :active="$enabled" />

    Color tokens:
        primary | success | warning | danger
--}}
@props([
    'active' => false,
    'color' => 'primary',
    'size' => 'md',          // sm | md | lg
    'disabled' => false,
    'label' => null,         // optional inline label di kanan toggle
    'description' => null,   // optional sub-label
])

@php
    $colorTokens = [
        'primary' => 'bg-emerald-700',
        'success' => 'bg-green-600',
        'warning' => 'bg-amber-600',
        'danger' => 'bg-rose-600',
    ];
    $activeBg = $colorTokens[$color] ?? $colorTokens['primary'];

    $dimensions = match ($size) {
        'sm' => ['track' => 'h-5 w-9', 'thumb' => 'size-4', 'translate' => 'translate-x-4'],
        'lg' => ['track' => 'h-7 w-13', 'thumb' => 'size-6', 'translate' => 'translate-x-6'],
        default => ['track' => 'h-6 w-11', 'thumb' => 'size-5', 'translate' => 'translate-x-5'],
    };

    $trackClass = trim(implode(' ', [
        'relative inline-flex shrink-0 cursor-pointer rounded-full border-2 border-transparent',
        'transition-colors duration-200 ease-in-out',
        'focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-offset-2 focus-visible:ring-emerald-600',
        'dark:focus-visible:ring-offset-neutral-900',
        $dimensions['track'],
        $active ? $activeBg : 'bg-gray-200 dark:bg-neutral-600',
        $disabled ? 'opacity-50 cursor-not-allowed' : '',
    ]));

    $thumbClass = trim(implode(' ', [
        'pointer-events-none inline-block rounded-full bg-white shadow',
        'transform transition duration-200 ease-in-out',
        $dimensions['thumb'],
        $active ? $dimensions['translate'] : 'translate-x-0',
    ]));
@endphp

@if ($label || $description)
    <label class="flex items-start gap-3 {{ $disabled ? 'opacity-60' : '' }}">
        <button type="button"
            role="switch"
            aria-checked="{{ $active ? 'true' : 'false' }}"
            @if ($disabled) disabled @endif
            {{ $attributes->merge(['class' => $trackClass]) }}>
            <span class="{{ $thumbClass }}"></span>
        </button>
        <div class="flex-1 min-w-0 -mt-0.5">
            @if ($label)
                <p class="text-sm font-medium text-gray-800 dark:text-neutral-200">{{ $label }}</p>
            @endif
            @if ($description)
                <p class="text-xs text-gray-500 dark:text-neutral-400 mt-0.5">{{ $description }}</p>
            @endif
        </div>
    </label>
@else
    <button type="button"
        role="switch"
        aria-checked="{{ $active ? 'true' : 'false' }}"
        @if ($disabled) disabled @endif
        {{ $attributes->merge(['class' => $trackClass]) }}>
        <span class="{{ $thumbClass }}"></span>
    </button>
@endif
