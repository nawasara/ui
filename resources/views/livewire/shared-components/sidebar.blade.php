<!-- Sidebar -->
<div id="hs-application-sidebar"
    class="hs-overlay  [--auto-close:lg]
  hs-overlay-open:translate-x-0
  -translate-x-full transition-all duration-300 transform
  w-65 h-full
  hidden
  fixed inset-y-0 start-0 z-60
  bg-white border-e border-gray-200
  lg:block lg:translate-x-0 lg:end-auto lg:bottom-0
  dark:bg-neutral-800 dark:border-neutral-700"
    role="dialog" tabindex="-1" aria-label="Sidebar">
    <div class="relative flex flex-col h-full max-h-full">
        <div class="px-8 pt-4 pb-4 flex items-center border-b border-gray-200 dark:border-neutral-700">
            <a href="{{ url('/') }}" wire:navigate aria-label="Home"
                class="flex-none rounded-xl text-xl inline-block font-semibold focus:outline-hidden focus:opacity-80">
                <x-nawasara-ui::brand-logo height="h-8" />
            </a>
        </div>

        <!-- Content -->
        <div x-data x-init="
                $el.scrollTop = sessionStorage.getItem('sidebar-scroll') || 0;
                $el.addEventListener('scroll', () => sessionStorage.setItem('sidebar-scroll', $el.scrollTop));
            "
            class="h-full overflow-y-auto [&::-webkit-scrollbar]:w-2 [&::-webkit-scrollbar-thumb]:rounded-full [&::-webkit-scrollbar-track]:bg-gray-100 [&::-webkit-scrollbar-thumb]:bg-gray-300 dark:[&::-webkit-scrollbar-track]:bg-neutral-700 dark:[&::-webkit-scrollbar-thumb]:bg-neutral-500">
            <nav class="hs-accordion-group relative space-y-5 pt-5 pb-10 sm:pt-7 px-4 sm:px-8 lg:my-0"
                data-hs-accordion-always-open>
                @php $currentUrl = url()->current(); @endphp

                {{-- Home adalah tombol KEMBALI, bukan menu sederajat dengan
                     workspace di bawahnya.

                     Sebelumnya keduanya sama-sama tebal berikon hijau, jadi
                     terbaca sebagai dua menu setara — padahal yang satu jalan
                     keluar dan yang satu tempat kamu berada. Panah kiri,
                     teks lebih kecil dan redup, lalu garis pemisah: ketiganya
                     menyatakan "ini bukan bagian dari daftar di bawah".

                     Saat berada DI Home, tombolnya tidak dirender sama
                     sekali — tombol kembali ke tempat yang sedang dibuka
                     hanya membingungkan. --}}
                @if ($currentUrl !== url('/'))
                    <div class="border-b border-gray-200 pb-4 dark:border-neutral-700">
                        <a href="{{ url('/') }}" wire:navigate
                            class="group flex items-center gap-2 text-xs font-medium text-gray-500 transition hover:text-gray-900 focus:outline-hidden dark:text-neutral-400 dark:hover:text-neutral-100">
                            <x-lucide-arrow-left class="size-3.5 shrink-0 transition group-hover:-translate-x-0.5" />
                            Kembali ke Beranda
                        </a>
                    </div>
                @endif
                @php
                    $workspaces = app('nawasara.workspaces');
                    $currentWorkspaceMenu = $workspaces->currentMenu();
                    // In a workspace → show only that workspace's submenu (one group,
                    // no header). At Home/root → show every accessible workspace,
                    // organised under its group heading.
                    if ($currentWorkspaceMenu) {
                        $renderGroups = ['' => [$currentWorkspaceMenu]];
                    } else {
                        $renderGroups = [];
                        foreach ($workspaces->grouped() as $groupLabel => $items) {
                            $renderGroups[$groupLabel] = array_map(fn ($ws) => $ws['menu'], $items);
                        }
                    }
                @endphp

                {{-- ── Navigasi ────────────────────────────────────────────
                     Gaya mengikuti sidebar dokumentasi Tailwind:

                     • Judul seksi kecil-kapital-redup, item polos di bawahnya
                     • Item submenu TANPA ikon — hanya teks, jadi mata memindai
                       satu kolom kata alih-alih berpindah antara ikon dan label
                     • Penanda aktif berupa garis kiri + teks tegas, tanpa
                       latar; latar berwarna pada item ramping terbaca berat
                       dan menarik perhatian lebih dari yang pantas
                     • Ikon HANYA di menu utama atas (Home) dan judul workspace,
                       tempat ikon benar-benar membantu membedakan

                     Rail vertikal digambar sebagai border pada <ul>, dan
                     penanda aktif menimpanya dengan border-l pada <a> — bukan
                     elemen terpisah, supaya keduanya tidak pernah bergeser. --}}
                <ul class="space-y-5" x-data="{ show: false }" x-init="setTimeout(() => show = true, 10)"
                    x-show="show"
                    x-transition:enter="transition ease-out duration-200"
                    x-transition:enter-start="opacity-0 translate-x-1"
                    x-transition:enter-end="opacity-100 translate-x-0">
                    @foreach ($renderGroups as $groupLabel => $menusToRender)
                        @if ($groupLabel !== '')
                            <li class="px-1 text-[11px] font-semibold uppercase tracking-wider text-gray-400 dark:text-neutral-500">
                                {{ $groupLabel }}
                            </li>
                        @endif

                        @foreach ($menusToRender as $menu)
                            @if (!empty($menu['submenu']))
                                <li>
                                    {{-- Judul workspace hanya di BERANDA, tempat
                                         beberapa workspace berjejer dan namanya
                                         yang membedakan satu dari yang lain.

                                         Di dalam workspace ia dihilangkan: hanya
                                         ada satu, dan namanya sudah disebut
                                         breadcrumb serta judul halaman. Di sidebar
                                         ia mengulang tanpa menambah — dan justru
                                         mendorong daftar seksi turun sehingga
                                         jaraknya terlihat jomplang. --}}
                                    @if (! $currentWorkspaceMenu)
                                        <div class="mb-2 flex items-center gap-2.5 text-sm font-semibold text-gray-800 dark:text-neutral-200">
                                            @if (! empty($menu['icon']))
                                                <x-dynamic-component :component="$menu['icon']" class="size-4 text-emerald-700 dark:text-emerald-500" />
                                            @endif
                                            {{ $menu['label'] }}
                                        </div>
                                    @endif

                                    {{-- Rail TIDAK di <ul> melainkan di tiap <a>. Kalau digambar di
                                         <ul>, garisnya menerus melewati judul seksi juga —
                                         dan menutupinya dengan latar meninggalkan celah yang
                                         terbaca seperti penanda aktif. Dengan border per
                                         item, judul memang tidak punya garis. --}}
                                    <ul class="space-y-0.5">
                                        @foreach ($menu['submenu'] as $submenu)
                                            {{-- Penanda seksi: entri TANPA url, dipakai untuk
                                                 mengelompokkan submenu di dalam satu workspace.
                                                 Bentuknya ['section' => 'Hibah'].

                                                 Dipakai nawasara/hibah, yang punya tiga kelompok
                                                 (Hibah / Bansos / Bantuan Keuangan) di bawah satu
                                                 workspace — ketiganya harus tetap terlihat saat
                                                 salah satunya dibuka.

                                                 Aman untuk paket lain: yang tidak memakai
                                                 'section' tidak berubah sama sekali. --}}
                                            @if (! empty($submenu['section']))
                                                @if (empty($submenu['permission']) || optional(auth()->user())->can($submenu['permission']))
                                                    {{-- Judul seksi SEJAJAR dengan rail, bukan
                                                         dengan teks itemnya.

                                                         Di sidebar Tailwind, judul rata kiri
                                                         menempel garis vertikal sementara item
                                                         menjorok ke dalam. Itu yang membuat judul
                                                         terbaca sebagai penanda kelompok — kalau
                                                         ikut menjorok, ia hanya tampak seperti
                                                         item yang kebetulan bergaya lain.

                                                         <ul> tidak punya padding — border-nya
                                                         tepat di tepi kiri, dan item menjorok
                                                         lewat pl-4 pada <a>. Jadi judul cukup
                                                         diberi pl-3 (lebih rapat dari item)
                                                         supaya sejajar rail tanpa menempel.

                                                         Latar TIDAK dipakai untuk memutus rail:
                                                         penutup selebar baris meninggalkan celah
                                                         yang terbaca seperti penanda aktif —
                                                         itulah yang membuat dua item tampak
                                                         aktif bersamaan. --}}
                                                    <li class="mt-6 first:mt-0 pb-1.5 text-[11px] font-semibold uppercase tracking-widest text-gray-500 dark:text-neutral-400">
                                                        {{ $submenu['section'] }}
                                                    </li>
                                                @endif
                                                @continue
                                            @endif

                                            @php $isActive = $currentUrl === url($submenu['url']); @endphp
                                            @if (empty($submenu['permission']) || optional(auth()->user())->can($submenu['permission']))
                                                <li>
                                                    <a href="{{ url($submenu['url']) }}"
                                                        @isset($submenu['navigate']) @if ($submenu['navigate']) wire:navigate.hover @endif @endisset
                                                        @class([
                                                            'block border-l pl-4 pr-3 py-2 text-sm transition',
                                                            'border-gray-200 text-gray-600 hover:border-gray-400 hover:text-gray-900 dark:border-neutral-700 dark:text-neutral-400 dark:hover:border-neutral-500 dark:hover:text-neutral-100' => !$isActive,
                                                            'border-emerald-600 font-medium text-emerald-700 dark:border-emerald-500 dark:text-emerald-400' => $isActive,
                                                        ])>
                                                        {{ $submenu['label'] }}
                                                    </a>
                                                </li>
                                            @endif
                                        @endforeach
                                    </ul>
                                </li>
                            @else
                                {{-- Workspace tanpa submenu — satu tautan, dan
                                     di sini ikon dipertahankan karena ia berdiri
                                     sendiri tanpa judul di atasnya. --}}
                                @php $isActive = $currentUrl === url($menu['url']); @endphp
                                <li>
                                    <a href="{{ url($menu['url']) }}" @class([
                                        'flex items-center gap-2 py-1.5 text-sm transition',
                                        'text-gray-700 hover:text-gray-900 dark:text-neutral-300 dark:hover:text-neutral-100' => !$isActive,
                                        'font-semibold text-emerald-700 dark:text-emerald-400' => $isActive,
                                    ])>
                                        @if (!empty($menu['icon']))
                                            <x-dynamic-component :component="$menu['icon']" class="size-4 shrink-0 text-emerald-700 dark:text-emerald-500" />
                                        @endif
                                        <span>{{ $menu['label'] }}</span>
                                    </a>
                                </li>
                            @endif
                        @endforeach
                    @endforeach
                </ul>

            </nav>
        </div>
        <!-- End Content -->
    </div>
</div>
<!-- End Sidebar -->
