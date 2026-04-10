@props([
    'label' => null,
    'name' => null,
    'rows' => 3,
    'placeholder' => '',
    'hint' => null,
])

<div class="flex flex-col gap-1">
    @if ($attributes->has('label'))
        <x-nawasara-ui::form.label :value="$attributes['label']" />
    @endif

    <textarea @if($name) id="{{ $name }}" name="{{ $name }}" @endif rows="{{ $rows }}" placeholder="{{ $placeholder }}"
        {{ $attributes->merge([
            'class' =>
                'w-full py-3 px-4 rounded-md border border-gray-300 text-sm transition-all duration-200 focus:border-transparent focus:ring-2 focus:ring-green-700/80 focus:!border-transparent outline-none dark:bg-neutral-900 dark:border-gray-800 text-gray-900 dark:text-neutral-100',
        ]) }}>{{ $name ? old($name) : '' }}</textarea>

    @if ($hint)
        <p class="text-xs text-gray-500">{{ $hint }}</p>
    @endif

    @if ($name)
        @error($name)
            <p class="text-xs text-red-600">{{ $message }}</p>
        @enderror
    @endif
</div>
