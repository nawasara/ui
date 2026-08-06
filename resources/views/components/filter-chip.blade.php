{{--
    Filter chip — penanda filter aktif, bisa diklik untuk melepasnya.

    Ditaruh di slot chips filter-bar supaya pengguna melihat filter apa yang
    sedang berlaku tanpa membuka panelnya.

    Pemakaian:
        <x-nawasara-ui::filter-chip label="Status: Aktif" model="statusFilter" />
--}}
@props([
    'label' => '',
    'model' => null,
])

<span class="inline-flex items-center gap-x-1.5 py-1 px-2.5 rounded-full text-xs font-medium bg-emerald-50 text-emerald-700 dark:bg-emerald-900/20 dark:text-emerald-400">
    {{ $label }}
    <button type="button" wire:click="$set('{{ $model }}', '')"
        class="shrink-0 size-3.5 inline-flex items-center justify-center rounded-full text-emerald-600 hover:text-emerald-800 hover:bg-emerald-100 focus:outline-none dark:hover:bg-emerald-800 transition-colors">
        <svg class="size-2.5" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
            <path d="M18 6 6 18"></path>
            <path d="m6 6 12 12"></path>
        </svg>
    </button>
</span>
