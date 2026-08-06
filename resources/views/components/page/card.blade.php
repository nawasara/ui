{{--
    Page card — kartu putih dengan batas dan padding, wadah dasar isi halaman.

    Pemakaian:
        <x-nawasara-ui::page.card>
            ... isi ...
        </x-nawasara-ui::page.card>
--}}
<div
    {{ $attributes->merge(['class' => 'bg-white shadow rounded-lg p-4 dark:bg-neutral-700 dark:border dark:border-1 dark:border-neutral-500']) }}>
    {{ $slot }}
</div>
