@props(['id', 'items' => [], 'modalName' => null])

<div class="hs-dropdown relative inline-flex">
    <!-- Toggle -->
    <button type="button"
        class="hs-dropdown-toggle py-1.5 px-2 inline-flex justify-center items-center gap-2 rounded-lg text-gray-700 align-middle disabled:opacity-50 disabled:pointer-events-none focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-offset-white focus:ring-green-600 transition-all text-sm dark:text-neutral-400 dark:hover:text-white dark:focus:ring-offset-gray-800">
        <svg class="shrink-0 size-4" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"
            fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <circle cx="12" cy="12" r="1" />
            <circle cx="19" cy="12" r="1" />
            <circle cx="5" cy="12" r="1" />
        </svg>
    </button>

    <!-- Dropdown -->
    <div
        class="hs-dropdown-menu transition-[opacity,margin] duration hs-dropdown-open:opacity-100 opacity-0 hidden z-20 mt-2 min-w-44 bg-white shadow-lg rounded-xl p-1 dark:bg-neutral-800">

        <!-- Title -->
        <div class="px-3 pb-2">
            <span class="block text-xs font-semibold uppercase text-gray-400 dark:text-neutral-500">
                Actions
            </span>
        </div>

        <!-- Items -->
        @foreach ($items as $item)
            @if (empty($item['permission']) || optional(auth()->user())->can($item['permission']))
                @php
                    $baseClass =
                        'block cursor-pointer w-full text-left px-3 py-2 text-sm rounded-md transition-colors ' .
                        'hover:bg-gray-100 focus:outline-none focus:bg-gray-100 ' .
                        'dark:hover:bg-neutral-700 dark:focus:bg-neutral-700 dark:text-neutral-300';

                    $attrs = ['class' => $baseClass];

                    switch ($item['type']) {
                        case 'click':
                            $attrs['wire:click'] = "{$item['action']}('{$item['param']}')";
                            break;

                        case 'href':
                            $attrs['href'] = $item['url'];
                            break;

                        case 'href-navigate':
                            $attrs['href'] = $item['url'];
                            $attrs['wire:navigate'] = true;
                            break;

                        case 'wireModal':
                            $jsonPayload = json_encode(
                                $item['payload'],
                                JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE,
                            );
                            $attrs['onclick'] = "openLivewireModal({$jsonPayload})";
                            break;

                        case 'modal':
                            break;

                        case 'delete':
                            $attrs['class'] .=
                                ' text-red-600 hover:bg-red-50 dark:hover:bg-red-600/20 dark:text-red-400 dark:hover:text-red-100';
                            $attrs[
                                'onclick'
                            ] = "Livewire.dispatch('modal-delete', { id: '{$id}', name: '{$item['name']}' })";
                            break;

                        case 'disabled':
                            $attrs['class'] .= ' text-gray-400 cursor-not-allowed bg-transparent hover:bg-transparent';
                            $attrs['disabled'] = true;
                            break;

                        default:
                            $attrs['class'] .= ' text-gray-700 dark:text-neutral-300';
                            break;
                    }
                @endphp

                <a {{ $attributes->merge($attrs) }}>
                    @if (!empty($item['bold']) && $item['bold'])
                        <span class="font-semibold">{{ $item['label'] }}</span>
                    @else
                        {{ $item['label'] }}
                    @endif
                </a>
            @endif
        @endforeach
    </div>
</div>
