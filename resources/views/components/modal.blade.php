@props([
    'title' => null,
    'subtitle' => null,
    'maxWidth' => 'lg',
])

@php
    $maxWidthClass = match($maxWidth) {
        'sm' => 'max-w-sm',
        'md' => 'max-w-md',
        'lg' => 'max-w-lg',
        'xl' => 'max-w-xl',
        '2xl' => 'max-w-2xl',
        '3xl' => 'max-w-3xl',
        default => 'max-w-lg',
    };
@endphp

<div x-data="{ open: @entangle($attributes->wire('model')).live }"
    x-show="open" x-cloak
    x-on:keydown.escape.window="open = false"
    class="fixed inset-0 z-50 overflow-y-auto">

    {{-- Overlay --}}
    <div x-show="open"
        x-transition:enter="ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
        x-transition:leave="ease-in duration-150" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
        class="fixed inset-0 bg-black/50 dark:bg-black/70"
        @click="open = false"></div>

    {{-- Modal --}}
    <div class="flex min-h-full items-center justify-center p-4">
        <div x-show="open"
            x-transition:enter="ease-out duration-200"
            x-transition:enter-start="opacity-0 translate-y-4 sm:scale-95"
            x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
            x-transition:leave="ease-in duration-150"
            x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
            x-transition:leave-end="opacity-0 translate-y-4 sm:scale-95"
            class="relative bg-white dark:bg-neutral-800 rounded-xl shadow-xl w-full {{ $maxWidthClass }} overflow-hidden"
            @click.stop>

            {{-- Header --}}
            @if ($title)
                <div class="flex items-center justify-between px-6 py-4 border-b border-gray-200 dark:border-neutral-700">
                    <div>
                        <h3 class="text-lg font-semibold text-gray-800 dark:text-neutral-200">{{ $title }}</h3>
                        @if ($subtitle)
                            <p class="text-sm text-gray-500 dark:text-neutral-400">{{ $subtitle }}</p>
                        @endif
                    </div>
                    <button @click="open = false" class="size-8 inline-flex items-center justify-center rounded-full text-gray-400 hover:text-gray-600 hover:bg-gray-100 dark:hover:text-neutral-300 dark:hover:bg-neutral-700 transition-colors">
                        <x-lucide-x class="size-4" />
                    </button>
                </div>
            @endif

            {{-- Body --}}
            <div class="px-6 py-4 max-h-[70vh] overflow-y-auto">
                {{ $slot }}
            </div>

            {{-- Footer --}}
            @if (isset($footer))
                <div class="px-6 py-3 border-t border-gray-200 dark:border-neutral-700 flex justify-end gap-3">
                    {{ $footer }}
                </div>
            @endif
        </div>
    </div>
</div>
