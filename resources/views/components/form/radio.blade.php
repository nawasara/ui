@props(['label', 'name', 'value'])

<div class="flex items-center gap-2">
    <input id="{{ $name . '-' . $value }}" type="radio" name="{{ $name }}" value="{{ $value }}"
        {{ $attributes->merge([
            'class' => 'border-gray-300 text-blue-600 shadow-sm focus:border-blue-500 focus:ring-blue-500',
        ]) }}
        @checked(old($name) == $value)>
    <label for="{{ $name . '-' . $value }}" class="text-sm text-gray-700 dark:text-gray-300">
        {{ $label }}
    </label>
</div>
@error($name)
    <p class="text-xs text-red-600">{{ $message }}</p>
@enderror
