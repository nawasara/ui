@props(['id', 'items' => [], 'modalName' => null])

<div class="hs-dropdown [--placement:bottom-right] relative inline-flex">
    {{-- Toggle: vertical dots --}}
    <button type="button"
        class="hs-dropdown-toggle size-8 inline-flex justify-center items-center rounded-lg border border-gray-200 bg-white text-gray-700 shadow-sm hover:bg-gray-50 focus:outline-none focus:bg-gray-50 disabled:opacity-50 dark:bg-neutral-800 dark:border-neutral-700 dark:text-neutral-400 dark:hover:bg-neutral-700 dark:focus:bg-neutral-700 transition-colors"
        aria-haspopup="menu" aria-expanded="false">
        <svg class="size-4" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"
            fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <circle cx="12" cy="5" r="1" />
            <circle cx="12" cy="12" r="1" />
            <circle cx="12" cy="19" r="1" />
        </svg>
    </button>

    {{-- Dropdown Menu --}}
    <div class="hs-dropdown-menu transition-[opacity,margin] duration hs-dropdown-open:opacity-100 opacity-0 hidden z-20 mt-2 min-w-40 bg-white shadow-md rounded-lg p-1 dark:bg-neutral-800 dark:border dark:border-neutral-700"
        role="menu" aria-orientation="vertical">

        @foreach ($items as $item)
            @if (empty($item['permission']) || optional(auth()->user())->can($item['permission']))
                @php
                    $isDelete = ($item['type'] ?? '') === 'delete';
                    $baseClass = 'flex items-center gap-x-3 py-2 px-3 rounded-lg text-sm transition-colors '
                        . ($isDelete
                            ? 'text-red-600 hover:bg-red-50 dark:text-red-400 dark:hover:bg-red-900/20'
                            : 'text-gray-800 hover:bg-gray-100 dark:text-neutral-300 dark:hover:bg-neutral-700')
                        . ' focus:outline-none';

                    $icon = $item['icon'] ?? match($item['type'] ?? '') {
                        'delete' => 'lucide-trash-2',
                        'wireModal' => 'lucide-pencil',
                        'href', 'href-navigate' => 'lucide-external-link',
                        default => null,
                    };

                    $attrs = ['class' => $baseClass];

                    switch ($item['type'] ?? '') {
                        case 'click':
                            $attrs['wire:click'] = $item['wire:click'] ?? "{$item['action']}('{$item['param']}')";
                            if (! empty($item['modal'])) {
                                $attrs['x-on:click'] = "\$dispatch('open-modal', {id: '{$item['modal']}', loading: true})";
                            }
                            break;
                        case 'link':
                        case 'href':
                            $attrs['href'] = $item['href'] ?? $item['url'] ?? '#';
                            if (! empty($item['navigate'])) {
                                $attrs['wire:navigate'] = true;
                            }
                            if (! empty($item['target'])) {
                                $attrs['target'] = $item['target'];
                                if ($item['target'] === '_blank') {
                                    $attrs['rel'] = 'noopener noreferrer';
                                }
                            }
                            break;
                        case 'href-navigate':
                            $attrs['href'] = $item['url'];
                            $attrs['wire:navigate'] = true;
                            break;
                        case 'wireModal':
                            $jsonPayload = json_encode($item['payload'], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
                            $attrs['onclick'] = "openLivewireModal({$jsonPayload})";
                            break;
                        case 'delete':
                            $attrs['onclick'] = "Livewire.dispatch('modal-delete', { id: '{$id}', name: '{$item['name']}' })";
                            break;
                        case 'disabled':
                            $attrs['class'] .= ' !text-gray-300 cursor-not-allowed hover:!bg-transparent dark:!text-neutral-600';
                            $attrs['disabled'] = true;
                            break;
                    }

                    // Confirm dialog support
                    if (! empty($item['confirm'])) {
                        $attrs['wire:confirm'] = $item['confirm'];
                    }
                @endphp

                <a {{ $attributes->merge($attrs) }}>
                    @if ($icon)
                        <x-dynamic-component :component="$icon" class="shrink-0 size-4" />
                    @endif
                    {{ $item['label'] }}
                </a>
            @endif
        @endforeach
    </div>
</div>
