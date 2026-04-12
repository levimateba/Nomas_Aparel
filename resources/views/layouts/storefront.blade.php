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
            --bg: #ffffff;
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
        .topbar .container { display: flex; justify-content: space-between; padding: 8px 0; }
        .header { background: #fff; border-bottom: 1px solid var(--line); position: sticky; top: 0; z-index: 30; }
        .header-row { display: grid; gap: 16px; grid-template-columns: 220px 1fr auto; align-items: center; padding: 14px 0; }
        .brand {
            display: inline-flex;
            align-items: center;
            gap: 10px;
            font-size: 24px;
            font-weight: 800;
            color: var(--brand);
        }
        .brand-logo {
            width: 44px;
            height: 44px;
            object-fit: cover;
            border-radius: 10px;
            border: 1px solid var(--line);
            background: #fff;
        }
        .brand-text { line-height: 1; }
        .search { display: flex; gap: 8px; }
        .search input { width: 100%; border: 1px solid #d1d5db; border-radius: 8px; padding: 12px; font-size: 14px; }
        .btn { border: 0; border-radius: 8px; padding: 12px 14px; font-weight: 700; cursor: pointer; }
        .btn-primary { background: var(--brand); color: var(--black); }
        .btn-primary:hover { background: var(--brand-dark); }
        .header-actions { display: flex; align-items: center; gap: 10px; font-size: 14px; }
        .nav { background: #fff; border-bottom: 1px solid var(--line); }
        .nav .container { display: flex; gap: 18px; padding: 10px 0; overflow-x: auto; }
        .nav a { font-size: 14px; white-space: nowrap; color: #1f1f1f; }
        .nav a:hover, .nav a.active { color: var(--brand); font-weight: 700; }
        .card { background: var(--card); border: 1px solid var(--line); border-radius: 10px; }
        .footer { margin-top: 40px; background: var(--black); border-top: 2px solid var(--gold); }
        .footer .container { padding: 24px 0; color: #f3e9c4; font-size: 14px; }
        @media (max-width: 900px) {
            .header-row { grid-template-columns: 1fr; }
        }
    </style>
    @stack('styles')
</head>
<body>
    @php
        $brandName = $settings->site_name ?? 'Carenels';
        $rawLogoPath = (is_object($settings) && method_exists($settings, 'getRawOriginal'))
            ? $settings->getRawOriginal('logo')
            : ($settings->logo ?? null);
        $brandLogo = \App\Support\PublicStorageUrl::fromPath($rawLogoPath) ?? ($settings->logo ?? null);
        $contactItems = collect($siteContacts ?? []);
        $primaryPhone = optional($contactItems->firstWhere('type', 'phone'))->value ?? '+254 700 000 000';
        $cartCount = collect(session('cart', []))->sum('qty');
        $wishlistCount = auth()->check() ? \App\Models\WishlistItem::where('user_id', auth()->id())->count() : 0;
    @endphp

    <div class="topbar">
        <div class="container">
            <span>Sell on {{ $brandName }}</span>
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
                <input type="text" name="q" value="{{ request('q') }}" placeholder="Search products, brands and categories">
                <button class="btn btn-primary" type="submit">Search</button>
            </form>
            <div class="header-actions">
                @auth
                    <a href="{{ route('orders.my') }}">My Orders</a>
                    <a href="{{ route('wishlist.index') }}">Wishlist ({{ $wishlistCount }})</a>
                @else
                    <a href="{{ route('login') }}">Account</a>
                @endauth
                <a href="{{ route('orders.track') }}">Track Order</a>
                <a href="{{ route('cart.index') }}">Cart ({{ $cartCount }})</a>
            </div>
        </div>
    </header>

    <nav class="nav">
        <div class="container">
            <a href="{{ route('home') }}" class="{{ request()->routeIs('home') ? 'active' : '' }}">Home</a>
            <a href="{{ route('shop.index') }}" class="{{ request()->routeIs('shop.*') ? 'active' : '' }}">Shop</a>
            <a href="{{ route('blog.index') }}" class="{{ request()->routeIs('blog.*') ? 'active' : '' }}">Blog</a>
            <a href="#">Supermarket</a>
            <a href="#">Phones</a>
            <a href="#">Computing</a>
            <a href="#">Fashion</a>
            <a href="#">Electronics</a>
        </div>
    </nav>

    <main>
        @yield('content')
    </main>

    <footer class="footer">
        <div class="container">
            <div>{{ $brandName }} Marketplace</div>
            <div>Fast delivery, secure checkout, best prices.</div>
        </div>
    </footer>
</body>
</html>
