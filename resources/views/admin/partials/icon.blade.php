@php
    $size = $size ?? 18;
    $paths = [
        'home' => 'M3 11l9-8 9 8M5 10v11h14V10',
        'pos' => 'M4 9h16v11H4V9Zm3-5h10v4H7V4Zm2 9h6',
        'orders' => 'M6 7h12M6 12h12M6 17h8M4 4h16a1 1 0 0 1 1 1v14a1 1 0 0 1-1 1H4a1 1 0 0 1-1-1V5a1 1 0 0 1 1-1Z',
        'products' => 'M21 8.5 12 3 3 8.5v7L12 21l9-5.5v-7ZM12 12l9-5.5M12 12v9M12 12 3 6.5',
        'stocktake' => 'M9 5H5v16h14V5h-4M9 5V3h6v2M9 5h6M8 11h8M8 15h8',
        'categories' => 'M4 5h7v7H4V5Zm9 0h7v7h-7V5ZM4 14h7v7H4v-7Zm9 3h7v4h-7v-4Z',
        'coupons' => 'M4 8a2 2 0 0 1 2-2h12a2 2 0 0 1 2 2v3a2 2 0 1 0 0 4v3a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2v-3a2 2 0 1 0 0-4V8Zm8 1v2m0 4v2',
        'customers' => 'M16 19v-1a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v1M12 11a3 3 0 1 0-3-3 3 3 0 0 0 3 3Zm9 8v-1a3.5 3.5 0 0 0-2.5-3.35M16.5 7.1a2.5 2.5 0 1 1 0 4.8',
        'vendors' => 'M4 10h16l-1.2-4.2A2 2 0 0 0 16.9 4H7.1a2 2 0 0 0-1.9 1.8L4 10Zm0 0v8a1 1 0 0 0 1 1h3v-5h8v5h3a1 1 0 0 0 1-1v-8',
        'payouts' => 'M4 7h16v10H4V7Zm0 4h16M8 15h3',
        'reports' => 'M5 19V9m7 10V5m7 14v-7',
        'blog' => 'M5 5h14v14H5V5Zm3 4h8M8 12h8M8 15h5',
        'reviews' => 'M12 3 14.2 8.2 20 9l-4 3.9.9 5.6L12 16.2 7.1 18.5 8 12.9 4 9l5.8-.8L12 3Z',
        'enquiries' => 'M4 6h16v10H8l-4 4V6Z',
        'subscribers' => 'M4 7h16v10H4V7Zm0 0 8 6 8-6',
        'contacts' => 'M7 3h10v18l-5-2-5 2V3Zm3 5h4M10 12h4',
        'qa' => 'M9 9a3 3 0 1 1 4.2 2.7c-.8.4-1.2.9-1.2 1.8V14m0 3h.01',
        'settings' => 'M12 15a3 3 0 1 0-3-3 3 3 0 0 0 3 3Zm7.4-3a7.4 7.4 0 0 0-.1-1l2-1.5-2-3.5-2.4 1a7.6 7.6 0 0 0-1.7-1L12.8 3h-4l-.4 2.5a7.6 7.6 0 0 0-1.7 1l-2.4-1-2 3.5 2 1.5a7.4 7.4 0 0 0-.1 1 7.4 7.4 0 0 0 .1 1l-2 1.5 2 3.5 2.4-1a7.6 7.6 0 0 0 1.7 1L8.8 21h4l.4-2.5a7.6 7.6 0 0 0 1.7-1l2.4 1 2-3.5-2-1.5a7.4 7.4 0 0 0 .1-1Z',
        'profile' => 'M12 12a4 4 0 1 0-4-4 4 4 0 0 0 4 4Zm0 2c-4 0-8 2-8 5v1h16v-1c0-3-4-5-8-5Z',
        'roles' => 'M8 11V7a4 4 0 1 1 8 0v4M6 11h12v10H6V11Z',
        'permissions' => 'M7 11V8a5 5 0 0 1 10 0v3M6 11h12v10H6V11Z',
        'groups' => 'M8 11a3 3 0 1 0-3-3 3 3 0 0 0 3 3Zm10 0a3 3 0 1 0-3-3 3 3 0 0 0 3 3ZM3 19v-1a4 4 0 0 1 4-4h2a4 4 0 0 1 4 4v1m2 0v-1a4 4 0 0 1 3-3.87',
        'store' => 'M4 9h16l-1-4H5L4 9Zm0 0v10h6v-6h4v6h6V9',
        'bell' => 'M15 18a3 3 0 0 1-6 0m9-2H6l1.2-2.4A6.5 6.5 0 0 0 8 10V9a4 4 0 1 1 8 0v1a6.5 6.5 0 0 0 .8 3.6L18 16Z',
        'calendar' => 'M7 4v2M17 4v2M4 9h16M6 6h12a2 2 0 0 1 2 2v11a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2Z',
        'backup' => 'M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4M7 10l5 5 5-5M12 15V3',
        'backup' => 'M4 7h16v12H4V7Zm4 4h8M8 15h5M12 3v4',
    ];
    $d = $paths[$name] ?? $paths['home'];
@endphp
<svg xmlns="http://www.w3.org/2000/svg" width="{{ $size }}" height="{{ $size }}" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="{{ $d }}" /></svg>
