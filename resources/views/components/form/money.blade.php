{{--
    Input rupiah dengan pemisah ribuan.

    Angka anggaran pemerintah panjang, dan `67756767` tanpa pemisah hampir
    mustahil dibaca — di situlah salah ketik satu nol bersembunyi sampai
    laporan keluar. Yang ditampilkan `67.756.767`; yang dikirim ke Livewire
    tetap `67756767`.

    Pemakaian:
        <div><x-nawasara-ui::form.money label="Anggaran" wire:model="budget" /></div>

    ⚠️ Dua kotak, bukan satu. Yang terlihat hanya tampilan; nilainya disimpan
    di <input type="hidden"> yang membawa wire:model. Menaruh wire:model di
    kotak yang terlihat akan mengirim string bertitik ke validasi `integer`,
    dan setiap penyimpanan ditolak.

    Tidak punya pembungkus <div> sendiri — sama seperti form.input. Di dalam
    grid, SELALU bungkus dengan <div>.
--}}
@props([
    'disabled' => false,
    'placeholder' => '0',
])

@if ($attributes->has('label'))
    <x-nawasara-ui::form.label :value="$attributes['label']" />
@endif

@php
    // Nama properti Livewire diambil dari wire:model apa pun bentuknya
    // (.live, .blur, .lazy). Kotak yang terlihat TIDAK boleh membawanya —
    // Alpine yang menjembatani, supaya Livewire menerima angka murni dan
    // bukan teks bertitik.
    $modelName = null;

    foreach ($attributes->whereStartsWith('wire:model')->getAttributes() as $value) {
        $modelName = $value;
        break;
    }

    if ($modelName === null) {
        throw new \InvalidArgumentException(
            'form.money membutuhkan wire:model — tanpa itu nilainya tidak pernah sampai ke komponen.'
        );
    }
@endphp

<div
    x-data="{
        raw: @entangle($modelName).live,

        /** Tampilan berpemisah — kosong tetap kosong, bukan '0'. */
        get display() {
            if (this.raw === null || this.raw === '' || this.raw === undefined) return '';
            return new Intl.NumberFormat('id-ID').format(this.raw);
        },

        /**
         * Simpan hanya digit. Titik, koma, spasi, dan 'Rp' yang ditempel dari
         * Excel semuanya dibuang — petugas menyalin dari spreadsheet, dan
         * menolak tempelan mereka hanya memindahkan pekerjaan.
         */
        onInput(event) {
            const digits = event.target.value.replace(/\D/g, '');
            this.raw = digits === '' ? null : parseInt(digits, 10);
            event.target.value = this.display;
        },
    }"
    class="relative"
>
    <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-4 text-sm text-gray-500 dark:text-neutral-400">
        Rp
    </span>

    <input
        type="text"
        inputmode="numeric"
        autocomplete="off"
        :value="display"
        x-on:input="onInput($event)"
        placeholder="{{ $placeholder }}"
        {{ $disabled ? 'disabled' : '' }}
        {{ $attributes->except(array_keys($attributes->whereStartsWith('wire:model')->getAttributes()))->merge([
            'class' => 'py-3 pl-10 pr-4 block w-full border border-gray-300 rounded-md text-sm text-right tabular-nums transition-all duration-200 focus:border-transparent focus:ring-2 focus:ring-emerald-700/80 focus:!border-transparent outline-none dark:bg-neutral-900 dark:border-gray-800 text-gray-900 dark:text-neutral-100 disabled:opacity-60',
        ]) }}
    >
</div>
