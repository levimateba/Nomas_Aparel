<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Progressive Web App
    |--------------------------------------------------------------------------
    |
    | When enabled, visitors on mobile (and supported desktop browsers) can
    | install the storefront as an app from the browser install prompt.
    |
    */
    'enabled' => env('PWA_ENABLED', true),

    'theme_color' => env('PWA_THEME_COLOR', '#16456e'),

    'background_color' => env('PWA_BACKGROUND_COLOR', '#ffffff'),

    /*
    | Bump this when deploying asset or shell changes so clients refresh caches.
    */
    'cache_version' => env('PWA_CACHE_VERSION', '1'),

    'admin_theme_color' => env('PWA_ADMIN_THEME_COLOR', '#121212'),

    'admin_background_color' => env('PWA_ADMIN_BACKGROUND_COLOR', '#121212'),

    'admin_start_url' => env('PWA_ADMIN_START_URL', '/admin/pos'),
];
