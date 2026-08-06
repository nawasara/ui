{{--
    Page title — judul halaman.

    Untuk halaman yang tidak memakai page-header (yang sudah memuat judul,
    deskripsi, dan zona aksi sekaligus).

    Pemakaian:
        <x-nawasara-ui::page.title>Daftar OPD</x-nawasara-ui::page.title>
--}}
@php
    $titleText = trim((string) $slot);
@endphp

@section('nawasaraTitle', $titleText)

@push('title')
    <h1 class="text-2xl font-bold text-gray-800 dark:text-white mb-4">
        {{ $slot }}
    </h1>
@endpush
