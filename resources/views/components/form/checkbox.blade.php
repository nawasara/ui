@props(['label' => null, 'name' => null, 'value' => 1])

@php
    // Wrapper class (applies to outer div)
    $wrapperClass = $attributes->get('class') ?? '';

    // Allow callers to pass an `input-class` specifically for the input
    $inputClassAttr = $attributes->get('input-class') ?? null;

    // Remove wrapper class and input-class from attributes forwarded to input
    $inputAttributes = $attributes->except(['class', 'input-class', 'id']);

    $inputDefaultClasses = 'rounded border-gray-300 text-blue-600 shadow-sm focus:border-blue-500 focus:ring-blue-500';

    // Generate a safe unique id for the checkbox so label toggles it reliably
    $givenId = $attributes->get('id');
    if ($givenId) {
        $id = $givenId;
    } else {
        if ($name) {
            // remove array brackets if present and append value to make unique
            $base = preg_replace('/[^A-Za-z0-9_-]/', '-', str_replace(['[', ']'], ['-', ''], $name));
            $id = \Illuminate\Support\Str::slug($base . '-' . $value);
        } else {
            $id = 'checkbox-' . \Illuminate\Support\Str::random(6);
        }
    }

    $labelText = $label ?? trim((string) $slot);
@endphp

<div class="{{ $wrapperClass }}">
    <label class="flex items-center gap-2 cursor-pointer">
        <input id="{{ $id }}" type="checkbox" name="{{ $name }}" value="{{ $value }}"
            {{ $inputAttributes->merge([
                'class' => $inputClassAttr ?? $inputDefaultClasses,
            ]) }}
            @checked(old($name))>
        @if ($labelText)
            <span class="text-sm text-gray-700 dark:text-gray-300">{{ $labelText }}</span>
        @endif
    </label>
</div>
@error($name)
    <p class="text-xs text-red-600">{{ $message }}</p>
@enderror
