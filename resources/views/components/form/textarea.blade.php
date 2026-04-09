@props([
    'label' => null,
    'name',
    'rows' => 3,
    'placeholder' => '',
    'hint' => null,
])

<div class="flex flex-col gap-1">
    @if ($attributes->has('label'))
        <x-nawasara-ui::form.label :value="$attributes['label']" />
    @endif

    <textarea id="{{ $name }}" name="{{ $name }}" rows="{{ $rows }}" placeholder="{{ $placeholder }}"
        {{ $attributes->merge([
            'class' =>
                'w-full py-3 px-4 rounded-md border border-gray-300 text-sm transition-all duration-200 focus:border-transparent focus:ring-4 focus:ring-emerald-500/80 focus:!border-transparent outline-none dark:bg-neutral-900 dark:border-gray-800 text-gray-900 dark:text-neutral-100',
        ]) }}>{{ old($name) }}</textarea>

    @if ($hint)
        <p class="text-xs text-gray-500">{{ $hint }}</p>
    @endif

    @error($name)
        <p class="text-xs text-red-600">{{ $message }}</p>
    @enderror
</div>
