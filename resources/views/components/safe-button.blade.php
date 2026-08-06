{{--
    Safe button — tombol yang meminta konfirmasi sebelum menjalankan aksinya.

    Untuk aksi merusak yang tidak cukup penting untuk modal penuh tapi terlalu
    berisiko untuk sekali klik.

    Pemakaian:
        <x-nawasara-ui::safe-button color="danger" confirm="Cabut token ini?"
            wire:click="revoke">Cabut</x-nawasara-ui::safe-button>
--}}
@props([
    'action' => null, // aksi dinamis (misal: Alpine.store('form').save())
    'color' => 'primary', // warna tombol
    'size' => 'md', // ukuran tombol
    'full' => false, // apakah full width
    'timeout' => 2000, // durasi disable (ms)
])

<x-nawasara-ui::button :color="$color" :size="$size" :full="$full" x-data="{ disabled: false, loading: false }"
    x-bind:disabled="disabled"
    @click="
        if (disabled) return;
        disabled = true;
        loading = true;

        try {
            if ('{{ $action }}') {
                let result = eval('{{ $action }}');
                if (result instanceof Promise) {
                    result.finally(() => {
                        loading = false;
                        setTimeout(() => disabled = false, {{ $timeout }});
                    });
                } else {
                    setTimeout(() => {
                        loading = false;
                        disabled = false;
                    }, {{ $timeout }});
                }
            }
        } catch (e) {
            console.error('Action error:', e);
            loading = false;
            disabled = false;
        }
    ">
    <template x-if="loading">
        <svg class="w-4 h-4 mr-2 animate-spin text-white" xmlns="http://www.w3.org/2000/svg" fill="none"
            viewBox="0 0 24 24">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4">
            </circle>
            <path class="opacity-75" fill="currentColor"
                d="M4 12a8 8 0 018-8v4l3-3-3-3v4a8 8 0 100 16v-4l-3 3 3 3v-4a8 8 0 01-8-8z"></path>
        </svg>
    </template>
    <span x-show="!loading">{{ $slot }}</span>
</x-nawasara-ui::button>
