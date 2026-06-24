@props([
    'title' => null,
    'description' => 'Superapp pemerintahan — monitoring, manajemen aset, dan layanan terintegrasi dalam satu platform.',
])

@php
    $appName = function_exists('brand') ? brand('app_name', config('app.name', 'Nawasara')) : config('app.name', 'Nawasara');
    $pageTitle = $title ?? $appName;
    // brand('favicon') is the admin-uploaded override; fall back to the bundled
    // Nawasara mark in public/brand. OG/favicon must be ABSOLUTE urls — WhatsApp,
    // Telegram, and Twitter crawlers do not resolve relative paths.
    $favicon = function_exists('brand') ? brand('favicon') : null;
    $ogImage = function_exists('brand') ? brand('og_image') : null;
    $ogImageUrl = $ogImage ?: url('/brand/og-image.png');
@endphp

{{-- Favicons. The .ico (multi-size 16/32/48) is the broad fallback; modern
     browsers pick the PNG/SVG. apple-touch-icon covers iOS home-screen. --}}
@if ($favicon)
    <link rel="icon" type="image/png" href="{{ $favicon }}">
@else
    <link rel="icon" href="{{ url('/favicon.ico') }}" sizes="any">
    <link rel="icon" type="image/png" sizes="32x32" href="{{ url('/brand/favicon-32.png') }}">
    <link rel="icon" type="image/png" sizes="16x16" href="{{ url('/brand/favicon-16.png') }}">
    <link rel="icon" type="image/svg+xml" href="{{ url('/brand/nawasara-mark.svg') }}">
    <link rel="apple-touch-icon" sizes="180x180" href="{{ url('/brand/apple-touch-icon.png') }}">
@endif
<meta name="theme-color" content="#059669">

{{-- Open Graph (WhatsApp/Telegram/Facebook) + Twitter Card. The image is what
     renders as the link thumbnail when the URL is shared. --}}
<meta property="og:type" content="website">
<meta property="og:site_name" content="{{ $appName }}">
<meta property="og:title" content="{{ $pageTitle }}">
<meta property="og:description" content="{{ $description }}">
<meta property="og:image" content="{{ $ogImageUrl }}">
<meta property="og:image:width" content="1200">
<meta property="og:image:height" content="630">
<meta property="og:url" content="{{ url()->current() }}">
<meta name="twitter:card" content="summary_large_image">
<meta name="twitter:title" content="{{ $pageTitle }}">
<meta name="twitter:description" content="{{ $description }}">
<meta name="twitter:image" content="{{ $ogImageUrl }}">
<meta name="description" content="{{ $description }}">
