<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', ($settings->site_name ?? 'Store') . ' - Online Shopping')</title>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@400;500;600;700;800&family=Instrument+Serif:ital@0;1&display=swap" rel="stylesheet">
    <style>
        :root {
            --brand: #a58112;
            --brand-dark: #8a6c0f;
            --brand-soft: #c9a227;
            --bg: #f4f5f7;
            --text: #111827;
            --muted: #6b7280;
            --card: #ffffff;
            --line: #e8e8e8;
            --black: #111827;
            --gold: #a58112;
            --white: #ffffff;
        }
        * { box-sizing: border-box; }
        body {
            margin: 0;
            background: var(--bg);
            color: var(--text);
            font-family: Outfit, ui-sans-serif, system-ui, sans-serif;
        }
        a { color: inherit; text-decoration: none; }
        .container { width: min(1240px, 100% - 32px); margin: 0 auto; }
        .topbar {
            background: #0f172a;
            color: rgba(255,255,255,.82);
            font-size: 13px;
            font-weight: 500;
        }
        .topbar .container {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 12px;
            padding: 9px 0;
            flex-wrap: wrap;
        }
        .topbar-links { display: flex; gap: 16px; flex-wrap: wrap; }
        .topbar a { color: rgba(255,255,255,.82); font-weight: 600; }
        .topbar a:hover { color: var(--brand-soft); }
        .header { background: #fff; border-bottom: 1px solid var(--line); }
        .store-sticky {
            position: sticky;
            top: 0;
            z-index: 40;
            background: #fff;
            box-shadow: 0 8px 24px rgba(15,23,42,.06);
        }
        .header-row {
            display: grid;
            gap: 20px;
            grid-template-columns: minmax(200px, 280px) minmax(0, 1fr) auto;
            align-items: center;
            padding: 18px 0;
        }
        .header-top-row {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 10px;
            min-width: 0;
        }
        .header-top-row .brand { flex: 1; min-width: 0; }
        .header-account { display: none; position: relative; flex: 0 0 auto; }
        .brand {
            display: flex;
            align-items: center;
            gap: 12px;
            min-width: 0;
            overflow: hidden;
        }
        .brand-logo {
            width: 48px; height: 48px; flex: 0 0 48px;
            object-fit: cover; border-radius: 12px;
            border: 1px solid rgba(165,129,18,.35); background: #fff;
        }
        .brand-mark {
            width: 48px; height: 48px; flex: 0 0 48px; border-radius: 12px;
            display: grid; place-items: center;
            background: linear-gradient(145deg, #a58112, #c9a227);
            color: #111; font-weight: 800; font-size: 20px;
        }
        .brand-text { min-width: 0; line-height: 1.15; }
        .brand-text strong {
            display: block;
            font-size: 15px;
            font-weight: 800;
            letter-spacing: -.01em;
            color: var(--text);
            overflow-wrap: anywhere;
        }
        .brand-text small {
            display: block;
            margin-top: 3px;
            font-size: 11px;
            font-weight: 600;
            color: var(--brand);
        }
        .search {
            display: flex;
            min-width: 0;
            width: 100%;
            max-width: 100%;
            background: #fff;
            border: 1.5px solid #e5e7eb;
            border-radius: 999px;
            overflow: hidden;
            box-shadow: 0 1px 2px rgba(16,24,40,.04);
            transition: border-color .15s ease, box-shadow .15s ease;
            min-height: 52px;
        }
        .search:focus-within {
            border-color: rgba(165,129,18,.55);
            box-shadow: 0 0 0 3px rgba(165,129,18,.12);
        }
        .search select, .search input {
            border: 0;
            background: transparent;
            padding: 14px 16px;
            font-size: 15px;
            font-family: inherit;
            outline: none;
        }
        .search select {
            border-right: 1px solid var(--line);
            max-width: 160px;
            min-width: 110px;
            color: #374151;
            font-weight: 600;
        }
        .search input { min-width: 0; flex: 1; }
        .btn { border: 0; border-radius: 10px; padding: 12px 14px; font-weight: 700; cursor: pointer; font-family: inherit; }
        .btn-primary { background: var(--brand); color: #111; white-space: nowrap; }
        .btn-primary:hover { background: var(--brand-dark); color: #fff; }
        .search .btn {
            border-radius: 0;
            padding: 0 28px;
            background: var(--brand);
            color: #111;
            font-weight: 800;
            font-size: 15px;
            min-width: 110px;
        }
        .header-actions { display: flex; align-items: center; gap: 14px; flex-shrink: 0; }
        .util {
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 12px;
            line-height: 1.2;
            color: #1f1f1f;
            transition: opacity .15s ease;
        }
        .util:hover { opacity: .8; }
        .util strong { display: block; font-size: 13px; }
        .util small { color: #6b7280; }
        .util-ico {
            width: 40px; height: 40px; border-radius: 50%;
            display: inline-flex; align-items: center; justify-content: center;
            background: #111827; color: var(--brand-soft); flex: 0 0 40px;
        }
        .util-ico svg { width: 18px; height: 18px; }
        .header-account-btn {
            width: 40px; height: 40px; border-radius: 50%;
            border: 1px solid #ececec; background: #121212; color: var(--gold);
            display: inline-flex; align-items: center; justify-content: center;
            font-weight: 800; font-size: 14px; cursor: pointer; padding: 0;
        }
        .header-account-menu {
            display: none; position: absolute; right: 0; top: calc(100% + 8px); min-width: 220px;
            background: #fff; border: 1px solid #ececec; border-radius: 12px;
            box-shadow: 0 16px 32px rgba(0,0,0,0.12); padding: 8px; z-index: 60;
        }
        .header-account.open .header-account-menu { display: block; }
        .header-account-head {
            padding: 10px 12px 8px; border-bottom: 1px solid #f3f4f6; margin-bottom: 4px;
        }
        .header-account-head strong { display: block; font-size: 13px; color: #121212; }
        .header-account-head small { display: block; color: #6b7280; font-size: 11px; margin-top: 2px; }
        .header-account-menu a,
        .header-account-menu button {
            display: flex; width: 100%; text-align: left; border: 0; background: transparent;
            padding: 10px 12px; border-radius: 8px; font-size: 13px; color: #121212;
            text-decoration: none; cursor: pointer; font-weight: 600; font-family: inherit;
        }
        .header-account-menu a:hover,
        .header-account-menu button:hover { background: #f6f6f6; }
        .header-account-menu .logout-btn { color: #991b1b; }

        .nav {
            background: #fff;
            border-top: 1px solid var(--line);
        }
        .nav-inner {
            display: flex;
            align-items: stretch;
            width: min(1240px, 100%);
            margin: 0 auto;
            max-width: 100%;
            gap: 8px;
        }
        .nav-all {
            background: var(--gold);
            color: #111;
            font-weight: 800;
            padding: 13px 18px;
            position: relative;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            flex: 0 0 auto;
            font-size: 14px;
            border-radius: 0;
            cursor: pointer;
        }
        .nav-scroll {
            display: flex;
            align-items: center;
            gap: 4px;
            min-width: 0;
            flex: 1;
            overflow-x: auto;
            -webkit-overflow-scrolling: touch;
            scrollbar-width: none;
            padding: 0 4px;
        }
        .nav-scroll::-webkit-scrollbar { display: none; }
        .nav-scroll > a {
            font-size: 14px;
            white-space: nowrap;
            color: #374151;
            padding: 13px 12px;
            flex: 0 0 auto;
            font-weight: 600;
            border-bottom: 2px solid transparent;
            transition: color .15s ease, border-color .15s ease;
        }
        .nav-scroll > a:hover,
        .nav-scroll > a.active {
            color: var(--brand);
            border-bottom-color: var(--brand);
            font-weight: 700;
        }
        .nav-cats-inline { display: flex; align-items: center; gap: 0; }
        .nav-cats-inline a {
            font-size: 14px;
            white-space: nowrap;
            color: #374151;
            padding: 13px 12px;
            font-weight: 600;
            border-bottom: 2px solid transparent;
        }
        .nav-cats-inline a:hover,
        .nav-cats-inline a.active {
            color: var(--brand);
            border-bottom-color: var(--brand);
        }
        .nav-offers {
            display: none;
            align-items: center;
            margin-left: auto;
            flex: 0 0 auto;
            padding: 8px 0;
        }
        .nav-offers a {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 8px 14px;
            border-radius: 999px;
            background: #f6f0df;
            color: var(--brand-dark);
            font-size: 13px;
            font-weight: 800;
            white-space: nowrap;
        }
        .nav-offers a:hover { background: #efe4c4; }
        .nav-drop {
            display: none; position: absolute; top: 100%; left: 0; min-width: 260px;
            background: #fff; color: #121212; box-shadow: 0 16px 32px rgba(0,0,0,.14); z-index: 50;
            border-radius: 0 0 12px 12px; overflow: hidden;
        }
        .nav-all:hover .nav-drop, .nav-all:focus-within .nav-drop { display: block; }
        .nav-drop a {
            display: flex; align-items: center; justify-content: space-between;
            padding: 11px 16px; color: #121212; font-weight: 600; font-size: 14px;
            border-bottom: 1px solid #f3f4f6;
        }
        .nav-drop a:hover { background: #fafafa; color: var(--brand); }

        .card { background: var(--card); border: 1px solid var(--line); border-radius: 14px; }
        .footer {
            margin-top: 48px;
            background: #0f172a;
            border-top: 3px solid var(--gold);
            color: rgba(255,255,255,.78);
        }
        .footer .container {
            padding: 36px 0 28px;
            display: grid;
            gap: 24px;
            grid-template-columns: 1.4fr 1fr 1fr;
        }
        .footer h4 {
            margin: 0 0 12px;
            color: #fff;
            font-size: 14px;
            font-weight: 800;
            letter-spacing: .02em;
        }
        .footer p, .footer a { font-size: 14px; line-height: 1.6; color: rgba(255,255,255,.72); }
        .footer a:hover { color: var(--brand-soft); }
        .footer-brand strong {
            display: block;
            color: #fff;
            font-size: 16px;
            font-weight: 800;
            margin-bottom: 8px;
        }
        .footer-copy {
            grid-column: 1 / -1;
            padding-top: 18px;
            border-top: 1px solid rgba(255,255,255,.08);
            font-size: 13px;
            color: rgba(255,255,255,.5);
        }
        .flash-toast {
            position: fixed; top: 18px; left: 50%; transform: translateX(-50%); z-index: 80;
            background: #121212; color: #fff; border: 1px solid var(--gold); border-radius: 12px;
            padding: 12px 18px; font-weight: 700; box-shadow: 0 16px 32px rgba(0,0,0,0.2);
            max-width: min(520px, calc(100% - 24px));
        }
        .flash-toast.error { border-color: #dc3545; }
        .pager { margin: 18px 0 8px; }
        .pager-list { display: flex; flex-wrap: wrap; align-items: center; gap: 8px; list-style: none; padding: 0; margin: 0; }
        .pager-item a, .pager-item span {
            display: inline-flex; align-items: center; justify-content: center; min-width: 38px; height: 38px;
            padding: 0 12px; border: 1px solid var(--line); border-radius: 8px; font-size: 13px; font-weight: 700; background: #fff;
        }
        .pager-item.is-active span { background: var(--gold); border-color: var(--gold); color: var(--black); }
        .pager-item.is-disabled span { opacity: 0.4; }
        nav svg, .pager svg { width: 16px !important; height: 16px !important; display: inline-block; }

        .p-card {
            background: #fff;
            border: 1px solid var(--line);
            border-radius: 14px;
            overflow: hidden;
            transition: transform .18s ease, box-shadow .18s ease, border-color .18s ease;
        }
        .p-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 14px 28px rgba(15,23,42,.08);
            border-color: rgba(165,129,18,.35);
        }
        .p-card-media { position: relative; }
        .p-card-img { display: block; height: 180px; background: #f3f4f6 center/cover no-repeat; }
        .p-disc {
            position: absolute; left: 10px; top: 10px;
            background: #dc2626; color: #fff; font-size: 11px; font-weight: 800;
            border-radius: 999px; padding: 4px 9px;
        }
        .p-heart { position: absolute; right: 10px; top: 10px; margin: 0; }
        .p-heart button, a.p-heart {
            width: 34px; height: 34px; border: 0; border-radius: 50%; background: #fff; color: #111;
            display: inline-flex; align-items: center; justify-content: center;
            box-shadow: 0 4px 10px rgba(0,0,0,.12); cursor: pointer; font-size: 16px;
        }
        .p-heart.is-on button { color: #dc2626; }
        .p-card-body { padding: 12px 14px 14px; }
        .p-cat { color: #6b7280; font-size: 12px; font-weight: 600; text-transform: uppercase; letter-spacing: .03em; }
        .p-name {
            display: block; font-weight: 700; font-size: 14px; margin: 5px 0 8px;
            min-height: 38px; line-height: 1.3; color: #111827;
        }
        .p-name:hover { color: var(--brand); }
        .p-rating { color: #d1d5db; font-size: 12px; margin-bottom: 6px; }
        .p-rating .is-on { color: #a58112; }
        .p-price { font-weight: 800; font-size: 15px; }
        .p-was { color: #9ca3af; font-weight: 500; text-decoration: line-through; margin-left: 6px; font-size: 12px; }
        .p-actions { display: flex; gap: 8px; margin-top: 12px; flex-wrap: wrap; }
        .p-actions .btn { padding: 9px 12px; font-size: 12px; border-radius: 10px; flex: 1; text-align: center; min-width: 0; }
        .p-actions form { flex: 1.2; margin: 0; min-width: 0; }
        .p-actions form .btn { width: 100%; }
        .btn-cart {
            background: var(--brand) !important;
            border: 0 !important;
            color: #111 !important;
            font-weight: 800;
        }
        .btn-cart:hover { background: var(--brand-dark) !important; color: #fff !important; }
        .btn-view {
            background: #fff !important;
            border: 1px solid #d1d5db !important;
            color: #374151 !important;
            font-weight: 700;
        }
        .btn-view:hover { border-color: var(--brand) !important; color: var(--brand) !important; }

        @media (min-width: 981px) {
            .nav-offers { display: flex; }
            .search {
                min-height: 56px;
            }
            .search select, .search input {
                padding: 16px 18px;
                font-size: 16px;
            }
            .search .btn {
                padding: 0 32px;
                font-size: 16px;
                min-width: 120px;
            }
        }
        @media (max-width: 1100px) {
            .header-row { grid-template-columns: minmax(0, 180px) minmax(280px, 1fr) auto; gap: 12px; }
            .footer .container { grid-template-columns: 1fr 1fr; }
        }
        @media (max-width: 900px) {
            .header-row { grid-template-columns: 1fr; gap: 10px; }
            .header-top-row { grid-column: 1 / -1; }
            .header-account { display: block; }
            .header-actions { justify-content: flex-start; flex-wrap: wrap; }
        }
        @media (max-width: 768px) {
            .container { width: min(1240px, 100% - 16px); }
            .topbar .container {
                flex-direction: column;
                align-items: flex-start;
                gap: 4px;
                font-size: 12px;
                padding: 7px 0;
            }
            .header-row { grid-template-columns: 1fr; gap: 10px; padding: 12px 0; }
            .brand-logo, .brand-mark { width: 40px; height: 40px; flex-basis: 40px; }
            .brand-text strong { font-size: 13px; }
            .search { border-radius: 12px; min-height: 46px; }
            .search select { display: none; }
            .search input { font-size: 13px; padding: 10px 12px; }
            .search .btn { padding: 10px 14px; font-size: 13px; min-width: 0; }
            .header-actions {
                display: grid;
                grid-template-columns: repeat(2, minmax(0, 1fr));
                gap: 8px;
                width: 100%;
            }
            .util { min-width: 0; gap: 6px; }
            .util strong { font-size: 12px; }
            .util small { font-size: 11px; }
            .util-ico { width: 34px; height: 34px; flex-basis: 34px; }
            .nav-all { padding: 11px 12px; font-size: 13px; }
            .nav-scroll { gap: 0; padding: 0 4px; }
            .nav-scroll > a { font-size: 13px; padding: 11px 10px; }
            .nav-cats-inline { display: none; }
            .footer .container { grid-template-columns: 1fr; padding: 28px 0 22px; }
        }
    </style>
    @stack('styles')
</head>
<body>
    @php
        $brandName = $settings->site_name ?? 'Store';
        $rawLogoPath = (is_object($settings) && method_exists($settings, 'getRawOriginal'))
            ? $settings->getRawOriginal('logo')
            : ($settings->logo ?? null);
        $brandLogo = \App\Support\PublicStorageUrl::fromPath($rawLogoPath) ?? ($settings->logo ?? null);
        $contactItems = collect($siteContacts ?? []);
        $primaryPhone = optional($contactItems->firstWhere('type', 'phone'))->value ?? '+254 700 000 000';
        $cartCount = $cartCount ?? collect(session('cart', []))->sum('qty');
        $cartTotal = $cartTotal ?? 0;
        $wishlistCount = is_array($wishlistIds ?? null) ? count($wishlistIds) : 0;
        $userInitial = strtoupper(substr(auth()->user()?->name ?? 'U', 0, 1));
    @endphp

    <div class="store-sticky">
    <div class="topbar">
        <div class="container">
            <span>Free delivery on orders over KES 10,000</span>
            <div class="topbar-links">
                <a href="tel:{{ preg_replace('/\s+/', '', $primaryPhone) }}">Call us {{ $primaryPhone }}</a>
                <a href="{{ route('orders.track') }}">Track Order</a>
                <a href="{{ route('contact') }}">Help Center</a>
            </div>
        </div>
    </div>

    <header class="header">
        <div class="container header-row">
            <div class="header-top-row">
                <a class="brand" href="{{ route('home') }}">
                    @if(!empty($brandLogo))
                        <img src="{{ $brandLogo }}" alt="{{ $brandName }}" class="brand-logo">
                    @else
                        <span class="brand-mark" aria-hidden="true">N</span>
                    @endif
                    <span class="brand-text">
                        <strong>{{ strtoupper($brandName) }}</strong>
                        <small>Best Quality For You</small>
                    </span>
                </a>
                @auth
                    <div class="header-account" id="store-account">
                        <button type="button" class="header-account-btn" id="store-account-toggle" aria-label="Account menu" aria-haspopup="true" aria-expanded="false">{{ $userInitial }}</button>
                        <div class="header-account-menu">
                            <div class="header-account-head">
                                <strong>{{ auth()->user()->name }}</strong>
                                <small>{{ auth()->user()->email }}</small>
                            </div>
                            <a href="{{ route('orders.my') }}">My orders</a>
                            <a href="{{ route('wishlist.index') }}">Wishlist</a>
                            <form method="POST" action="{{ route('logout') }}" style="margin:0;">
                                @csrf
                                <button type="submit" class="logout-btn">Logout</button>
                            </form>
                        </div>
                    </div>
                @endauth
            </div>
            <form class="search" method="GET" action="{{ route('shop.index') }}">
                <select name="category_id">
                    <option value="">All</option>
                    @foreach($shopCategories ?? [] as $navCategory)
                        <option value="{{ $navCategory->id }}" @selected((string) request('category_id') === (string) $navCategory->id)>{{ $navCategory->name }}</option>
                    @endforeach
                </select>
                <input type="text" name="q" value="{{ request('q') }}" placeholder="Search products, brands and categories">
                <button class="btn btn-primary" type="submit">Search</button>
            </form>
            <div class="header-actions">
                @auth
                    <a class="util" href="{{ route('orders.my') }}">
                        <span class="util-ico" aria-hidden="true">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M20 21v-2a4 4 0 00-4-4H8a4 4 0 00-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                        </span>
                        <span><strong>Account</strong><small>My orders</small></span>
                    </a>
                    <a class="util" href="{{ route('wishlist.index') }}">
                        <span class="util-ico" aria-hidden="true">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M20.84 4.61a5.5 5.5 0 00-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 00-7.78 7.78L12 21.23l8.84-8.84a5.5 5.5 0 000-7.78z"/></svg>
                        </span>
                        <span><strong>Wishlist</strong><small>{{ $wishlistCount }} saved</small></span>
                    </a>
                @else
                    <a class="util" href="{{ route('login') }}">
                        <span class="util-ico" aria-hidden="true">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M20 21v-2a4 4 0 00-4-4H8a4 4 0 00-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                        </span>
                        <span><strong>Account</strong><small>Sign in / Up</small></span>
                    </a>
                @endauth
                <a class="util" href="{{ route('cart.index') }}">
                    <span class="util-ico" aria-hidden="true">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 7h12l-1 13H7L6 7zm3-3h6l1 3H8l1-3z"/></svg>
                    </span>
                    <span><strong>Cart ({{ $cartCount }})</strong><small>KES {{ number_format((float) $cartTotal, 2) }}</small></span>
                </a>
            </div>
        </div>
        <nav class="nav">
            <div class="nav-inner container">
                <div class="nav-all" tabindex="0">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" aria-hidden="true"><path stroke-linecap="round" d="M4 7h16M4 12h16M4 17h16"/></svg>
                    All Categories
                    <div class="nav-drop">
                        @forelse($shopCategories ?? [] as $navCategory)
                            <a href="{{ route('shop.index', ['category_id' => $navCategory->id]) }}">{{ $navCategory->name }} <span>›</span></a>
                        @empty
                            <a href="{{ route('shop.index') }}">Shop <span>›</span></a>
                        @endforelse
                    </div>
                </div>
                <div class="nav-scroll">
                    <a href="{{ route('home') }}" class="{{ request()->routeIs('home') ? 'active' : '' }}">Home</a>
                    <a href="{{ route('shop.index') }}" class="{{ request()->routeIs('shop.*') && !request()->filled('category_id') ? 'active' : '' }}">Shop</a>
                    <div class="nav-cats-inline">
                        @foreach(($shopCategories ?? collect())->take(6) as $navCategory)
                            <a href="{{ route('shop.index', ['category_id' => $navCategory->id]) }}" class="{{ (string) request('category_id') === (string) $navCategory->id ? 'active' : '' }}">{{ $navCategory->name }}</a>
                        @endforeach
                    </div>
                    <a href="{{ route('contact') }}" class="{{ request()->routeIs('contact') ? 'active' : '' }}">Contact</a>
                    <a href="{{ route('blog.index') }}" class="{{ request()->routeIs('blog.*') ? 'active' : '' }}">Blog</a>
                </div>
                <div class="nav-offers">
                    <a href="{{ route('shop.index', ['sale' => 1]) }}">Special Offers ›</a>
                </div>
            </div>
        </nav>
    </header>
    </div>

    <main>
        @if(session('success'))
            <div class="flash-toast">{{ session('success') }}</div>
        @endif
        @if(session('error'))
            <div class="flash-toast error">{{ session('error') }}</div>
        @endif
        @if($errors->any())
            <div class="flash-toast error">{{ $errors->first() }}</div>
        @endif
        @yield('content')
    </main>

    <footer class="footer">
        <div class="container">
            <div class="footer-brand">
                <strong>{{ $brandName }}</strong>
                <p>Premium apparel for every occasion. Suits, fashion, bags, uniforms and shoes — delivered across Kenya.</p>
            </div>
            <div>
                <h4>Shop</h4>
                <p><a href="{{ route('shop.index') }}">All products</a></p>
                <p><a href="{{ route('shop.index', ['sale' => 1]) }}">Special offers</a></p>
                <p><a href="{{ route('orders.track') }}">Track order</a></p>
            </div>
            <div>
                <h4>Support</h4>
                <p><a href="{{ route('contact') }}">Help Center</a></p>
                <p><a href="tel:{{ preg_replace('/\s+/', '', $primaryPhone) }}">{{ $primaryPhone }}</a></p>
                <p><a href="{{ route('blog.index') }}">Blog</a></p>
            </div>
            <div class="footer-copy">© {{ date('Y') }} {{ $brandName }}. Best Quality For You.</div>
        </div>
    </footer>
    <script>
        setTimeout(function () {
            document.querySelectorAll('.flash-toast').forEach(function (el) { el.remove(); });
        }, 4500);

        (function () {
            var account = document.getElementById('store-account');
            var toggle = document.getElementById('store-account-toggle');
            if (!account || !toggle) return;

            toggle.addEventListener('click', function (e) {
                e.stopPropagation();
                var willOpen = !account.classList.contains('open');
                account.classList.toggle('open', willOpen);
                toggle.setAttribute('aria-expanded', willOpen ? 'true' : 'false');
            });

            document.addEventListener('click', function () {
                account.classList.remove('open');
                toggle.setAttribute('aria-expanded', 'false');
            });
        })();
    </script>
    @stack('scripts')
</body>
</html>
