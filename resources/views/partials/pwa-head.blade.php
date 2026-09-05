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
    $pwaIconVersion = config('pwa.cache_version', '1');
    $pwaIcon192 = url('/pwa/icon-192.png').'?v='.$pwaIconVersion;
    $pwaAppleIcon = is_file(public_path('pwa/apple-touch-icon.png'))
        ? url('/pwa/apple-touch-icon.png').'?v='.$pwaIconVersion
        : $pwaIcon192;
@endphp
<link rel="manifest" href="{{ $pwaManifest }}?v={{ $pwaIconVersion }}">
<meta name="theme-color" content="{{ $pwaTheme }}">
<meta name="mobile-web-app-capable" content="yes">
<meta name="apple-mobile-web-app-capable" content="yes">
<meta name="apple-mobile-web-app-status-bar-style" content="{{ $pwaIsAdmin ? 'black-translucent' : 'default' }}">
<meta name="apple-mobile-web-app-title" content="{{ $pwaAppTitle }}">
<link rel="icon" type="image/png" sizes="192x192" href="{{ $pwaIcon192 }}">
<link rel="apple-touch-icon" href="{{ $pwaAppleIcon }}">
@endif
