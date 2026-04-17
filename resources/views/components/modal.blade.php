@props([
    'id' => null,
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

    $hasWireModel = (bool) $attributes->wire('model')->value();
    $modalId = $id ?? uniqid('modal-');
@endphp

{{-- Alpine-first modal (id mode) — no server roundtrip for open/close --}}
@if (! $hasWireModel)
<div x-data="{
        open: false,
        loading: false,
        show()        { this.open = true; this.loading = false; },
        showLoading() { this.open = true; this.loading = true; },
        close()       { this.open = false; this.loading = false; },
    }"
    x-on:keydown.escape.window="if (open) close()"
    x-init="
        $wire.on('modal-open:{{ $modalId }}', () => { show() });
        $wire.on('modal-close:{{ $modalId }}', () => { close() });
        $watch('open', v => document.body.classList.toggle('overflow-hidden', v));
    "
    @open-modal.window="if ($event.detail === '{{ $modalId }}' || $event.detail?.id === '{{ $modalId }}') {
        $event.detail?.loading ? showLoading() : show()
    }"
    @close-modal.window="if ($event.detail === '{{ $modalId }}' || $event.detail?.id === '{{ $modalId }}') close()"
    x-show="open" x-cloak
    class="fixed inset-0 z-[80] overflow-y-auto">
@else
{{-- Legacy wire:model mode (backward compat) --}}
<div x-data="{ open: @entangle($attributes->wire('model')).live, loading: false }"
    x-on:keydown.escape.window="if (open) { open = false }"
    x-init="$watch('open', v => document.body.classList.toggle('overflow-hidden', v))"
    x-show="open" x-cloak
    class="fixed inset-0 z-[80] overflow-y-auto">
@endif

    {{-- Overlay --}}
    <div x-show="open"
        x-transition:enter="ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
        x-transition:leave="ease-in duration-150" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
        class="fixed inset-0 bg-black/60 backdrop-blur-sm dark:bg-black/70"
        @click="{{ $hasWireModel ? 'open = false' : 'close()' }}"></div>

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
                    <button @click="{{ $hasWireModel ? 'open = false' : 'close()' }}" class="size-8 inline-flex items-center justify-center rounded-full text-gray-400 hover:text-gray-600 hover:bg-gray-100 dark:hover:text-neutral-300 dark:hover:bg-neutral-700 transition-colors">
                        <x-lucide-x class="size-4" />
                    </button>
                </div>
            @endif

            {{-- Body --}}
            <div class="px-6 py-4 max-h-[70vh] overflow-y-auto
                [&::-webkit-scrollbar]:w-1.5
                [&::-webkit-scrollbar-track]:bg-transparent
                [&::-webkit-scrollbar-thumb]:rounded-full
                [&::-webkit-scrollbar-thumb]:bg-gray-300
                dark:[&::-webkit-scrollbar-thumb]:bg-neutral-600">

                {{-- Loading skeleton (Alpine-first mode only) --}}
                @if (! $hasWireModel)
                    <div x-show="loading" x-transition class="space-y-4 animate-pulse">
                        <div class="h-4 bg-gray-200 dark:bg-neutral-700 rounded w-3/4"></div>
                        <div class="h-10 bg-gray-200 dark:bg-neutral-700 rounded"></div>
                        <div class="h-4 bg-gray-200 dark:bg-neutral-700 rounded w-1/2"></div>
                        <div class="h-10 bg-gray-200 dark:bg-neutral-700 rounded"></div>
                        <div class="h-4 bg-gray-200 dark:bg-neutral-700 rounded w-2/3"></div>
                        <div class="h-10 bg-gray-200 dark:bg-neutral-700 rounded"></div>
                    </div>
                @endif

                {{-- Actual content --}}
                <div @if (! $hasWireModel) x-show="!loading" x-transition @endif>
                    {{ $slot }}
                </div>
            </div>

            {{-- Footer --}}
            @if (isset($footer))
                <div @if (! $hasWireModel) x-show="!loading" @endif class="px-6 py-3 border-t border-gray-200 dark:border-neutral-700 flex justify-end gap-3">
                    {{ $footer }}
                </div>
            @endif
        </div>
    </div>
</div>
