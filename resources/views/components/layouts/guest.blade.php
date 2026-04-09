<!DOCTYPE html>
<html lang="en" class="">

<head>
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <script>
        if (
            localStorage.getItem('theme') === 'dark' ||
            (!localStorage.getItem('theme') && window.matchMedia('(prefers-color-scheme: dark)').matches)
        ) {
            document.documentElement.classList.add('dark');
        } else {
            document.documentElement.classList.remove('dark');
        }
    </script>

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title ?? config('app.name') }}</title>
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <x-nawasara-toaster::script />
    @livewireStyles
</head>

<body>
    <div class="min-h-screen bg-gray-100 dark:bg-neutral-700">
        {{ $slot }}
    </div>

    <x-nawasara-toaster::toaster position="top-right" :duration="5000" />
    <x-nawasara-ui::init-preline />
    @livewireScripts
</body>

</html>
