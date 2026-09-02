@if(config('pwa.enabled', true))
@php
    $pwaContext = $pwaContext ?? 'storefront';
    $pwaIsAdmin = $pwaContext === 'admin';
    $pwaSuffix = $pwaIsAdmin ? '-admin' : '';
    $pwaManifest = $pwaIsAdmin ? route('pwa.manifest.admin') : route('pwa.manifest');
    $pwaTheme = $pwaIsAdmin ? config('pwa.admin_theme_color', '#121212') : config('pwa.theme_color', '#16456e');
    $pwaAppTitle = $pwaIsAdmin
        ? (($settings->site_name ?? config('app.name')).' Staff')
        : ($settings->site_name ?? config('app.name'));
@endphp
<link rel="manifest" href="{{ $pwaManifest }}">
<meta name="theme-color" content="{{ $pwaTheme }}">
<meta name="mobile-web-app-capable" content="yes">
<meta name="apple-mobile-web-app-capable" content="yes">
<meta name="apple-mobile-web-app-status-bar-style" content="{{ $pwaIsAdmin ? 'black-translucent' : 'default' }}">
<meta name="apple-mobile-web-app-title" content="{{ $pwaAppTitle }}">
<link rel="apple-touch-icon" href="{{ $settings->logo ?? url('/pwa/icon-192.png') }}">
@endif
