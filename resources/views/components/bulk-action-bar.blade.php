@props([
    'count' => 0,
    'clearAction' => null, // wire:click expression to clear selection (e.g. "$set('selected', [])")
    'label' => 'dipilih',
])

@if ($count > 0)
    <div class="mb-3 flex items-center justify-between gap-3 px-4 py-2.5 rounded-lg border border-blue-200 bg-blue-50 dark:bg-blue-900/20 dark:border-blue-800">
        <div class="flex items-center gap-2 text-sm text-blue-800 dark:text-blue-200">
            <x-lucide-check-square class="size-4" />
            <span><strong>{{ $count }}</strong> {{ $label }}</span>
            @if ($clearAction)
                <button type="button" wire:click="{{ $clearAction }}" class="ml-2 text-xs underline hover:no-underline opacity-75 hover:opacity-100">
                    Bersihkan
                </button>
            @endif
        </div>
        <div class="flex items-center gap-2">
            {{ $slot }}
        </div>
    </div>
@endif
