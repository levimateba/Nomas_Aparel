<!doctype html>
<html lang="en" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'POS') | {{ $settings->site_name ?? 'Nomas Apparel' }}</title>
    @include('partials.pwa-head', ['pwaContext' => 'admin'])
    @vite(['resources/css/admin.css', 'resources/js/admin.js'])
    <style>
        [x-cloak] { display: none !important; }
        :root {
            --pos-ink: #101828;
            --pos-gold: #a58112;
            --pos-gold-soft: #c9a227;
            --pos-panel: #0f172a;
            --pos-line: rgba(255,255,255,.08);
        }
        html, body {
            margin: 0;
            width: 100%;
            max-width: 100%;
            overflow-x: hidden;
        }
        body {
            font-family: Outfit, ui-sans-serif, system-ui, sans-serif;
            background:
                radial-gradient(1200px 480px at 12% -10%, rgba(165,129,18,.12), transparent 55%),
                radial-gradient(900px 420px at 88% 0%, rgba(15,23,42,.06), transparent 50%),
                #f3f4f6;
            color: var(--pos-ink);
            min-height: 100%;
        }
        a { color: inherit; text-decoration: none; }

        .pos-top {
            position: sticky;
            top: 0;
            z-index: 40;
            background: linear-gradient(180deg, #0b1220 0%, #111827 100%);
            color: #fff;
            border-bottom: 1px solid rgba(165,129,18,.22);
            box-shadow: 0 10px 28px rgba(15, 23, 42, .22);
        }
        .pos-top-inner {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 16px;
            padding: 12px 20px;
            max-width: 1600px;
            margin: 0 auto;
        }
        .pos-brand-wrap { display: flex; align-items: center; gap: 12px; min-width: 0; }
        .pos-logo {
            width: 42px; height: 42px; border-radius: 12px; object-fit: cover;
            flex: 0 0 42px; background: #1f2937; border: 1px solid rgba(165,129,18,.4);
        }
        .pos-logo-fallback {
            width: 42px; height: 42px; border-radius: 12px; flex: 0 0 42px;
            display: grid; place-items: center; background: var(--pos-gold); color: #111827;
            font-weight: 800; font-size: 18px;
        }
        .pos-brand-text strong { display: block; font-size: 15px; font-weight: 700; line-height: 1.2; letter-spacing: -.01em; }
        .pos-brand-text small { display: block; margin-top: 2px; color: var(--pos-gold-soft); font-size: 11px; font-weight: 600; }

        .pos-nav {
            display: none;
            align-items: center;
            gap: 6px;
            flex: 1;
            justify-content: center;
        }
        .pos-nav a {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 9px 14px;
            border-radius: 999px;
            font-size: 13px;
            font-weight: 650;
            color: rgba(255,255,255,.78);
            border: 1px solid transparent;
            transition: background .15s ease, color .15s ease, border-color .15s ease;
        }
        .pos-nav a:hover { background: rgba(255,255,255,.06); color: #fff; }
        .pos-nav a.is-active {
            background: var(--pos-gold);
            color: #111827;
            border-color: var(--pos-gold);
        }
        .pos-nav a svg { width: 16px; height: 16px; flex: 0 0 auto; }

        .pos-top-right {
            display: flex;
            align-items: center;
            gap: 12px;
            flex-shrink: 0;
        }
        .pos-clock {
            display: none;
            text-align: right;
            font-size: 12px;
            font-weight: 600;
            color: rgba(255,255,255,.72);
            line-height: 1.25;
            white-space: nowrap;
        }
        .pos-clock strong { display: block; color: #fff; font-size: 13px; }
        .pos-sales-pill {
            display: none;
            flex-direction: column;
            align-items: flex-end;
            padding: 6px 12px;
            border-radius: 12px;
            background: rgba(255,255,255,.05);
            border: 1px solid rgba(255,255,255,.08);
            min-width: 110px;
        }
        .pos-sales-pill .lbl {
            display: flex; align-items: center; gap: 5px;
            color: #d1d5db; font-size: 11px; font-weight: 600;
        }
        .pos-sales-pill .amt {
            color: var(--pos-gold-soft); font-size: 15px; font-weight: 800; line-height: 1.2;
        }
        .pos-avatar {
            width: 36px; height: 36px; border-radius: 999px; background: #1f2937; color: var(--pos-gold-soft);
            display: grid; place-items: center; font-weight: 800; font-size: 13px; flex: 0 0 36px;
            border: 1px solid #374151;
        }
        .pos-user-meta { display: none; }
        .pos-user-meta strong { display: block; font-size: 13px; font-weight: 700; }
        .pos-user-meta small { display: block; color: #9ca3af; font-size: 11px; margin-top: 1px; }

        .pos-mobile-bar {
            display: grid;
            grid-template-columns: repeat(4, minmax(0, 1fr));
            gap: 8px;
            padding: 0 12px 12px;
        }
        .pos-mobile-bar a {
            display: flex; flex-direction: column; align-items: center; justify-content: center; gap: 4px;
            min-height: 54px; border-radius: 14px; border: 1px solid rgba(255,255,255,.08);
            background: rgba(255,255,255,.04); color: #fff; font-size: 11px; font-weight: 700;
            padding: 8px 4px;
        }
        .pos-mobile-bar a.is-active {
            background: var(--pos-gold); border-color: var(--pos-gold); color: #111827;
        }
        .pos-mobile-bar svg { width: 18px; height: 18px; }

        .pos-shell { max-width: 1600px; margin: 0 auto; }
        .alert {
            margin: 12px 16px 0; padding: 10px 12px; border-radius: 12px;
            background: #fef9c3; border: 1px solid #fde68a; color: #854d0e; font-size: 14px;
        }
        .alert-danger { background: #fee2e2; border-color: #fecaca; color: #991b1b; }
        .pager { margin: 16px 0 4px; }
        .pager-list { display: flex; flex-wrap: wrap; align-items: center; gap: 8px; list-style: none; padding: 0; margin: 0; }
        .pager-item a, .pager-item span {
            display: inline-flex; align-items: center; justify-content: center; min-width: 36px; padding: 6px 10px;
            border: 1px solid #e5e7eb; border-radius: 8px; background: #fff; font-size: 13px; font-weight: 700;
        }
        .pager-item.is-active span { background: var(--pos-gold); border-color: var(--pos-gold); color: #111; }
        .pager-item.is-disabled span { opacity: 0.4; }

        @media (min-width: 981px) {
            .pos-nav { display: flex; }
            .pos-clock, .pos-sales-pill, .pos-user-meta { display: block; }
            .pos-sales-pill { display: flex; }
            .pos-mobile-bar { display: none; }
            .pos-top-inner { padding: 10px 24px; }
        }
        @media print { .pos-top, .no-print, .alert { display: none !important; } }
    </style>
    @stack('styles')
</head>
<body>
@php
    $store = $settings->site_name ?? 'Store';
    $rawLogoPath = (is_object($settings) && method_exists($settings, 'getRawOriginal'))
        ? $settings->getRawOriginal('logo')
        : ($settings->logo ?? null);
    $posLogo = \App\Support\PublicStorageUrl::fromPath($rawLogoPath) ?? ($settings->logo ?? null);
    $posUser = auth()->user();
    $posInitial = strtoupper(substr($posUser?->name ?? 'A', 0, 1));
    $posRole = $posUser?->role?->name
        ?? ($posUser?->isAdminUser() ? 'Admin' : 'Staff');
    $isPosIndex = request()->routeIs('admin.pos.index') && ! request()->routeIs('admin.pos.scan');
    $isScan = request()->routeIs('admin.pos.scan');
    $hour = (int) now()->format('G');
    $greeting = $hour < 12 ? 'Good morning' : ($hour < 17 ? 'Good afternoon' : 'Good evening');
    $canPos = fn (?string $permission = null): bool => $permission === null
        ? true
        : (bool) $posUser?->hasPermission($permission);
    $showPosSell = $canPos('create_sale');
    $showPosDash = $canPos('create_sale') || $canPos('view_dashboard');
    $showPosOrders = $canPos('view_sales');
    $showPosCustomers = $canPos('manage_customers');
    $showPosReports = $canPos('view_pos_reports');
    $posMobileCols = collect([$showPosDash, $showPosSell, $showPosSell, $showPosOrders])->filter()->count();
    $posMobileCols = max(2, min(4, $posMobileCols ?: 2));
@endphp
<header class="pos-top no-print">
    <div class="pos-top-inner">
        <div class="pos-brand-wrap">
            @if(!empty($posLogo))
                <img src="{{ $posLogo }}" alt="{{ $store }}" class="pos-logo">
            @else
                <div class="pos-logo-fallback" aria-hidden="true">N</div>
            @endif
            <div class="pos-brand-text">
                <strong>{{ $store }}</strong>
                <small>Best Quality For You</small>
            </div>
        </div>

        <nav class="pos-nav" aria-label="POS">
            @if($showPosSell)
            <a href="{{ route('admin.pos.index') }}" class="{{ $isPosIndex ? 'is-active' : '' }}">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="4" width="18" height="14" rx="2"/><path stroke-linecap="round" d="M8 21h8M12 18v3"/></svg>
                POS Terminal
            </a>
            <a href="{{ route('admin.pos.scan') }}" class="{{ $isScan ? 'is-active' : '' }}">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M4 7V5a1 1 0 011-1h2M4 17v2a1 1 0 001 1h2m10-16h2a1 1 0 011 1v2m0 10v2a1 1 0 01-1 1h-2M8 12h8"/></svg>
                Scan &amp; Sell
            </a>
            @endif
            @if($showPosDash)
            <a href="{{ $canPos('create_sale') ? route('admin.cashier.home') : route('admin.dashboard') }}">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M4 10.5V20h6v-6h4v6h6V10.5L12 4 4 10.5z"/></svg>
                Dashboard
            </a>
            @endif
            @if($showPosOrders)
            <a href="{{ route('admin.orders.index') }}">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 7h12l-1 13H7L6 7zm3-3h6l1 3H8l1-3z"/></svg>
                Transactions
            </a>
            @endif
            @if($showPosCustomers)
            <a href="{{ route('admin.customers.index') }}">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M20 21v-2a4 4 0 00-4-4H8a4 4 0 00-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                Customers
            </a>
            @endif
            @if($showPosReports)
            <a href="{{ route('admin.reports.index') }}">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6m6 0V9a2 2 0 012-2h2a2 2 0 012 2v10m6 0V5a2 2 0 00-2-2h-2a2 2 0 00-2 2v14"/></svg>
                Reports
            </a>
            @endif
        </nav>

        <div class="pos-top-right">
            <div class="pos-clock">
                <strong>{{ now()->format('D, d M Y') }}</strong>
                {{ now()->format('h:i A') }}
            </div>
            <div class="pos-sales-pill">
                @hasSection('pos-stats')
                    @yield('pos-stats')
                @else
                    <div class="lbl">Today</div>
                    <div class="amt">—</div>
                @endif
            </div>
            <div class="pos-user-meta">
                <strong>{{ $posUser?->name ?? 'Cashier' }}</strong>
                <small>{{ $posRole }}</small>
            </div>
            <div class="pos-avatar" title="{{ $posUser?->name }}">{{ $posInitial }}</div>
        </div>
    </div>

    <nav class="pos-mobile-bar" style="grid-template-columns: repeat({{ $posMobileCols }}, minmax(0, 1fr));" aria-label="POS mobile">
        @if($showPosDash)
        <a href="{{ $canPos('create_sale') ? route('admin.cashier.home') : route('admin.dashboard') }}">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M4 10.5V20h6v-6h4v6h6V10.5L12 4 4 10.5z"/></svg>
            Home
        </a>
        @endif
        @if($showPosSell)
        <a href="{{ route('admin.pos.index') }}" class="{{ $isPosIndex ? 'is-active' : '' }}">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="4" width="18" height="14" rx="2"/><path stroke-linecap="round" d="M8 21h8M12 18v3"/></svg>
            Sell
        </a>
        <a href="{{ route('admin.pos.scan') }}" class="{{ $isScan ? 'is-active' : '' }}">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M4 7V5a1 1 0 011-1h2M4 17v2a1 1 0 001 1h2m10-16h2a1 1 0 011 1v2m0 10v2a1 1 0 01-1 1h-2M8 12h8"/></svg>
            Scan
        </a>
        @endif
        @if($showPosOrders)
        <a href="{{ route('admin.orders.index') }}">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 7h12l-1 13H7L6 7zm3-3h6l1 3H8l1-3z"/></svg>
            Orders
        </a>
        @endif
    </nav>
</header>

@if(session('success') && ! request()->routeIs('admin.pos.receipt'))
    <div class="alert">{{ session('success') }}</div>
@endif
@if(isset($errors) && $errors->any())
    <div class="alert alert-danger">{{ $errors->first() }}</div>
@endif

<div class="pos-shell">
    @yield('content')
</div>

@include('partials.pwa-install', ['pwaContext' => 'admin'])
<iframe id="pos-drawer-frame" title="Cash drawer" style="position:absolute;width:0;height:0;border:0;visibility:hidden;"></iframe>
<script>
    function nomasOpenCashDrawer() {
        var frame = document.getElementById('pos-drawer-frame');
        if (!frame) return;
        var doc = frame.contentWindow.document;
        doc.open();
        doc.write('<html><body><pre style="font-size:1px;color:#fff;">\x1B\x70\x00\x19\xFA</pre><script>window.onload=function(){window.print();}<\/script></body></html>');
        doc.close();
    }
    document.addEventListener('keydown', function (event) {
        if (event.key === 'F2') {
            event.preventDefault();
            window.location.href = @json(route('admin.pos.scan'));
        }
        if (event.key === 'F12') {
            event.preventDefault();
            nomasOpenCashDrawer();
        }
        if ((event.metaKey || event.ctrlKey) && event.key.toLowerCase() === 'k') {
            var search = document.getElementById('pos-search-input');
            if (search) {
                event.preventDefault();
                search.focus();
                search.select();
            }
        }
    });
</script>
@stack('scripts')
</body>
</html>
