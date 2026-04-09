<div x-data="{ open: false }"
    x-on:open-modal-livewire.window="
        open = true;
        $wire.load($event.detail)
    "
    x-on:modal-close-livewire.window="open = false" x-show="open" x-cloak
    class="fixed inset-0 z-65 flex items-center justify-center bg-gray-900/60 dark:bg-black/70 transition-opacity duration-300">

    <div x-show="open" x-transition:enter="ease-out duration-300"
        x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
        x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100" x-transition:leave="ease-in duration-200"
        x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
        x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
        class="w-full max-w-3xl bg-white dark:bg-gray-800 rounded-xl shadow-lg border border-gray-200 dark:border-gray-700 p-6 relative">

        <!-- Header -->
        <div class="flex items-center justify-between border-b border-gray-200 dark:border-gray-700 pb-3 mb-4">
            <h2 class="text-lg font-semibold text-gray-800 dark:text-gray-100" x-text="$wire.title"></h2>
            <button @click="open = false"
                class="inline-flex items-center justify-center w-8 h-8 text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-200 rounded-full hover:bg-gray-100 dark:hover:bg-gray-700 focus:outline-none">
                <svg class="w-4 h-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                    stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>

        <!-- Body -->
        <div class="relative">
            @if ($component)
                <div wire:loading.flex wire:target="load" class="justify-center py-10">
                    <x-nawasara-ui::loading />
                </div>
                <div wire:loading.remove wire:target="load">
                    <livewire:dynamic-component :is="$component" :params="$params" :key="$component" />
                </div>
            @else
                <p class="text-gray-500 dark:text-gray-400">Sedang memuat...</p>
            @endif
        </div>
    </div>
</div>
