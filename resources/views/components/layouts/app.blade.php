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
        $workspaces = app('nawasara.workspaces');
        $currentWorkspace = $workspaces->current();
        $inWorkspace = $currentWorkspace !== null;

        // Auto-generated breadcrumb: kalau page tidak define <x-slot name="breadcrumb">
        // dan user lagi di dalam workspace, derive breadcrumb dari workspace name +
        // active submenu label. Tujuannya supaya tiap page tidak harus declare slot
        // satu-satu — pattern repetitif yang gampang miss saat tambah page baru.
        //
        // Detection: cocokkan request path dengan submenu URL (longest match wins).
        $autoBreadcrumbItems = null;
        if ($inWorkspace) {
            $autoBreadcrumbItems = [['label' => $currentWorkspace['label'], 'url' => $currentWorkspace['first_url'] ?? '#']];

            $currentPath = trim(request()->path(), '/');
            $bestSubmenu = null;
            $bestLength = 0;
            foreach ($currentWorkspace['menu']['submenu'] ?? [] as $sub) {
                if (empty($sub['url'])) continue;
                $subPath = trim((string) parse_url($sub['url'], PHP_URL_PATH), '/');
                if ($subPath !== '' && str_starts_with($currentPath, $subPath) && strlen($subPath) > $bestLength) {
                    $bestSubmenu = $sub;
                    $bestLength = strlen($subPath);
                }
            }

            if ($bestSubmenu) {
                $autoBreadcrumbItems[] = ['label' => $bestSubmenu['label']];
            }
        }
    @endphp

<body x-data class="bg-gray-50 dark:bg-neutral-900">
    <livewire:nawasara-ui.shared-components.topbar />
    @isset($breadcrumb)
        {{ $breadcrumb }}
    @elseif ($autoBreadcrumbItems)
        <livewire:nawasara-ui.shared-components.breadcrumb :items="$autoBreadcrumbItems" />
    @endif

    @if ($inWorkspace)
        <livewire:nawasara-ui.shared-components.sidebar />
    @endif

    <!-- Content
         Page transition: subtle opacity fade saat wire:navigate triggered.
         Listening ke Livewire navigation events:
         - `livewire:navigating` → konten lama mulai fade out (opacity-50)
         - `livewire:navigated` → konten baru muncul fade-in via CSS animation

         Pure CSS opacity transition (no layout shift) — kompositor-only,
         tidak block render. Durasi ringan 150ms — kurang dari itu user tidak
         notice, lebih dari itu mulai feel sluggish.

         Di-skip kalau `prefers-reduced-motion` set (accessibility). -->
    <div class="w-full {{ $inWorkspace ? 'lg:ps-64' : '' }}">
        <div
            x-data="{ navigating: false }"
            @livewire:navigating.window="navigating = true"
            @livewire:navigated.window="navigating = false"
            x-bind:class="navigating ? 'opacity-50' : 'opacity-100'"
            class="p-4 sm:p-6 space-y-4 sm:space-y-6 transition-opacity duration-150 motion-reduce:transition-none {{ $inWorkspace ? '' : 'max-w-7xl mx-auto' }}">
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
