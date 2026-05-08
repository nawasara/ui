@props(['id', 'items' => [], 'modalName' => null])

{{-- --scope:window tells Preline to teleport the menu element to <body>
     when opened (and back when closed). This sidesteps every container's
     stacking context, overflow:hidden, and sticky-cell paint order issues
     that previously sandwiched the menu behind adjacent rows' kebab
     buttons in tables. With body-scope, the menu lives at the document
     root with z-[100], so nothing in the table can ever clip or overlap
     it. Pair with [--strategy:fixed] (Preline default) to keep it pinned
     during scroll. --}}
<div class="hs-dropdown [--placement:bottom-right] [--scope:window] relative inline-flex">
    {{-- Toggle: vertical dots — title attribute kasih native tooltip
         (browser-rendered, accessible). Pakai native title vs custom
         tooltip component karena dropdown trigger di kondisi closed
         tidak conflict dengan dropdown-menu yang akan render. --}}
    {{-- Hover state uses gray-100 / neutral-600 (one step DARKER than the row's
         hover bg gray-50 / neutral-700/40) so the button stays visually
         distinct when the row is hovered. Otherwise the button blends into
         the sticky cell's row-hover bg and looks "missing". --}}
    <button type="button" title="More actions"
        class="hs-dropdown-toggle size-8 inline-flex justify-center items-center rounded-lg border border-gray-200 bg-white text-gray-700 shadow-sm hover:bg-gray-100 hover:border-gray-300 focus:outline-none focus:bg-gray-100 disabled:opacity-50 dark:bg-neutral-800 dark:border-neutral-700 dark:text-neutral-300 dark:hover:bg-neutral-600 dark:hover:border-neutral-500 dark:focus:bg-neutral-600 transition-colors"
        aria-haspopup="menu" aria-expanded="false" aria-label="More actions">
        <svg class="size-4" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"
            fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <circle cx="12" cy="5" r="1" />
            <circle cx="12" cy="12" r="1" />
            <circle cx="12" cy="19" r="1" />
        </svg>
    </button>

    @php
        // ── Helper: parse "method('a', 'b', 1, true, null)" jadi argumen
        // siap untuk $wire.call(method, ...args).
        //
        // Output: string args yang sudah JS-escaped, prefix dengan nama method
        // sebagai string literal, mis. "'method', 'a', 'b', 1, true, null".
        // Caller pakai dengan cara: $wire.call(<output>).
        //
        // Tipe yang di-recognize:
        //   - 'foo' / "foo"   → string (preserve quotes, escape isi)
        //   - 123 / 1.5       → number (pakai as-is)
        //   - true / false    → boolean
        //   - null            → null literal
        //   - $foo            → BUKAN — caller harus literal string/scalar
        //                        yang ke-bake ke template oleh Blade dulu.
        //
        // Kita pakai regex parsing instead of full JS parser karena input
        // selalu shape sederhana "name(arg, arg, arg)" — tidak perlu handle
        // nested calls atau expression. Defensive fallback ke JSON-encode
        // kalau parse gagal.
        $_parseWireCall = function (string $raw): string {
            $raw = trim($raw);

            // Match "method(...)" pattern. Method name = identifier chars only.
            // \\1 = method name, \\2 = isi parens (bisa kosong, bisa multi-arg)
            if (! preg_match('/^([A-Za-z_][A-Za-z0-9_]*)\s*\(\s*(.*?)\s*\)\s*;?\s*$/s', $raw, $m)) {
                // Fallback: tidak match pattern method(args). Treat sebagai
                // method tanpa args dengan nama raw (rare/unexpected).
                return json_encode($raw);
            }

            $method = $m[1];
            $argsStr = $m[2];

            // Method name as JS string literal — itu argumen pertama $wire.call().
            $parts = ["'".addslashes($method)."'"];

            if ($argsStr !== '') {
                // Split args by top-level commas. Untuk shape sederhana yang
                // kita expect (literal scalar), comma di dalam string literal
                // tidak terjadi karena caller biasanya pass ID atau slug.
                // Kalau ada edge case dengan koma di string, caller harus
                // escape sendiri atau pakai array di payload (bukan use case
                // saat ini).
                foreach (preg_split('/\s*,\s*/', $argsStr) as $arg) {
                    $arg = trim($arg);

                    // Quoted string ('foo' atau "foo") — preserve, normalize ke
                    // JS single-quote dan re-escape isi.
                    if (preg_match("/^'(.*)'$/s", $arg, $sm) || preg_match('/^"(.*)"$/s', $arg, $sm)) {
                        $parts[] = "'".addslashes($sm[1])."'";
                        continue;
                    }

                    // Number / null / boolean → emit literal.
                    if (is_numeric($arg) || in_array($arg, ['null', 'true', 'false'], true)) {
                        $parts[] = $arg;
                        continue;
                    }

                    // Unrecognized shape (mis. variable name) — wrap as string
                    // safely. Better than emitting bareword yang bisa jadi
                    // ReferenceError di JS.
                    $parts[] = "'".addslashes($arg)."'";
                }
            }

            return implode(', ', $parts);
        };
    @endphp

    {{-- Dropdown Menu — z-[100] (well above filter-panel, modals stay on
         top via z-[200]+). Once Preline opens this, --scope:window moves
         it to <body> so no parent stacking context applies. --}}
    <div class="hs-dropdown-menu transition-[opacity,margin] duration hs-dropdown-open:opacity-100 opacity-0 hidden z-[100] mt-2 min-w-40 bg-white shadow-md rounded-lg p-1 dark:bg-neutral-800 dark:border dark:border-neutral-700"
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
                            // ── PERHATIKAN ──
                            // Komponen ini di-teleport ke <body> oleh Preline
                            // (--scope:window) saat dropdown terbuka, sehingga
                            // anchor element keluar dari Livewire component
                            // sub-tree. Konsekuensinya, atribut `wire:click`
                            // TIDAK fire — handler Livewire bind by DOM scope
                            // saat component init, dan teleported element tidak
                            // termasuk lagi.
                            //
                            // Solusi: bridge via Alpine `$wire.call()`. Alpine
                            // listener attached di element-level (atomic),
                            // bukan DOM-tree-level, jadi tetap jalan setelah
                            // teleport. `$wire.call(method, ...args)` adalah
                            // Livewire 3 JS API resmi dan equivalent dengan
                            // wire:click.
                            //
                            // Parser di bawah: caller masih pass syntax
                            // "method('a', 'b')" seperti biasa di field
                            // 'wire:click' (atau 'action'+'param'). Kita
                            // ekstrak nama method + JSON args, lalu render
                            // sebagai $wire.call(method, ...args). Args
                            // di-detect tipe number/string/null/boolean
                            // supaya call signature ke PHP method tetap
                            // benar (number tidak ke-coerce jadi string).
                            $rawClick = $item['wire:click'] ?? "{$item['action']}('{$item['param']}')";
                            $jsCall = '$wire.call('.$_parseWireCall($rawClick).')';
                            $modalDispatch = ! empty($item['modal'])
                                ? "; \$dispatch('open-modal', {id: '{$item['modal']}', loading: true})"
                                : '';

                            // wire:confirm support — Livewire normalnya
                            // intercept klik dengan native confirm() popup
                            // sebelum wire:click dispatch. Karena kita pakai
                            // $wire.call() (bukan wire:click), Livewire tidak
                            // wrap action ini lagi. Implement manual: kalau
                            // user batal, return early sebelum panggil $wire.
                            if (! empty($item['confirm'])) {
                                $msg = addslashes($item['confirm']);
                                $jsCall = "if (! confirm('{$msg}')) return; ".$jsCall;
                            }

                            $attrs['x-on:click'] = $jsCall.$modalDispatch;
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

                    // Note: `wire:confirm` di-handle inline di case 'click'
                    // (manual confirm() guard) karena $wire.call() bypass
                    // Livewire's wire:click interceptor. Untuk type lain
                    // (delete via Livewire.dispatch, href dengan plain
                    // navigation), wire:confirm tidak applicable — kalau
                    // butuh konfirmasi, caller bisa pasang `onclick="return
                    // confirm(...)"` sendiri.
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
