{{--
    Dropdown menu aksi — menu bertitik-tiga untuk aksi baris yang jumlahnya
    banyak.

    Item memakai Livewire.dispatch di balik layar, bukan wire:click langsung,
    karena Preline memindahkan menu ke luar komponen saat dibuka dan itu
    memutus ikatan wire:click.

    Pemakaian:
        <x-nawasara-ui::dropdown-menu-action :items="[
            ['type' => 'click', 'label' => 'Ubah', 'wire:click' => 'edit(1)', 'icon' => 'lucide-pencil'],
            ['type' => 'click', 'label' => 'Hapus', 'wire:click' => 'destroy(1)', 'icon' => 'lucide-trash-2',
             'confirm' => 'Hapus data ini?'],
        ]" />

    ⚠️ JANGAN menaruh kutip ganda lurus di dalam teks item, mis. pada 'confirm'.
    Array ini ditulis di dalam atribut yang juga dibatasi kutip ganda, jadi satu
    kutip ganda menutup atribut itu lebih awal dan SISA ARRAY TUMPAH KE HALAMAN
    sebagai teks PHP mentah, lengkap dengan nama permission. Tidak ada galat,
    hanya tabel yang terlihat rusak.

    Pakai kutip tipografis, mis. 'Hapus ‘Nama‘ secara permanen?'

    Kalau memang butuh kutip ganda, bangun array di blok PHP lebih dulu lalu
    lewatkan variabelnya lewat :items, seperti nawasara-proxmox.

    Kena di nawasara-emergency sampai 17 September 2026.
--}}
@props(['id', 'items' => [], 'modalName' => null])

@php
    // ── Audit keamanan 15 Oktober 2026 (T9) ──
    // Semua JavaScript di komponen ini dibangun dari DATA (nama user dari
    // Keycloak, topik Zoom, nama OPD, nama instance Vault). Dulu nilai itu
    // disambung ke string JS dengan kutip tunggal, mis. name: '{$item['name']}'.
    // Browser men-decode entitas HTML di atribut onclick SEBELUM menjalankannya,
    // jadi e() dari Blade tidak menolong: satu kutip tunggal di nama sudah
    // keluar dari string dan menjalankan skrip di sesi admin.
    //
    // Aturannya sekarang: setiap nilai yang masuk ke JS lewat $_js(), yaitu
    // json_encode dengan JSON_HEX_*. Hasilnya literal JS yang sah dan tidak
    // memuat ' " < > & mentah, sehingga aman di dalam atribut maupun string.
    $_js = static fn ($value): string => json_encode(
        $value,
        JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP | JSON_UNESCAPED_SLASHES | JSON_PARTIAL_OUTPUT_ON_ERROR,
    );

    // Buka escape string JS/JSON (\', \", \\, \n, \uXXXX termasuk pasangan
    // surrogate) supaya argumen yang ditulis pemanggil dengan Js::from()
    // kembali menjadi nilai aslinya sebelum di-encode ulang.
    $_jsUnescape = static function (string $s): string {
        $s = preg_replace_callback(
            '/\\\\u(d[89ab][0-9a-f]{2})\\\\u(d[c-f][0-9a-f]{2})/i',
            static fn ($m) => mb_chr(0x10000 + ((hexdec($m[1]) - 0xD800) << 10) + (hexdec($m[2]) - 0xDC00), 'UTF-8'),
            $s,
        );

        return preg_replace_callback(
            '/\\\\(u[0-9a-fA-F]{4}|x[0-9a-fA-F]{2}|.)/s',
            static function ($m) {
                $c = $m[1];
                if ($c[0] === 'u' && strlen($c) === 5) {
                    return (string) mb_chr(hexdec(substr($c, 1)), 'UTF-8');
                }
                if ($c[0] === 'x' && strlen($c) === 3) {
                    return chr(hexdec(substr($c, 1)));
                }

                return match ($c) {
                    'n' => "\n",
                    'r' => "\r",
                    't' => "\t",
                    default => $c,
                };
            },
            $s,
        );
    };

    // ── Helper: parse "method('a', 'b', 1, true, null)" ke daftar argumen JS
    // siap untuk Livewire.find(id).call(method, ...args).
    //
    // Output: argumen JS dipisah koma dengan nama method sebagai elemen
    // pertama, mis. "\"method\", \"a\", \"b\", 1, true, null". Pemanggil
    // memakainya: __nawasaraDdCall(this, <output>).
    //
    // Tipe yang dikenali:
    //   - 'foo' / "foo"   → string (escape dibuka, lalu di-encode ulang $_js)
    //   - 123 / 1.5       → angka
    //   - true / false    → boolean
    //   - null            → null
    //   - bareword        → dijadikan string (defensif)
    //
    // Argumen dipecah dengan tokenizer yang memahami string berkutip, jadi
    // koma di dalam string (mis. 'Dinas A, B') tidak lagi memecah argumen.
    $_parseWireCall = function (string $raw) use ($_js, $_jsUnescape): string {
        $raw = trim($raw);

        if (! preg_match('/^([A-Za-z_][A-Za-z0-9_]*)\s*\(\s*(.*?)\s*\)\s*;?\s*$/s', $raw, $m)) {
            return $_js($raw);
        }

        $parts = [$_js($m[1])];
        $argsStr = $m[2];

        if ($argsStr !== '') {
            preg_match_all(
                '/\G\s*(\'(?:[^\'\\\\]|\\\\.)*\'|"(?:[^"\\\\]|\\\\.)*"|[^,]*?)\s*(?:,|$)/s',
                $argsStr,
                $all,
            );

            foreach ($all[1] as $i => $arg) {
                // preg_match_all dengan \G menghasilkan satu kecocokan kosong
                // di ujung string; buang yang itu saja.
                if ($arg === '' && $i === count($all[1]) - 1 && $i > 0) {
                    continue;
                }

                $arg = trim($arg);

                if (strlen($arg) >= 2 && ($arg[0] === "'" || $arg[0] === '"') && $arg[-1] === $arg[0]) {
                    $parts[] = $_js($_jsUnescape(substr($arg, 1, -1)));
                    continue;
                }

                if (is_numeric($arg)) {
                    $parts[] = $_js($arg + 0);
                    continue;
                }

                if (in_array($arg, ['null', 'true', 'false'], true)) {
                    $parts[] = $arg;
                    continue;
                }

                $parts[] = $_js($arg);
            }
        }

        return implode(', ', $parts);
    };

    // href dari data: tolak skema yang menjalankan skrip.
    $_safeHref = static function ($href): string {
        $href = (string) $href;
        $normalized = strtolower(preg_replace('/[\x00-\x20]+/', '', $href));

        return preg_match('/^(javascript|data|vbscript):/', $normalized) ? '#' : $href;
    };
@endphp

{{-- ── KENAPA INI BUKAN wire:click ──
     Preline `--scope:window` (lihat class di outer div) teleport menu
     element ke <body> saat dropdown terbuka. Akibatnya:
       1. wire:click di anchor menu TIDAK fire — Livewire bind handler
          by DOM subtree, dan teleported element keluar dari subtree.
       2. Alpine $wire reference juga gagal resolve setelah teleport
          karena Alpine scope chain putus saat element dipindah ke body.
       3. Alpine x-data variabel di ancestor wrapper juga tidak survive
          teleport karena anchor lose scope chain ke wrapper.

     Solusi: pakai global `Livewire.find(id).call()` dengan component ID
     yang DI-RESOLVE & CACHED di onmouseenter trigger button (sebelum
     teleport). ID disimpan sebagai data-attribute di MENU CONTAINER itu
     sendiri (yang nanti ke-teleport bareng anchor — anchor di-body
     tetap punya akses parent menu container untuk baca attribute).

     Helper `__nawasaraDdCall` lookup component ID dari `data-livewire-id`
     di nearest `.hs-dropdown-menu` ancestor (yang TELEPORTED bersama
     anchor) atau fallback ke trigger walk-up.
     ─────────────────────────────────────────────────── --}}

@once
    {{-- Inject helper sekali per page render. @once memastikan tidak
         duplicate kalau ada banyak instance dropdown di halaman. --}}
    <script>
        (function () {
            if (window.__nawasaraDdCall) return;

            window.__nawasaraDdCall = function (anchorEl, method, ...args) {
                if (! anchorEl) return;

                // Strategy 1: anchor's nearest .hs-dropdown-menu ancestor
                // (teleported bersama anchor) carries data-livewire-id
                // yang di-set saat trigger di-hover/click.
                const menu = anchorEl.closest('.hs-dropdown-menu');
                let wireId = menu ? menu.getAttribute('data-livewire-id') : null;

                // Strategy 2 (fallback): walk up dari anchor ke nearest
                // [wire:id]. Kalau tidak teleported (mis. mobile responsive
                // mode), anchor masih di subtree component asli.
                if (! wireId) {
                    const root = anchorEl.closest('[wire\\:id]');
                    if (root) wireId = root.getAttribute('wire:id');
                }

                if (! wireId) {
                    console.warn('[dropdown] cannot resolve Livewire component ID for action', method, args);
                    return;
                }

                const component = window.Livewire.find(wireId);
                if (! component) {
                    console.warn('[dropdown] Livewire.find('+wireId+') returned null');
                    return;
                }
                return component.call(method, ...args);
            };

            // Hook trigger button click/hover untuk capture component ID
            // SEBELUM teleport, simpan di adjacent menu element. Pakai
            // event delegation di body supaya aktif untuk dropdown yang
            // baru di-render via Livewire morph juga.
            //
            // Defensive: e.target bisa bukan Element kalau event fire di
            // text node / window / document context. closest() hanya ada
            // di Element prototype — guard supaya tidak throw TypeError
            // yang spam console untuk setiap mouseenter di seluruh page.
            const captureId = function (e) {
                const target = e.target;
                if (! target || typeof target.closest !== 'function') return;

                const trigger = target.closest('.hs-dropdown-toggle');
                if (! trigger) return;

                // Find component ID from trigger's ancestor (sebelum teleport,
                // trigger ada di subtree component).
                const root = trigger.closest('[wire\\:id]');
                if (! root) return;
                const wireId = root.getAttribute('wire:id');

                // Find sibling menu element (initial state, before teleport)
                // dan store ID di sana. Setelah Preline teleport, attribute
                // tetap menempel di element yang sama.
                const wrapper = trigger.closest('.hs-dropdown');
                if (! wrapper) return;
                const menu = wrapper.querySelector('.hs-dropdown-menu');
                if (! menu) return;

                menu.setAttribute('data-livewire-id', wireId);
            };

            document.addEventListener('mouseenter', captureId, true);
            document.addEventListener('focusin', captureId, true);
            document.addEventListener('click', captureId, true);
        })();
    </script>
@endonce

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
                        . ' focus:outline-none cursor-pointer';

                    $icon = $item['icon'] ?? match($item['type'] ?? '') {
                        'delete' => 'lucide-trash-2',
                        'wireModal' => 'lucide-pencil',
                        'href', 'href-navigate' => 'lucide-external-link',
                        default => null,
                    };

                    $attrs = ['class' => $baseClass];

                    switch ($item['type'] ?? '') {
                        case 'click':
                            // ── BRIDGE METHOD CALL VIA Livewire.find ──
                            // Pakai inline `onclick=""` (HTML attribute) bukan
                            // x-on:click karena onclick handler di-attach by
                            // browser as element property — tidak depend on
                            // Alpine scope chain yang putus pasca-teleport.
                            //
                            // `this` di onclick = anchor itu sendiri. Helper
                            // walk up ke menu container yang punya data-
                            // livewire-id (di-set saat trigger pertama
                            // di-interact, sebelum Preline teleport).
                            //
                            // Modal dispatch (kalau ada): pakai window event
                            // dispatch yang juga survive teleport karena
                            // listener-nya di window scope, bukan element
                            // scope.
                            $jsArgs = isset($item['wire:click'])
                                ? $_parseWireCall($item['wire:click'])
                                // Bentuk lama action + param: param dikirim sebagai
                                // string apa adanya, tanpa melewati parser.
                                : $_js((string) ($item['action'] ?? '')).', '.$_js((string) ($item['param'] ?? ''));
                            $jsCall = "__nawasaraDdCall(this, {$jsArgs})";

                            $modalDispatch = ! empty($item['modal'])
                                ? "; window.dispatchEvent(new CustomEvent('open-modal', {detail: ".$_js(['id' => (string) $item['modal'], 'loading' => true])."}))"
                                : '';

                            // wire:confirm support — manual karena kita
                            // bypass Livewire wire:click interceptor.
                            if (! empty($item['confirm'])) {
                                $jsCall = 'if (! confirm('.$_js((string) $item['confirm']).')) return; '.$jsCall;
                            }

                            $attrs['onclick'] = $jsCall.$modalDispatch;
                            break;
                        case 'link':
                        case 'href':
                            $attrs['href'] = $_safeHref($item['href'] ?? $item['url'] ?? '#');
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
                            $attrs['href'] = $_safeHref($item['url'] ?? '#');
                            $attrs['wire:navigate'] = true;
                            break;
                        case 'wireModal':
                            $attrs['onclick'] = 'openLivewireModal('.$_js($item['payload'] ?? null).')';
                            break;
                        case 'delete':
                            // Dulu: name: '{$item['name']}', inilah celah T9.
                            $attrs['onclick'] = "Livewire.dispatch('modal-delete', ".$_js(['id' => (string) $id, 'name' => (string) ($item['name'] ?? '')]).')';
                            break;
                        case 'disabled':
                            $attrs['class'] .= ' !text-gray-300 cursor-not-allowed hover:!bg-transparent dark:!text-neutral-600';
                            $attrs['disabled'] = true;
                            break;
                    }

                    // Note: `wire:confirm` di-handle inline di case 'click'
                    // (manual confirm() guard) karena kita bypass Livewire's
                    // wire:click interceptor. Untuk type lain (delete via
                    // Livewire.dispatch, href dengan plain navigation),
                    // wire:confirm tidak applicable.
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
