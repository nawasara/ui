{{--
    Form select dropdown — select dengan pencarian, untuk pilihan yang banyak.

    Pemakaian:
        <x-nawasara-ui::form.select-dropdown label="Pegawai" wire:model="userId"
            :options="$userOptions" searchable />
--}}
@props(['label' => 'label'])
@if ($attributes->has('label'))
    <x-nawasara-ui::form.label :value="$attributes['label']" />
@endif

<div class="hs-dropdown w-full relative inline-flex [--strategy:absolute] [--trigger:click]" {{ $attributes->merge() }}>
    <button id="select-page-dropdown-right-but-left-on-lg" type="button"
        class="inline-flex items-center justify-between w-full px-4 py-3 text-sm font-medium text-gray-800 bg-white border border-gray-200 rounded-lg hs-dropdown-toggle gap-x-2 hover:bg-gray-50 disabled:opacity-50 disabled:pointer-events-none dark:bg-neutral-900 dark:border-neutral-700 dark:text-white dark:hover:bg-neutral-800">
        {{ $defaultValue ?? 'Select an option' }}
        <svg class="hs-dropdown-open:rotate-180 size-4" xmlns="http://www.w3.org/2000/svg" width="24" height="24"
            viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
            stroke-linejoin="round">
            <path d="m6 9 6 6 6-6"></path>
        </svg>
    </button>

    <div class="hs-dropdown-menu border-gray-200 border max-h-96 overflow-y-auto transition-[opacity,margin] w-full duration hs-dropdown-open:opacity-100 opacity-0 hidden min-w-60 bg-white shadow-md rounded-lg p-3 mt-2 z-40 dark:bg-neutral-800 dark:border dark:border-neutral-700 dark:divide-neutral-700 after:absolute after:-bottom-4 after:start-0 after:w-full before:h-4 before:absolute before:-top-4 before:start-0 before:w-full scrollbar-thin scroll-smooth scrollbar-thumb-gray-300"
        aria-labelledby="select-page-dropdown-right-but-left-on-lg">
        {{ $slot }}
    </div>
</div>
