<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', ($settings->site_name ?? 'Store') . ' - Online Shopping')</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        :root {
            --brand: #d4af37;
            --brand-dark: #b8942d;
            --bg: #f6f6f6;
            --text: #121212;
            --muted: #5f5f5f;
            --card: #ffffff;
            --line: #e7e7e7;
            --black: #121212;
            --gold: #d4af37;
            --white: #ffffff;
        }
        * { box-sizing: border-box; }
        body { margin: 0; background: var(--bg); color: var(--text); font-family: Inter, sans-serif; }
        a { color: inherit; text-decoration: none; }
        .container { width: min(1240px, 100% - 32px); margin: 0 auto; }
        .topbar { background: var(--black); color: #e8d9a2; font-size: 13px; }
        .topbar .container { display: flex; justify-content: space-between; gap: 12px; padding: 8px 0; }
        .header { background: #fff; border-bottom: 1px solid var(--line); position: sticky; top: 0; z-index: 40; }
        .header-row {
            display: grid;
            gap: 16px;
            grid-template-columns: minmax(0, 240px) minmax(0, 1fr) auto;
            align-items: center;
            padding: 14px 0;
        }
        .brand { display: flex; align-items: center; gap: 10px; min-width: 0; overflow: hidden; font-size: 15px; font-weight: 800; color: var(--brand); }
        .brand-logo { width: 44px; height: 44px; flex: 0 0 44px; object-fit: cover; border-radius: 10px; border: 1px solid var(--line); background: #fff; }
        .brand-text { min-width: 0; line-height: 1.15; overflow-wrap: anywhere; display: -webkit-box; -webkit-box-orient: vertical; -webkit-line-clamp: 3; overflow: hidden; }
        .search { display: flex; min-width: 0; width: 100%; background: #fff; border: 1px solid #d1d5db; border-radius: 8px; overflow: hidden; }
        .search select, .search input { border: 0; background: transparent; padding: 12px; font-size: 14px; }
        .search select { border-right: 1px solid var(--line); max-width: 150px; }
        .search input { min-width: 0; flex: 1; }
        .btn { border: 0; border-radius: 8px; padding: 12px 14px; font-weight: 700; cursor: pointer; }
        .btn-primary { background: var(--brand); color: var(--black); white-space: nowrap; }
        .btn-primary:hover { background: var(--brand-dark); }
        .search .btn { border-radius: 0; }
        .header-actions { display: flex; align-items: center; gap: 16px; }
        .util {
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 12px;
            line-height: 1.2;
            color: #1f1f1f;
        }
        .util strong { display: block; font-size: 13px; }
        .util small { color: #6b7280; }
        .util-ico {
            width: 36px; height: 36px; border-radius: 50%;
            display: inline-flex; align-items: center; justify-content: center;
            background: #111; color: var(--gold); font-size: 16px; flex: 0 0 36px;
        }
        .nav { background: var(--black); }
        .nav .container { display: flex; gap: 16px; padding: 0; overflow-x: auto; align-items: center; }
        .nav a, .nav-all { font-size: 14px; white-space: nowrap; color: #fff; padding: 12px 0; }
        .nav a:hover, .nav a.active { color: var(--gold); font-weight: 700; }
        .nav-all {
            background: var(--gold); color: #121212; font-weight: 800;
            padding: 12px 16px; position: relative; display: inline-flex; align-items: center; gap: 8px;
        }
        .nav-drop {
            display: none; position: absolute; top: 100%; left: 0; min-width: 240px;
            background: #fff; color: #121212; box-shadow: 0 12px 24px rgba(0,0,0,.16); z-index: 50;
        }
        .nav-all:hover .nav-drop, .nav-all:focus-within .nav-drop { display: block; }
        .nav-drop a { display: block; padding: 10px 14px; color: #121212; }
        .nav-drop a:hover { background: #f6f6f6; color: var(--brand); }
        .card { background: var(--card); border: 1px solid var(--line); border-radius: 10px; }
        .footer { margin-top: 40px; background: var(--black); border-top: 2px solid var(--gold); }
        .footer .container { padding: 24px 0; color: #f3e9c4; font-size: 14px; }
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
        .p-card { background: #fff; border: 1px solid var(--line); border-radius: 10px; overflow: hidden; }
        .p-card-media { position: relative; }
        .p-card-img { display: block; height: 170px; background: #f3f4f6 center/cover no-repeat; }
        .p-disc { position: absolute; left: 8px; top: 8px; background: #dc2626; color: #fff; font-size: 11px; font-weight: 800; border-radius: 999px; padding: 3px 8px; }
        .p-heart { position: absolute; right: 8px; top: 8px; margin: 0; }
        .p-heart button, a.p-heart {
            width: 32px; height: 32px; border: 0; border-radius: 50%; background: #fff; color: #111;
            display: inline-flex; align-items: center; justify-content: center; box-shadow: 0 4px 10px rgba(0,0,0,.12); cursor: pointer; font-size: 16px;
        }
        .p-heart.is-on button { color: #dc2626; }
        .p-card-body { padding: 10px 12px 12px; }
        .p-cat { color: #6b7280; font-size: 12px; }
        .p-name { display: block; font-weight: 700; font-size: 14px; margin: 4px 0 6px; min-height: 36px; }
        .p-rating { color: #d1d5db; font-size: 12px; margin-bottom: 6px; }
        .p-rating .is-on { color: #d4af37; }
        .p-price { font-weight: 800; }
        .p-was { color: #9ca3af; font-weight: 500; text-decoration: line-through; margin-left: 6px; font-size: 12px; }
        .p-actions { display: flex; gap: 6px; margin-top: 10px; flex-wrap: wrap; }
        .p-actions .btn { padding: 8px 10px; font-size: 12px; }
        .btn-cart { background: #fff; border: 1px solid #d1d5db; }
        @media (max-width: 1100px) {
            .header-row { grid-template-columns: minmax(0, 200px) minmax(0, 1fr); }
            .header-actions { grid-column: 1 / -1; justify-content: flex-end; }
        }
        @media (max-width: 700px) {
            .header-row { grid-template-columns: 1fr; }
            .header-actions { justify-content: flex-start; flex-wrap: wrap; }
            .search select { display: none; }
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
    @endphp

    <div class="topbar">
        <div class="container">
            <span>Free delivery on selected orders</span>
            <span>Call us: {{ $primaryPhone }}</span>
        </div>
    </div>

    <header class="header">
        <div class="container header-row">
            <a class="brand" href="{{ route('home') }}">
                @if(!empty($brandLogo))
                    <img src="{{ $brandLogo }}" alt="{{ $brandName }}" class="brand-logo">
                @endif
                <span class="brand-text">{{ strtoupper($brandName) }}</span>
            </a>
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
                        <span class="util-ico">👤</span>
                        <span><strong>Account</strong><small>My orders</small></span>
                    </a>
                    <a class="util" href="{{ route('wishlist.index') }}">
                        <span class="util-ico">♡</span>
                        <span><strong>Wishlist</strong><small>{{ $wishlistCount }} saved</small></span>
                    </a>
                @else
                    <a class="util" href="{{ route('login') }}">
                        <span class="util-ico">👤</span>
                        <span><strong>Account</strong><small>Sign in / Up</small></span>
                    </a>
                @endauth
                <a class="util" href="{{ route('orders.track') }}">
                    <span class="util-ico">📦</span>
                    <span><strong>Track Order</strong><small>Check status</small></span>
                </a>
                <a class="util" href="{{ route('cart.index') }}">
                    <span class="util-ico">🛒</span>
                    <span><strong>Cart ({{ $cartCount }})</strong><small>KES {{ number_format((float) $cartTotal, 2) }}</small></span>
                </a>
            </div>
        </div>
        <nav class="nav">
            <div class="container">
                <div class="nav-all">
                    ☰ All Categories
                    <div class="nav-drop">
                        @forelse($shopCategories ?? [] as $navCategory)
                            <a href="{{ route('shop.index', ['category_id' => $navCategory->id]) }}">{{ $navCategory->name }}</a>
                        @empty
                            <a href="{{ route('shop.index') }}">Shop</a>
                        @endforelse
                    </div>
                </div>
                <a href="{{ route('home') }}" class="{{ request()->routeIs('home') ? 'active' : '' }}">Home</a>
                <a href="{{ route('shop.index') }}" class="{{ request()->routeIs('shop.*') && !request()->filled('category_id') ? 'active' : '' }}">Shop</a>
                <a href="{{ route('blog.index') }}" class="{{ request()->routeIs('blog.*') ? 'active' : '' }}">Blog</a>
                <a href="{{ route('contact') }}" class="{{ request()->routeIs('contact') ? 'active' : '' }}">Contact</a>
                @foreach($shopCategories ?? [] as $navCategory)
                    <a href="{{ route('shop.index', ['category_id' => $navCategory->id]) }}" class="{{ (string) request('category_id') === (string) $navCategory->id ? 'active' : '' }}">{{ $navCategory->name }}</a>
                @endforeach
            </div>
        </nav>
    </header>

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
            <div>{{ $brandName }} Marketplace</div>
            <div>Fast delivery, secure checkout, best prices.</div>
        </div>
    </footer>
    <script>
        setTimeout(function () {
            document.querySelectorAll('.flash-toast').forEach(function (el) { el.remove(); });
        }, 4500);
    </script>
</body>
</html>
