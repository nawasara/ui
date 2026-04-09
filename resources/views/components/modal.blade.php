<div x-data="{ open: false }" x-on:open-modal.window="if($event.detail.id === '{{ $id }}') open = true"
    x-on:close-modal.window="if($event.detail.id === '{{ $id }}') open = false" x-show="open" x-cloak
    class="fixed inset-0 z-65 flex items-center justify-center">

    <!-- Overlay -->
    <div class="fixed inset-0 bg-gray-900/60 dark:bg-black/70 transition-opacity" @click="open = false" x-show="open"
        x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100" x-transition:leave="ease-in duration-200"
        x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"></div>

    <!-- Modal Box -->
    <div x-show="open" x-transition:enter="ease-out duration-300"
        x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
        x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100" x-transition:leave="ease-in duration-200"
        x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
        x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
        class="relative bg-white dark:bg-gray-800 rounded-2xl shadow-lg border border-gray-200 dark:border-gray-700 max-w-lg w-full p-6">

        <!-- Header -->
        @if (isset($title))
            <div class="flex justify-between items-center border-b border-gray-200 dark:border-gray-700 pb-3 mb-4">
                <h2 class="text-lg font-semibold text-gray-800 dark:text-gray-100">{{ $title }}</h2>
                <button @click="open = false"
                    class="inline-flex items-center justify-center w-8 h-8 text-gray-400 hover:text-gray-600 dark:text-gray-400 dark:hover:text-gray-200 rounded-full hover:bg-gray-100 dark:hover:bg-gray-700 focus:outline-none">
                    <svg class="w-4 h-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                        stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        @endif

        <!-- Content -->
        <div class="modal-body text-gray-700 dark:text-gray-300">
            {{ $slot }}
        </div>

        <!-- Footer -->
        @if (isset($footer))
            <div class="mt-6 flex justify-end space-x-2">
                {{ $footer }}
            </div>
        @endif
    </div>
</div>
