@props([
    'href' => '#',
])

<a href="{{ $href }}"
    {{ $attributes->merge([
        'class' => 'py-1.5 px-2.5 inline-flex items-center gap-x-1.5 text-sm rounded-lg
                               text-gray-800 bg-gray-100
                               hover:text-green-700 focus:outline-hidden',
    ]) }}>
    {{ $slot }}
</a>
