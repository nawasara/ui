{{--
    Empty state — premium look untuk page-level "no data" placeholder.

    Pattern dari /home dashboard, di-extract jadi reusable component.

    Pemakaian dasar:
        <x-nawasara-ui::empty-state
            icon="lucide-database"
            title="Belum ada sync job"
            description="Aktivitas sinkronisasi akan tercatat di sini." />

    Dengan CTA button:
        <x-nawasara-ui::empty-state
            icon="lucide-mail-plus"
            title="Belum ada template"
            description="Buat template untuk auto-send email ke user.">
            <x-nawasara-ui::button color="primary" wire:click="openCreate">
                <x-slot:icon><x-lucide-plus class="size-4" /></x-slot:icon>
                Tambah Template
            </x-nawasara-ui::button>
        </x-nawasara-ui::empty-state>

    Variants (visual tone):
    - "default" — gray icon, dashed border, untuk no-data
    - "filter" — sama tapi label "filter" (untuk no-result-from-filter case),
      narrower padding karena biasanya inside table
    - "celebrate" — emerald tint, untuk positive empty state ("semua bersih,
      tidak ada error" — beda dari "ada masalah, kosong")

    Inside-table mode (compact, in-row):
        <x-nawasara-ui::empty-state
            icon="lucide-search-x"
            title="Tidak ada match"
            description="Coba ubah filter atau search keyword."
            inline />
--}}
@props([
    'icon' => 'lucide-inbox',
    'title' => '',
    'description' => null,
    'variant' => 'default', // default | filter | celebrate
    'inline' => false,      // true → padding compact, no outer border (untuk in-table)
])

@php
    $variantTokens = [
        'default' => [
            'wrapper' => 'border-2 border-dashed border-gray-200 dark:border-neutral-700 bg-gray-50/50 dark:bg-neutral-900/40',
            'iconWrap' => 'bg-gray-100 dark:bg-neutral-800',
            'iconColor' => 'text-gray-400 dark:text-neutral-500',
            'titleColor' => 'text-gray-800 dark:text-neutral-200',
        ],
        'filter' => [
            'wrapper' => 'border border-gray-200 dark:border-neutral-700 bg-white dark:bg-neutral-800',
            'iconWrap' => 'bg-gray-100 dark:bg-neutral-700',
            'iconColor' => 'text-gray-400 dark:text-neutral-400',
            'titleColor' => 'text-gray-800 dark:text-neutral-200',
        ],
        'celebrate' => [
            'wrapper' => 'border border-emerald-200 dark:border-emerald-900/40 bg-emerald-50/40 dark:bg-emerald-900/10',
            'iconWrap' => 'bg-emerald-100 dark:bg-emerald-900/30',
            'iconColor' => 'text-emerald-600 dark:text-emerald-400',
            'titleColor' => 'text-emerald-800 dark:text-emerald-300',
        ],
    ];
    $tokens = $variantTokens[$variant] ?? $variantTokens['default'];

    // Inline mode = compact untuk in-table empty rows. Pakai padding yang lebih
    // hemat dan drop wrapper border (inheriting table cell visual context).
    $padding = $inline ? 'py-12 px-6' : 'py-16 px-6';
    $iconSize = $inline ? 'size-12' : 'size-14';
    $iconInner = $inline ? 'size-6' : 'size-7';
    $titleClass = $inline ? 'text-sm font-semibold' : 'text-base font-semibold';
    $wrapperClass = $inline ? '' : 'rounded-xl '.$tokens['wrapper'];
@endphp

<div {{ $attributes->merge(['class' => "text-center $padding $wrapperClass"]) }}>
    <div class="inline-flex items-center justify-center {{ $iconSize }} rounded-2xl {{ $tokens['iconWrap'] }} mb-3">
        <x-dynamic-component :component="$icon" class="{{ $iconInner }} {{ $tokens['iconColor'] }}" />
    </div>
    @if ($title !== '')
        <p class="{{ $titleClass }} {{ $tokens['titleColor'] }}">
            {{ $title }}
        </p>
    @endif
    @if ($description)
        <p class="mt-1.5 text-sm text-gray-500 dark:text-neutral-400 max-w-sm mx-auto">
            {{ $description }}
        </p>
    @endif
    @if (trim($slot) !== '')
        <div class="mt-4 flex items-center justify-center gap-2">
            {{ $slot }}
        </div>
    @endif
</div>
