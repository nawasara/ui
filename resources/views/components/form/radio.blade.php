{{--
    Form radio — pilihan tunggal dari beberapa opsi.

    Pemakaian:
        <x-nawasara-ui::form.radio wire:model="tipe" value="local" label="Lokal" />
        <x-nawasara-ui::form.radio wire:model="tipe" value="sso" label="SSO" />
--}}
@props(['label', 'name', 'value'])

<div class="flex items-center gap-2">
    <input id="{{ $name . '-' . $value }}" type="radio" name="{{ $name }}" value="{{ $value }}"
        {{ $attributes->merge([
            'class' => 'border-gray-300 text-emerald-700 shadow-sm focus:border-emerald-600 focus:ring-emerald-600',
        ]) }}
        @checked(old($name) == $value)>
    <label for="{{ $name . '-' . $value }}" class="text-sm text-gray-700 dark:text-gray-300">
        {{ $label }}
    </label>
</div>
@error($name)
    <p class="text-xs text-red-600">{{ $message }}</p>
@enderror
