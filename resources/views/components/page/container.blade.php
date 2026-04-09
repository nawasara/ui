<div class="container mx-auto px-4 py-6">
    <div class="flex flex-col gap-4">
        {{-- Breadcrumb --}}
        {{-- @isset($breadcrumb)
            <div>
                {{ $breadcrumb }}
            </div>
        @endisset --}}

        {{-- Header Section (Title + Actions) --}}
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            {{-- Title --}}
            <div>
                @stack('title')

            </div>

            {{-- Actions --}}
            <div class="flex flex-wrap gap-2 sm:justify-end">
                {{ $actions ?? '' }}
            </div>
        </div>
        {{-- Page Content --}}
        <div>
            {{ $slot }}
        </div>
    </div>
</div>
