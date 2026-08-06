{{--
    Button group — beberapa tombol berdempet sebagai satu kesatuan pilihan.

    Untuk pilihan yang saling meniadakan dan jumlahnya sedikit. Kalau lebih
    dari empat, pakai select.

    Pemakaian:
        <x-nawasara-ui::button-group>
            <x-nawasara-ui::button-group.item>Harian</x-nawasara-ui::button-group.item>
            <x-nawasara-ui::button-group.item>Mingguan</x-nawasara-ui::button-group.item>
        </x-nawasara-ui::button-group>
--}}
<div {{ $attributes->merge(['class' => 'flex flex-wrap gap-1.5 sm:gap-2']) }}>
    {{ $slot }}
</div>
