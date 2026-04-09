<nav class="flex text-sm text-gray-500" aria-label="Breadcrumb">
    <ol class="inline-flex items-center space-x-1">
        @foreach ($items as $item)
            <li class="inline-flex items-center">
                @if (!$loop->last)
                    <a href="{{ $item['url'] }}" class="hover:text-gray-700">
                        {{ $item['label'] }}
                    </a>
                    <span class="mx-2">/</span>
                @else
                    <span class="text-gray-700 font-medium">{{ $item['label'] }}</span>
                @endif
            </li>
        @endforeach
    </ol>
</nav>
