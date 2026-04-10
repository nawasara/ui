@props([
    'label' => '',
    'model' => null,
])

<span class="inline-flex items-center gap-x-1.5 py-1 px-2.5 rounded-full text-xs font-medium bg-green-50 text-green-600 dark:bg-green-900/20 dark:text-green-400">
    {{ $label }}
    <button type="button" wire:click="$set('{{ $model }}', '')"
        class="shrink-0 size-3.5 inline-flex items-center justify-center rounded-full text-green-500 hover:text-green-700 hover:bg-green-100 focus:outline-none dark:hover:bg-green-800 transition-colors">
        <svg class="size-2.5" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
            <path d="M18 6 6 18"></path>
            <path d="m6 6 12 12"></path>
        </svg>
    </button>
</span>
