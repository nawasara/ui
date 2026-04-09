@props([
    'label' => null,
    'name',
    'options' => [],
    'placeholder' => '-- Pilih --',
    'hint' => null,
])

<div class="flex flex-col gap-1">
    @if ($attributes->has('label'))
        <x-nawasara-ui::form.label :value="$attributes['label']" />
    @endif

    <select id="{{ $name }}" name="{{ $name }}"
        {{ $attributes->merge([
            'class' => 'w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm',
        ]) }}>
        @if ($placeholder)
            <option value="">{{ $placeholder }}</option>
        @endif
        @foreach ($options as $value => $text)
            <option value="{{ $value }}" @selected(old($name) == $value)>
                {{ $text }}
            </option>
        @endforeach
    </select>

    @if ($hint)
        <p class="text-xs text-gray-500">{{ $hint }}</p>
    @endif

    @error($name)
        <p class="text-xs text-red-600">{{ $message }}</p>
    @enderror
</div>
