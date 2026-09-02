<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'POS')</title>
    @include('partials.pwa-head', ['pwaContext' => 'admin'])
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        :root { --gold: #d4af37; --black: #121212; --bg: #f3f4f6; }
        * { box-sizing: border-box; }
        body { margin: 0; font-family: Inter, system-ui, sans-serif; background: var(--bg); color: var(--black); }
        a { color: inherit; text-decoration: none; }
        .pos-top {
            display: flex; justify-content: space-between; align-items: center; gap: 12px;
            background: #121212; color: #fff; padding: 12px 18px; position: sticky; top: 0; z-index: 20;
        }
        .pos-brand { font-weight: 800; color: var(--gold); }
        .pos-meta { display: flex; gap: 14px; align-items: center; flex-wrap: wrap; font-size: 13px; }
        .pos-top a, .pos-top button {
            background: var(--gold); color: #121212; border: 0; border-radius: 10px; padding: 8px 12px; font-weight: 800; cursor: pointer;
        }
        .pos-top .ghost { background: transparent; color: #fff; border: 1px solid rgba(255,255,255,.2); }
        .alert { margin: 12px 16px 0; padding: 10px 12px; border-radius: 10px; background: #faf3d0; }
        .alert-danger { background: #fee2e2; color: #991b1b; }
        .pager { margin: 16px 0 4px; }
        .pager-list { display: flex; flex-wrap: wrap; align-items: center; gap: 8px; list-style: none; padding: 0; margin: 0; }
        .pager-item a, .pager-item span {
            display: inline-flex; align-items: center; justify-content: center; min-width: 36px; padding: 6px 10px;
            border: 1px solid #e5e7eb; border-radius: 8px; background: #fff; font-size: 13px; font-weight: 700;
        }
        .pager-item.is-active span { background: #d4af37; border-color: #d4af37; }
        .pager-item.is-disabled span { opacity: 0.4; }
        @media print { .pos-top, .no-print, .alert { display: none !important; } }
    </style>
    @stack('styles')
</head>
<body>
    @php $store = $settings->site_name ?? 'Store'; @endphp
    <header class="pos-top no-print">
        <div>
            <div class="pos-brand">{{ $store }} POS</div>
            <div style="font-size:12px;color:#cfcfcf;">{{ auth()->user()?->name }} · {{ now()->format('D d M Y H:i') }}</div>
        </div>
        <div class="pos-meta">
            @hasSection('pos-stats')
                @yield('pos-stats')
            @endif
            <a class="ghost" href="{{ route('admin.dashboard') }}">Dashboard</a>
            <a href="{{ route('admin.orders.index') }}">Orders</a>
            <form method="POST" action="{{ route('admin.logout') }}" style="margin:0;">
                @csrf
                <button type="submit" class="ghost">Logout</button>
            </form>
        </div>
    </header>
    @if(session('success') && ! request()->routeIs('admin.pos.receipt'))
        <div class="alert">{{ session('success') }}</div>
    @endif
    @if($errors->any())
        <div class="alert alert-danger">{{ $errors->first() }}</div>
    @endif
    @yield('content')
    @include('partials.pwa-install', ['pwaContext' => 'admin'])
</body>
</html>
