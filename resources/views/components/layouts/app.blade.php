@php
    $appName = function_exists('brand') ? brand('app_name', config('app.name')) : config('app.name');
    $favicon = function_exists('brand') ? brand('favicon') : null;
@endphp
<!DOCTYPE html>
<html lang="en" class="">

<head>
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('nawasaraTitle', $title ?? $appName)</title>
    @if ($favicon)
        <link rel="icon" type="image/png" href="{{ $favicon }}">
    @endif
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <x-nawasara-toaster::script />
    @livewireStyles
    @stack('nawasaraCoreScript')
</head>

    @php
        $inWorkspace = app('nawasara.workspaces')->current() !== null;
    @endphp

<body x-data class="bg-gray-50 dark:bg-neutral-900">
    <livewire:nawasara-ui.shared-components.topbar />
    {{ $breadcrumb ?? '' }}

    @if ($inWorkspace)
        <livewire:nawasara-ui.shared-components.sidebar />
    @endif

    <!-- Content -->
    <div class="w-full {{ $inWorkspace ? 'lg:ps-64' : '' }}">
        <div class="p-4 sm:p-6 space-y-4 sm:space-y-6 {{ $inWorkspace ? '' : 'max-w-7xl mx-auto' }}">
            {{ $slot }}
        </div>
    </div>
    <!-- End Content -->

    <livewire:nawasara-developer-tools.components.developer-tools />
    <x-nawasara-toaster::toaster position="top-right" :duration="5000" />
    <x-nawasara-modal::script />
    <x-nawasara-ui::init-preline />
    @stack('script')
    @livewireScripts
</body>

</html>
