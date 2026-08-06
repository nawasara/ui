{{--
    Form label — label berdiri sendiri, untuk kolom yang tidak memakai prop
    label bawaan komponen form.

    Kelasnya memakai capitalize, jadi tulis dalam Title Case.

    Pemakaian:
        <x-nawasara-ui::form.label value="Nama Lengkap" required />
--}}
@props(['value'])

<label {{ $attributes->merge(['class' => 'block capitalize text-sm font-medium mb-2 dark:text-white']) }}>
    {{ $value ?? $slot }}
</label>
