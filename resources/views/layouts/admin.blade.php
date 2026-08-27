<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>@yield('title', 'Admin panel')</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/app.css') }}" />
    <style>
        :root {
            --primary-dark: #121212;
            --primary-green: #d4af37;
            --gold: #d4af37;
            --bg: #f3f4f6;
            --sidebar-w: 260px;
        }
        * { box-sizing: border-box; }
        body {
            margin: 0;
            font-family: Inter, system-ui, -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: var(--bg);
            color: #121212;
        }
        a { color: inherit; }
        .sidebar {
            width: var(--sidebar-w);
            background: linear-gradient(180deg, #0c0c0c 0%, #161616 100%);
            height: 100vh;
            position: fixed;
            top: 0;
            left: 0;
            z-index: 1160;
            display: flex;
            flex-direction: column;
            overflow: hidden;
            transition: transform 0.25s ease;
        }
        .sidebar-brand {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 18px 16px 14px;
            border-bottom: 1px solid rgba(255,255,255,0.08);
            color: #fff;
            text-decoration: none;
        }
        .brand-logo { width: 42px; height: 42px; border-radius: 10px; object-fit: cover; background: rgba(255,255,255,0.12); }
        .brand-mark {
            width: 42px; height: 42px; border-radius: 10px; flex: 0 0 42px;
            display: grid; place-items: center; background: #d4af37; color: #121212; font-weight: 800;
        }
        .brand-title { font-weight: 800; font-size: 13px; line-height: 1.25; color: #d4af37; }
        .brand-subtitle { font-size: 11px; color: rgba(255,255,255,0.62); margin-top: 2px; }
        .sidebar-nav { flex: 1; overflow-y: auto; padding: 12px 10px 16px; }
        .sidebar-section {
            padding: 10px 12px 6px;
            color: rgba(255,255,255,0.45);
            font-size: 11px;
            font-weight: 800;
            letter-spacing: 0.12em;
            text-transform: uppercase;
        }
        .sidebar a.nav-link {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 10px 12px;
            margin-bottom: 4px;
            color: rgba(255,255,255,0.88);
            text-decoration: none;
            border-radius: 10px;
            font-size: 14px;
            font-weight: 500;
        }
        .sidebar a.nav-link:hover { background: rgba(212,175,55,0.12); color: #fff; }
        .sidebar a.nav-link.is-active {
            background: #d4af37;
            color: #121212;
            font-weight: 800;
        }
        .nav-badge {
            margin-left: auto;
            background: #d4af37;
            color: #121212;
            border-radius: 999px;
            padding: 1px 8px;
            font-size: 11px;
            font-weight: 800;
        }
        .sidebar-toggle {
            width: calc(100% - 8px);
            margin: 4px 4px 0;
            text-align: left;
            background: transparent !important;
            box-shadow: none !important;
            color: rgba(255,255,255,0.45) !important;
            padding: 10px 12px 6px;
            font-size: 11px;
            font-weight: 800;
            letter-spacing: 0.12em;
            text-transform: uppercase;
            display: flex;
            align-items: center;
            justify-content: space-between;
            border-radius: 8px;
        }
        .sidebar-toggle:hover { transform: none; background: transparent; box-shadow: none; color: #d4af37; }
        .sidebar-caret { transition: transform 0.2s ease; }
        .sidebar-submenu {
            overflow: hidden;
            max-height: 2200px;
            opacity: 1;
            transition: max-height 0.28s ease, opacity 0.2s ease;
        }
        .sidebar-submenu.is-collapsed { max-height: 0; opacity: 0; pointer-events: none; }
        .sidebar-foot { padding: 12px 14px 18px; }
        .visit-store {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            width: 100%;
            border: 1px solid #d4af37;
            color: #d4af37;
            background: transparent;
            border-radius: 10px;
            padding: 11px 12px;
            font-weight: 700;
            text-decoration: none;
        }
        .visit-store:hover { background: rgba(212,175,55,0.12); }
        .sidebar-close {
            display: none;
            width: 34px; height: 34px; border-radius: 10px;
            border: 1px solid rgba(255,255,255,0.2);
            background: rgba(255,255,255,0.12);
            color: #fff; margin: 10px 12px 0 auto;
        }
        .sidebar-backdrop { display: none; position: fixed; inset: 0; background: rgba(12, 24, 36, 0.46); z-index: 1150; }
        body.sidebar-open .sidebar-backdrop { display: block; }
        .shell { margin-left: var(--sidebar-w); min-height: 100vh; display: flex; flex-direction: column; }
        .topbar {
            background: #fff;
            border-bottom: 1px solid #e7e7e7;
            padding: 16px 24px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 16px;
            position: sticky;
            top: 0;
            z-index: 40;
        }
        .menu-toggle {
            display: none;
            width: 42px;
            height: 42px;
            border-radius: 12px;
            border: 1px solid #e5e7eb;
            background: #fff !important;
            color: #121212 !important;
            box-shadow: none !important;
        }
        .sidebar-close {
            background: rgba(255,255,255,0.12) !important;
            color: #fff !important;
            box-shadow: none !important;
        }
        .welcome h1 { margin: 0; font-size: 1.35rem; font-weight: 800; }
        .welcome p { margin: 4px 0 0; color: #6b7280; font-size: 13px; }
        .top-utils { display: flex; align-items: center; gap: 12px; }
        .top-date {
            display: inline-flex; align-items: center; gap: 8px;
            color: #4b5563; font-size: 13px; font-weight: 600;
            background: #f8f8f8; border: 1px solid #ececec; border-radius: 999px; padding: 8px 12px;
        }
        .icon-btn {
            width: 40px; height: 40px; border-radius: 12px; border: 1px solid #ececec;
            background: #fff !important; display: inline-flex; align-items: center; justify-content: center;
            position: relative; color: #121212; cursor: pointer; box-shadow: none !important;
        }
        .icon-btn .dot {
            position: absolute; top: 6px; right: 6px; min-width: 16px; height: 16px;
            padding: 0 4px; border-radius: 999px; background: #d4af37; color: #121212;
            font-size: 10px; font-weight: 800; display: inline-flex; align-items: center; justify-content: center;
        }
        .profile-chip {
            display: flex; align-items: center; gap: 8px; text-decoration: none;
            padding: 4px 10px 4px 4px; border-radius: 999px; border: 1px solid #ececec; background: #fff;
        }
        .avatar {
            width: 34px; height: 34px; border-radius: 50%; background: #121212; color: #d4af37;
            display: grid; place-items: center; font-weight: 800; font-size: 13px;
        }
        .profile-chip strong { display: block; font-size: 13px; }
        .profile-chip small { color: #6b7280; font-size: 11px; }
        .dropdown { position: relative; }
        .dropdown-menu {
            display: none; position: absolute; right: 0; top: calc(100% + 8px); min-width: 240px;
            background: #fff; border: 1px solid #ececec; border-radius: 12px;
            box-shadow: 0 16px 32px rgba(0,0,0,0.12); padding: 8px; z-index: 50;
        }
        .dropdown.open .dropdown-menu { display: block; }
        .dropdown-menu a, .dropdown-menu button {
            display: flex; width: 100%; text-align: left; border: 0; background: transparent !important;
            padding: 10px 12px; border-radius: 8px; font-size: 13px; color: #121212; text-decoration: none; cursor: pointer;
            box-shadow: none !important; font-weight: 600;
        }
        .dropdown-menu a:hover, .dropdown-menu button:hover { background: #f6f6f6; }
        .dropdown-menu button { box-shadow: none; transform: none; }
        .content { padding: 22px 24px 12px; flex: 1; }
        .admin-footer {
            text-align: center; color: #9ca3af; font-size: 12px; padding: 8px 24px 20px;
        }
        .card {
            background: white;
            border-radius: 16px;
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.04);
            padding: 18px;
            margin-bottom: 18px;
            border: 1px solid #ececec;
        }
        .btn, button, input[type=submit] {
            border: none;
            border-radius: 10px;
            color: #121212;
            background: linear-gradient(135deg, #d4af37, #b8942d);
            padding: 9px 15px;
            cursor: pointer;
            font-weight: 700;
        }
        .btn:hover, button:hover, input[type=submit]:hover { opacity: 0.94; }
        .btn-secondary { background: #fff; color: #121212; border: 1px solid #e5e7eb; }
        .alert { background: rgba(212, 175, 55, 0.16); color: #121212; padding: 10px 12px; border-left: 4px solid var(--gold); margin-bottom: 16px; border-radius: 10px; }
        .alert-danger { background: rgba(220, 53, 69, 0.1); color: #8a1f2d; border-left-color: #dc3545; }
        .pager { margin: 16px 0 4px; }
        .pager-list { display: flex; flex-wrap: wrap; align-items: center; gap: 8px; list-style: none; padding: 0; margin: 0; }
        .pager-item a, .pager-item span {
            display: inline-flex; align-items: center; justify-content: center; min-width: 36px; height: 36px;
            padding: 0 12px; border: 1px solid #e5e8eb; border-radius: 999px; font-size: 13px; font-weight: 700; background: #fff; color: #121212; text-decoration: none;
        }
        .pager-item.is-active span { background: linear-gradient(135deg, #d4af37, #b8942d); border-color: #d4af37; }
        .pager-item.is-disabled span { opacity: 0.4; }
        nav svg, .pager svg { width: 16px !important; height: 16px !important; display: inline-block; }
        table { width: 100%; border-collapse: separate; border-spacing: 0; }
        th, td { text-align: left; padding: 10px 8px; border-bottom: 1px solid #e5e8eb; }
        .page-head { display: flex; justify-content: space-between; align-items: center; gap: 12px; flex-wrap: wrap; margin-bottom: 14px; }
        .page-head h2 { margin: 0; }
        .admin-kpis { display: grid; grid-template-columns: repeat(auto-fit, minmax(170px, 1fr)); gap: 12px; margin-bottom: 16px; }
        .admin-kpi {
            background: #fff; border: 1px solid #ececec; border-radius: 16px; padding: 14px;
            box-shadow: 0 8px 16px rgba(0,0,0,0.04);
        }
        .admin-kpi span { display: block; font-size: 12px; color: #6b7280; font-weight: 700; }
        .admin-kpi strong { display: block; margin-top: 6px; font-size: 1.25rem; font-weight: 800; }
        .admin-kpi small { color: #9ca3af; }
        .admin-toolbar {
            display: flex; justify-content: space-between; gap: 10px; flex-wrap: wrap; align-items: end;
            background: #fff; border: 1px solid #ececec; border-radius: 16px; padding: 14px; margin-bottom: 16px;
        }
        .admin-filters { display: flex; gap: 10px; flex-wrap: wrap; align-items: end; flex: 1; }
        .admin-filters .field { min-width: 150px; flex: 1; }
        .admin-filters label { font-size: 12px; color: #6b7280; margin: 0 0 6px; font-weight: 700; }
        .admin-filters input, .admin-filters select { margin-bottom: 0 !important; }
        .table-wrap { overflow-x: auto; }
        .admin-table th {
            font-size: 11px; letter-spacing: .06em; text-transform: uppercase; color: #6b7280; background: #f8fafc;
        }
        .status-pill {
            display: inline-flex; border-radius: 999px; padding: 4px 10px; font-size: 12px; font-weight: 800;
            background: #faf3d0; color: #7b6116;
        }
        .status-pill.on { background: #dcfce7; color: #166534; }
        .status-pill.off { background: #f3f4f6; color: #4b5563; }
        .status-pill.danger { background: #fee2e2; color: #991b1b; }
        .status-pill.warn { background: #ffedd5; color: #c2410c; }
        .status-pill.info { background: #dbeafe; color: #1d4ed8; }
        .row-actions { display: flex; flex-wrap: wrap; gap: 8px; align-items: center; }
        .row-actions .btn, .row-actions button {
            margin: 0; min-height: 32px; padding: 6px 11px; font-size: .82rem; border-radius: 999px;
        }
        .row-actions form { display: inline-flex; margin: 0; }
        .btn-danger { background: linear-gradient(135deg, #c0392b, #e74c3c) !important; color: #fff !important; }
        .empty-cell { text-align: center; color: #6b7280; padding: 36px 12px !important; }
        .thumb { width: 48px; height: 48px; object-fit: cover; border-radius: 8px; border: 1px solid #e5e7eb; display: block; }
        .muted { color: #6b7280; font-size: 12px; }
        .content label { display: block; font-weight: 700; margin: 0 0 6px; }
        .content input[type=text],
        .content input[type=email],
        .content input[type=password],
        .content input[type=number],
        .content input[type=url],
        .content input[type=date],
        .content select,
        .content textarea {
            width: 100%;
            padding: 11px 12px;
            border: 1px solid #d7dde2;
            border-radius: 10px;
            margin-bottom: 14px;
        }
        .admin-form-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 0 16px; }
        .perm-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 8px 16px; margin: 4px 0 16px; }
        .perm-grid label { display: flex; align-items: center; gap: 8px; font-weight: 500; margin: 0; }
        .perm-grid input { width: auto; margin: 0; }
        .role-badges { display: flex; flex-wrap: wrap; gap: 6px; }
        .backup-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 16px; margin-bottom: 16px; }
        .perm-group-head { display: flex; justify-content: space-between; align-items: center; gap: 12px; margin-bottom: 14px; }
        .perm-catalog { display: grid; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); gap: 10px; }
        .perm-item { display: flex; align-items: flex-start; gap: 10px; border: 1px solid #ececec; border-radius: 12px; padding: 12px; font-weight: 500; margin: 0; }
        .perm-item input { width: auto; margin: 4px 0 0; }
        .perm-label { display: block; font-weight: 800; }
        .perm-key { display: inline-block; font-family: ui-monospace, SFMono-Regular, Menlo, monospace; font-size: 11px; color: #b8942d; background: #faf6ea; padding: 2px 8px; border-radius: 999px; }
        .perm-item .perm-key { display: block; background: none; padding: 0; margin-top: 4px; }
        .perm-select-all { display: flex; align-items: center; gap: 8px; margin: 0 !important; }
        .perm-select-all input { width: auto; margin: 0; }
        .content input[type=file] { margin-bottom: 14px; }
        .row-actions .btn, .row-actions button {
            margin: 0; min-height: 32px !important; display: inline-flex; align-items: center; justify-content: center;
            padding: 6px 11px !important; font-size: 0.82rem !important; border-radius: 999px !important;
        }
        tbody tr:hover { background: rgba(212, 175, 55, 0.05); }
        @media (max-width: 700px) { .admin-form-grid { grid-template-columns: 1fr; } .backup-grid { grid-template-columns: 1fr; } }
        @media (max-width: 991.98px) {
            .sidebar { transform: translateX(-100%); }
            body.sidebar-open .sidebar { transform: translateX(0); }
            .sidebar-close { display: flex; align-items: center; justify-content: center; }
            .shell { margin-left: 0; }
            .menu-toggle { display: inline-flex; align-items: center; justify-content: center; }
            .topbar { padding: 12px 14px; }
            .content { padding: 16px; }
            .welcome h1 { font-size: 1.1rem; }
            .top-date { display: none; }
            .profile-chip span { display: none; }
        }
    </style>
    @stack('styles')
</head>
<body>
@php
    $rawLogoPath = (is_object($settings) && method_exists($settings, 'getRawOriginal'))
        ? $settings->getRawOriginal('logo')
        : ($settings->logo ?? null);
    $adminLogo = \App\Support\PublicStorageUrl::fromPath($rawLogoPath) ?? ($settings->logo ?? null);
    $adminUser = auth()->user();
    $adminUser?->loadMissing(['roles', 'role']);
    $can = function (?string $permission = null) use ($adminUser): bool {
        if ($permission === null) {
            return true;
        }
        if ($permission === '__full_admin__') {
            return (bool) $adminUser?->isFullAdmin();
        }

        return (bool) $adminUser?->hasPermission($permission);
    };
    $adminRole = $adminUser?->allRoleNames()->first() ?: ($adminUser?->is_admin ? 'Super Admin' : 'Staff');
    $adminInitial = strtoupper(substr($adminUser?->name ?? 'A', 0, 1));
    $enquiryCount = \Illuminate\Support\Facades\Schema::hasTable('contact_messages') ? \App\Models\ContactMessage::count() : 0;
    $pendingOrderCount = \Illuminate\Support\Facades\Schema::hasTable('orders') ? \App\Models\Order::whereIn('status', ['pending', 'processing'])->count() : 0;
    $lowStockCount = \Illuminate\Support\Facades\Schema::hasTable('products') ? \App\Models\Product::where('is_active', true)->where('stock', '<=', 5)->count() : 0;
    $notifyCount = $pendingOrderCount + $lowStockCount + (int) $enquiryCount;
    $salesDeskOpen = request()->routeIs('admin.orders.*')
        || request()->routeIs('admin.returns.*')
        || request()->routeIs('admin.shifts.*')
        || request()->routeIs('admin.training');
    $reportsOpen = request()->routeIs('admin.reports.*');
    $userSettingsOpen = request()->routeIs('admin.settings.*')
        || request()->routeIs('admin.profile.*')
        || request()->routeIs('admin.roles.*')
        || request()->routeIs('admin.permissions.*')
        || request()->routeIs('admin.user-groups.*')
        || request()->routeIs('admin.users.*')
        || request()->routeIs('admin.backups.*');
    $mainLinks = array_values(array_filter([
        ['label' => 'Dashboard', 'route' => 'admin.dashboard', 'icon' => 'home', 'active' => request()->routeIs('admin.dashboard'), 'permission' => 'view_dashboard'],
        ['label' => 'POS', 'route' => 'admin.pos.index', 'icon' => 'pos', 'active' => request()->routeIs('admin.pos.*'), 'permission' => 'create_sale'],
        ['label' => 'Products', 'route' => 'admin.products.index', 'icon' => 'products', 'active' => request()->routeIs('admin.products.*'), 'permission' => 'manage_products'],
        ['label' => 'Stock Take', 'route' => 'admin.stock-takes.index', 'icon' => 'stocktake', 'active' => request()->routeIs('admin.stock-takes.*'), 'permission' => 'manage_stocktakes'],
        ['label' => 'Categories', 'route' => 'admin.categories.index', 'icon' => 'categories', 'active' => request()->routeIs('admin.categories.*'), 'permission' => 'manage_pos_categories'],
        ['label' => 'Coupons', 'route' => 'admin.coupons.index', 'icon' => 'coupons', 'active' => request()->routeIs('admin.coupons.*'), 'permission' => 'manage_coupons'],
        ['label' => 'Vendors', 'route' => 'admin.vendors.index', 'icon' => 'vendors', 'active' => request()->routeIs('admin.vendors.*'), 'permission' => 'manage_vendors'],
        ['label' => 'Payouts', 'route' => 'admin.vendor-payouts.index', 'icon' => 'payouts', 'active' => request()->routeIs('admin.vendor-payouts.*'), 'permission' => 'manage_vendors'],
        ['label' => 'Blog', 'route' => 'admin.blog.index', 'icon' => 'blog', 'active' => request()->routeIs('admin.blog.*'), 'permission' => 'manage_blog'],
        ['label' => 'Reviews', 'route' => 'admin.reviews.index', 'icon' => 'reviews', 'active' => request()->routeIs('admin.reviews.*'), 'permission' => 'manage_reviews'],
        ['label' => 'Enquiries', 'route' => 'admin.enquiries.index', 'icon' => 'enquiries', 'active' => request()->routeIs('admin.enquiries.*'), 'badge' => $enquiryCount, 'permission' => '__full_admin__'],
        ['label' => 'Subscribers', 'route' => 'admin.newsletter-subscribers.index', 'icon' => 'subscribers', 'active' => request()->routeIs('admin.newsletter-subscribers.*'), 'permission' => '__full_admin__'],
        ['label' => 'Contacts', 'route' => 'admin.contact.index', 'icon' => 'contacts', 'active' => request()->routeIs('admin.contact.*'), 'permission' => '__full_admin__'],
        ['label' => 'Q&A', 'route' => 'admin.questions.index', 'icon' => 'qa', 'active' => request()->routeIs('admin.questions.*'), 'permission' => 'manage_reviews'],
    ], fn ($link) => $can($link['permission'] ?? null)));
    $mainMenuOpen = collect($mainLinks)->contains(fn ($link) => !empty($link['active']));
@endphp

<div class="sidebar-backdrop" id="sidebar-backdrop"></div>
<aside class="sidebar">
    <button type="button" class="sidebar-close" id="sidebar-close" aria-label="Close menu">✕</button>
    <a class="sidebar-brand" href="{{ route('admin.dashboard') }}">
        @if(!empty($adminLogo))
            <img src="{{ $adminLogo }}" alt="{{ $settings->site_name }}" class="brand-logo">
        @else
            <span class="brand-mark">N</span>
        @endif
        <span>
            <span class="brand-title">{{ $settings->site_name }}</span>
            <span class="brand-subtitle">{{ $settings->site_tagline ?: 'Best Quality For You' }}</span>
        </span>
    </a>
    <nav class="sidebar-nav">
        @if(count($mainLinks))
        <button type="button" class="sidebar-toggle" id="main-menu-toggle" aria-expanded="{{ $mainMenuOpen ? 'true' : 'false' }}">
            <span>Main Menu</span>
            <span class="sidebar-caret" id="main-menu-caret" style="transform: rotate({{ $mainMenuOpen ? '180deg' : '0deg' }});">⌄</span>
        </button>
        <div class="sidebar-submenu {{ $mainMenuOpen ? '' : 'is-collapsed' }}" id="main-menu-submenu">
            @foreach($mainLinks as $link)
                <a class="nav-link {{ $link['active'] ? 'is-active' : '' }}" href="{{ route($link['route']) }}">
                    @include('admin.partials.icon', ['name' => $link['icon']])
                    <span>{{ $link['label'] }}</span>
                    @if(!empty($link['badge']))
                        <span class="nav-badge">{{ $link['badge'] }}</span>
                    @endif
                </a>
            @endforeach
        </div>
        @endif

        @if($can('view_sales') || $can('process_return') || $can('create_sale'))
        <button type="button" class="sidebar-toggle" id="sales-desk-toggle" aria-expanded="{{ $salesDeskOpen ? 'true' : 'false' }}">
            <span>Sales Desk</span>
            <span class="sidebar-caret" id="sales-desk-caret" style="transform: rotate({{ $salesDeskOpen ? '180deg' : '0deg' }});">⌄</span>
        </button>
        <div class="sidebar-submenu {{ $salesDeskOpen ? '' : 'is-collapsed' }}" id="sales-desk-submenu">
            @if($can('view_sales'))
            <a class="nav-link {{ request()->routeIs('admin.orders.*') ? 'is-active' : '' }}" href="{{ route('admin.orders.index') }}">
                @include('admin.partials.icon', ['name' => 'orders'])
                <span>Sales History</span>
            </a>
            @endif
            @if($can('process_return'))
            <a class="nav-link {{ request()->routeIs('admin.returns.*') ? 'is-active' : '' }}" href="{{ route('admin.returns.index') }}">
                @include('admin.partials.icon', ['name' => 'orders'])
                <span>Returns</span>
            </a>
            @endif
            @if($can('create_sale') || $can('manage_shifts'))
            <a class="nav-link {{ request()->routeIs('admin.shifts.*') ? 'is-active' : '' }}" href="{{ route('admin.shifts.index') }}">
                @include('admin.partials.icon', ['name' => 'pos'])
                <span>Cashier Shifts</span>
            </a>
            @endif
            @if($can('create_sale'))
            <a class="nav-link {{ request()->routeIs('admin.training') ? 'is-active' : '' }}" href="{{ route('admin.training') }}">
                @include('admin.partials.icon', ['name' => 'qa'])
                <span>Cashier Training</span>
            </a>
            @endif
        </div>
        @endif

        @if($can('view_pos_reports'))
        <button type="button" class="sidebar-toggle" id="reports-toggle" aria-expanded="{{ $reportsOpen ? 'true' : 'false' }}">
            <span>Reports</span>
            <span class="sidebar-caret" id="reports-caret" style="transform: rotate({{ $reportsOpen ? '180deg' : '0deg' }});">⌄</span>
        </button>
        <div class="sidebar-submenu {{ $reportsOpen ? '' : 'is-collapsed' }}" id="reports-submenu">
            <a class="nav-link {{ request()->routeIs('admin.reports.index') && ! request()->filled('preset') ? 'is-active' : '' }}" href="{{ route('admin.reports.index') }}">
                @include('admin.partials.icon', ['name' => 'reports'])
                <span>Sales & Inventory</span>
            </a>
            @if($can('view_cashier_performance'))
            <a class="nav-link {{ request()->routeIs('admin.reports.cashier') ? 'is-active' : '' }}" href="{{ route('admin.reports.cashier') }}">
                @include('admin.partials.icon', ['name' => 'customers'])
                <span>Cashier Performance</span>
            </a>
            @endif
            <a class="nav-link {{ request('preset') === 'today' && request()->routeIs('admin.reports.index') ? 'is-active' : '' }}" href="{{ route('admin.reports.index', ['preset' => 'today']) }}">
                @include('admin.partials.icon', ['name' => 'calendar'])
                <span>Today</span>
            </a>
            <a class="nav-link {{ request('preset') === 'month' && request()->routeIs('admin.reports.index') ? 'is-active' : '' }}" href="{{ route('admin.reports.index', ['preset' => 'month']) }}">
                @include('admin.partials.icon', ['name' => 'calendar'])
                <span>This month</span>
            </a>
            <a class="nav-link" href="{{ route('admin.reports.export', ['format' => 'pdf', 'preset' => 'today']) }}">
                @include('admin.partials.icon', ['name' => 'reports'])
                <span>Export PDF</span>
            </a>
            <a class="nav-link" href="{{ route('admin.reports.export', ['format' => 'excel', 'preset' => 'today']) }}">
                @include('admin.partials.icon', ['name' => 'reports'])
                <span>Export Excel</span>
            </a>
        </div>
        @endif

        <button type="button" class="sidebar-toggle" id="user-settings-toggle" aria-expanded="{{ $userSettingsOpen ? 'true' : 'false' }}">
            <span>User Settings</span>
            <span class="sidebar-caret" id="user-settings-caret" style="transform: rotate({{ $userSettingsOpen ? '180deg' : '0deg' }});">⌄</span>
        </button>
        <div class="sidebar-submenu {{ $userSettingsOpen ? '' : 'is-collapsed' }}" id="user-settings-submenu">
            @if($can('manage_system_settings'))
            <a class="nav-link {{ request()->routeIs('admin.settings.*') ? 'is-active' : '' }}" href="{{ route('admin.settings.edit') }}">
                @include('admin.partials.icon', ['name' => 'settings'])
                <span>Settings</span>
            </a>
            @endif
            <a class="nav-link {{ request()->routeIs('admin.profile.*') ? 'is-active' : '' }}" href="{{ route('admin.profile.edit') }}">
                @include('admin.partials.icon', ['name' => 'profile'])
                <span>Profile</span>
            </a>
            @if($can('backup_database') || $can('restore_database'))
            <a class="nav-link {{ request()->routeIs('admin.backups.*') ? 'is-active' : '' }}" href="{{ route('admin.backups.index') }}">
                @include('admin.partials.icon', ['name' => 'backup'])
                <span>Backup & Restore</span>
            </a>
            @endif
            @if($can('view_users'))
            <a class="nav-link {{ request()->routeIs('admin.users.*') ? 'is-active' : '' }}" href="{{ route('admin.users.index') }}">
                @include('admin.partials.icon', ['name' => 'customers'])
                <span>Users</span>
            </a>
            @endif
            @if($can('view_roles'))
            <a class="nav-link {{ request()->routeIs('admin.roles.*') ? 'is-active' : '' }}" href="{{ route('admin.roles.index') }}">
                @include('admin.partials.icon', ['name' => 'roles'])
                <span>Roles</span>
            </a>
            @endif
            @if($can('view_permissions'))
            <a class="nav-link {{ request()->routeIs('admin.permissions.*') ? 'is-active' : '' }}" href="{{ route('admin.permissions.index') }}">
                @include('admin.partials.icon', ['name' => 'permissions'])
                <span>Permissions</span>
            </a>
            @endif
            @if($can('view_user_groups') || $can('manage_user_groups'))
            <a class="nav-link {{ request()->routeIs('admin.user-groups.*') ? 'is-active' : '' }}" href="{{ route('admin.user-groups.index') }}">
                @include('admin.partials.icon', ['name' => 'groups'])
                <span>User Groups</span>
            </a>
            @endif
        </div>
    </nav>
    <div class="sidebar-foot">
        <a class="visit-store" href="{{ route('home') }}" target="_blank" rel="noopener">
            @include('admin.partials.icon', ['name' => 'store'])
            Visit Store
        </a>
    </div>
</aside>

<div class="shell">
    <header class="topbar">
        <div style="display:flex;align-items:center;gap:10px;min-width:0;">
            <button type="button" class="menu-toggle" id="menu-toggle" aria-label="Open menu">☰</button>
            <div class="welcome">
                <h1>
                    @hasSection('heading')
                        @yield('heading')
                    @elseif(request()->routeIs('admin.dashboard'))
                        Welcome back, {{ $adminUser?->name ?? 'Admin' }} 👋
                    @else
                        @yield('title', 'Admin')
                    @endif
                </h1>
                <p>
                    @hasSection('subheading')
                        @yield('subheading')
                    @elseif(request()->routeIs('admin.dashboard'))
                        Here's what's happening with your store today.
                    @else
                        Manage this section of your store.
                    @endif
                </p>
            </div>
        </div>
        <div class="top-utils">
            <div class="top-date">
                @include('admin.partials.icon', ['name' => 'calendar'])
                {{ now()->format('l, d M Y') }}
            </div>
            <div class="dropdown" id="notify-dropdown">
                <button type="button" class="icon-btn" id="notify-toggle" aria-label="Notifications">
                    @include('admin.partials.icon', ['name' => 'bell'])
                    @if($notifyCount > 0)
                        <span class="dot">{{ $notifyCount > 9 ? '9+' : $notifyCount }}</span>
                    @endif
                </button>
                <div class="dropdown-menu">
                    @if($can('view_sales'))
                    <a href="{{ route('admin.orders.index', ['status' => 'pending']) }}">{{ $pendingOrderCount }} pending orders</a>
                    @endif
                    @if($can('manage_products'))
                    <a href="{{ route('admin.products.index', ['stock' => 'low']) }}">{{ $lowStockCount }} low-stock products</a>
                    @endif
                    @if($can('__full_admin__'))
                    <a href="{{ route('admin.enquiries.index') }}">{{ $enquiryCount }} enquiries</a>
                    @endif
                </div>
            </div>
            <a class="profile-chip" href="{{ route('admin.profile.edit') }}">
                <span class="avatar">{{ $adminInitial }}</span>
                <span>
                    <strong>{{ $adminUser?->name ?? 'Admin' }}</strong>
                    <small>{{ $adminRole }}</small>
                </span>
            </a>
            <form method="POST" action="{{ route('admin.logout') }}" style="margin:0;">
                @csrf
                <button type="submit" class="btn-secondary">Logout</button>
            </form>
        </div>
    </header>

    <main class="content">
        @if(session('success'))
            <div class="alert">{{ session('success') }}</div>
        @endif
        @if($errors->any())
            <div class="alert alert-danger">
                <strong>Please fix the following:</strong>
                <ul style="margin:8px 0 0 18px;">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif
        @yield('content')
    </main>
    <div class="admin-footer">LeviPOS eCommerce Admin Panel v1.0.0 · Made with care for your business.</div>
</div>
<script>
    (function () {
        const body = document.body;
        const menuToggle = document.getElementById('menu-toggle');
        const sidebarClose = document.getElementById('sidebar-close');
        const backdrop = document.getElementById('sidebar-backdrop');
        const notify = document.getElementById('notify-dropdown');
        const notifyToggle = document.getElementById('notify-toggle');

        function closeSidebar() {
            body.classList.remove('sidebar-open');
        }
        function openSidebar() {
            body.classList.add('sidebar-open');
        }
        if (menuToggle) {
            menuToggle.addEventListener('click', function () {
                body.classList.contains('sidebar-open') ? closeSidebar() : openSidebar();
            });
        }
        if (sidebarClose) sidebarClose.addEventListener('click', closeSidebar);
        if (backdrop) backdrop.addEventListener('click', closeSidebar);
        window.addEventListener('resize', function () {
            if (window.innerWidth > 991) closeSidebar();
        });
        document.querySelectorAll('.sidebar a').forEach(function (link) {
            link.addEventListener('click', function () {
                if (window.innerWidth <= 991) closeSidebar();
            });
        });
        function bindSubmenu(toggleId, submenuId, caretId) {
            const toggle = document.getElementById(toggleId);
            const submenu = document.getElementById(submenuId);
            const caret = document.getElementById(caretId);
            if (!toggle || !submenu || !caret) {
                return;
            }
            toggle.addEventListener('click', function () {
                const isCollapsed = submenu.classList.toggle('is-collapsed');
                toggle.setAttribute('aria-expanded', String(!isCollapsed));
                caret.style.transform = isCollapsed ? 'rotate(0deg)' : 'rotate(180deg)';
            });
        }
        bindSubmenu('main-menu-toggle', 'main-menu-submenu', 'main-menu-caret');
        bindSubmenu('sales-desk-toggle', 'sales-desk-submenu', 'sales-desk-caret');
        bindSubmenu('reports-toggle', 'reports-submenu', 'reports-caret');
        bindSubmenu('user-settings-toggle', 'user-settings-submenu', 'user-settings-caret');
        if (notify && notifyToggle) {
            notifyToggle.addEventListener('click', function (event) {
                event.stopPropagation();
                notify.classList.toggle('open');
            });
            document.addEventListener('click', function () {
                notify.classList.remove('open');
            });
        }
        document.addEventListener('keydown', function (event) {
            if (event.key === 'Escape') {
                closeSidebar();
                if (notify) notify.classList.remove('open');
            }
        });
    })();
</script>
@stack('scripts')
</body>
</html>
