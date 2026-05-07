@props([
    'title' => '',
    'headers' => [],
    'table' => '',
    'footer' => '',
    'useSearch' => false,
    /**
     * Pin the last column to the right edge during horizontal scroll. Useful
     * for action columns that would otherwise be off-screen on wide tables.
     * Adds sticky positioning + a subtle left-edge shadow to convey depth.
     * Background colour follows row state (default / hover / selected) by
     * inheriting from the <tr>; consumers wanting a custom selected style
     * should set the bg on <tr> AND on the last <td> (or use bg-inherit there).
     */
    'stickyLast' => false,
])

<div x-data="{
    searchValue: '',
    search(value) {
        $wire.dispatch('search', { search: value });
        {{-- window.dispatchEvent(new CustomEvent('search', { detail: value })); --}}
    }
}" x-init="$watch('searchValue', value => search(value))">
    {{-- Knowing others is intelligence; knowing yourself is true wisdom. --}}
    <div class="border border-gray-100 rounded-lg shadow-sm p-6 bg-white dark:bg-neutral-800 dark:border-neutral-700">
        <div class="flex flex-col">
            <div class="grid grid-cols-3 gap-4 items-center">
                <div class="col-span-2 p-5 text-lg font-semibold text-left rtl:text-right text-gray-900 dark:text-white">
                    {{ $title }}
                </div>
                <div class="flex flex-col items-end justify-center gap-2">
                    @if (isset($action))
                        <div>{{ $action }}</div>
                    @endif
                    @if ($useSearch)
                        <div class="flex flex-row items-center gap-2 mb-2">
                            <x-nawasara-ui::form.input id="search-table" x-model="searchValue" name="name"
                                label="" placeholder="Search..." required autofocus />
                            <div wire:loading class="flex items-center justify-center">
                                <x-nawasara-ui::loading />
                            </div>
                        </div>
                    @endif
                </div>
            </div>
            @php
                // Sticky-last styling pinned to the right edge during scroll.
                // The last <th> and last <td> in every <tr> get sticky, right-0,
                // bg matching row state, and a subtle left shadow as depth cue.
                //
                // bg behaviour:
                // - default cell bg: white (light) / neutral-800 (dark) so it
                //   covers content scrolling underneath.
                // - hover row → cell bg shifts to row hover colour.
                // - selected row (consumer sets bg on <tr>) → consumer must
                //   also set bg-inherit on the last <td> OR use the matching
                //   bg colour. We document this in the prop docblock.
                // z-index assignments are deliberate to avoid sticky cells
                // clipping popovers spawned from inside them:
                // - sticky body cells: z-0 (just enough to lift above unscrolled
                //   content, but BELOW Preline dropdown menus which use z-50).
                // - sticky header cell: z-10 (above body sticky cells, since
                //   horizontal scroll body might pass under the header row).
                // Dropdown menus inside the action column are z-50 (set in
                // dropdown-menu-action.blade.php) so they always win against
                // sticky cells of adjacent rows.
                $bodySticky = $stickyLast ? '
                    [&>tr>td:last-child]:sticky
                    [&>tr>td:last-child]:right-0
                    [&>tr>td:last-child]:z-0
                    [&>tr>td:last-child]:bg-white
                    dark:[&>tr>td:last-child]:bg-neutral-800
                    [&>tr:hover>td:last-child]:bg-gray-50
                    dark:[&>tr:hover>td:last-child]:bg-neutral-700/40
                    [&>tr>td:last-child]:shadow-[-4px_0_6px_-4px_rgb(0_0_0/0.08)]
                    dark:[&>tr>td:last-child]:shadow-[-4px_0_6px_-4px_rgb(0_0_0/0.4)]
                ' : '';
                $headSticky = $stickyLast ? '
                    sticky right-0 z-10 bg-gray-50 dark:bg-neutral-800
                    shadow-[-4px_0_6px_-4px_rgb(0_0_0/0.08)]
                    dark:shadow-[-4px_0_6px_-4px_rgb(0_0_0/0.4)]
                ' : '';
                $lastIndex = count($headers) - 1;
            @endphp
            <div class="-m-1.5 overflow-x-auto mb-5
                [&::-webkit-scrollbar]:h-1.5
                [&::-webkit-scrollbar-track]:bg-transparent
                [&::-webkit-scrollbar-thumb]:rounded-full
                [&::-webkit-scrollbar-thumb]:bg-gray-300
                dark:[&::-webkit-scrollbar-thumb]:bg-neutral-600">
                <div class="p-1.5 min-w-full inline-block align-middle">
                    {{-- overflow-hidden removed: it clipped the sticky-last cell
                         shadow on the inner edge and could break sticky behaviour
                         in some browser/zoom combinations. The outer overflow-x-auto
                         already provides the scroll container. --}}
                    <table class="min-w-full divide-y divide-gray-200 dark:divide-neutral-700">
                        <thead>
                            <tr>
                                @foreach ($headers as $i => $item)
                                    <th scope="col"
                                        class="px-6 py-3 text-xs text-left font-bold text-black uppercase dark:text-neutral-500 {!! $stickyLast && $i === $lastIndex ? trim($headSticky) : '' !!}">
                                        {!! $item !!}
                                    </th>
                                @endforeach
                            </tr>
                        </thead>
                        {{-- Row hover state: subtle gray background + transition.
                             Pakai child selector [&>tr] supaya berlaku ke semua direct
                             child <tr> tanpa perlu setiap row tambah class hover. --}}
                        <tbody class="divide-y divide-gray-200 dark:divide-neutral-700 [&>tr]:transition-colors [&>tr]:hover:bg-gray-50 dark:[&>tr]:hover:bg-neutral-700/40 {!! trim($bodySticky) !!}">
                            {{ $table ?? '' }}
                        </tbody>
                    </table>
                </div>
            </div>
            {{ $footer ?? '' }}
        </div>
    </div>
</div>
