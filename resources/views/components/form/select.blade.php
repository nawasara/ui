{{--
    Form select — dropdown pilihan.

    Pemakaian:
        <x-nawasara-ui::form.select label="OPD" wire:model="opdId"
            :options="$opdOptions" placeholder="— pilih OPD —" />
--}}
@props([
    'label' => null,
    'name' => null,
    'options' => [],
    'placeholder' => '-- Pilih --',
    'hint' => null,
])

<div class="flex flex-col gap-1 w-full sm:w-auto sm:min-w-40">
    {{-- Dibaca dari $label, bukan $attributes — prop yang dideklarasikan
         di @props dikeluarkan dari $attributes, jadi has('label') selalu
         false dan labelnya tidak pernah tergambar. --}}
    @if ($label)
        <x-nawasara-ui::form.label :value="$label" />
    @endif

    <select @if($name) id="{{ $name }}" name="{{ $name }}" @endif
        {{ $attributes->merge([
            'class' => 'py-3 px-4 block w-full border border-gray-300 rounded-md text-sm transition-all duration-200 focus:border-transparent focus:ring-2 focus:ring-emerald-700/80 focus:!border-transparent outline-none dark:bg-neutral-900 dark:border-gray-800 text-gray-900 dark:text-neutral-100',
        ]) }}>
        @if ($placeholder)
            <option value="">{{ $placeholder }}</option>
        @endif
        @foreach ($options as $value => $text)
            <option value="{{ $value }}" @selected($name && old($name) == $value)>
                {{ $text }}
            </option>
        @endforeach
        {{ $slot }}
    </select>

    @if ($hint)
        <p class="text-xs text-gray-500">{{ $hint }}</p>
    @endif

    @if ($name)
        @error($name)
            <p class="text-xs text-red-600">{{ $message }}</p>
        @enderror
    @endif
</div>
