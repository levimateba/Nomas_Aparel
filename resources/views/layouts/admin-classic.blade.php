<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>@yield('title', 'Admin panel')</title>
    @include('partials.pwa-head', ['pwaContext' => 'admin'])
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
            --admin-font-size: {{ $settings->uiFontSizeCss() }};
        }
        * { box-sizing: border-box; }
        body {
            margin: 0;
            font-family: Inter, system-ui, -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: var(--bg);
            color: #121212;
            font-size: var(--admin-font-size);
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
        .nav-kbd {
            margin-left: auto;
            font-size: 10px;
            font-weight: 800;
            letter-spacing: .04em;
            padding: 2px 6px;
            border-radius: 6px;
            background: rgba(255,255,255,.12);
            color: #d4af37;
        }
        .nav-link.is-active .nav-kbd {
            background: rgba(18,18,18,.15);
            color: #121212;
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
            flex-wrap: wrap;
        }
        .topbar-main {
            display: flex;
            align-items: center;
            gap: 10px;
            min-width: 0;
            flex: 1 1 240px;
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
        .welcome h1 { margin: 0; font-size: 1.35rem; font-weight: 800; line-height: 1.25; }
        .welcome p { margin: 4px 0 0; color: #6b7280; font-size: 13px; line-height: 1.4; }
        .top-utils { display: flex; align-items: center; gap: 12px; flex-wrap: wrap; justify-content: flex-end; flex: 0 1 auto; }
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
        .avatar-btn {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            border: 1px solid #ececec;
            background: #fff !important;
            padding: 0;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            box-shadow: none !important;
            flex: 0 0 40px;
        }
        .avatar-btn .avatar {
            width: 34px;
            height: 34px;
            font-size: 13px;
        }
        .profile-menu-head {
            padding: 10px 12px 8px;
            border-bottom: 1px solid #f3f4f6;
            margin-bottom: 4px;
        }
        .profile-menu-head strong { display: block; font-size: 13px; color: #121212; }
        .profile-menu-head small { display: block; color: #6b7280; font-size: 11px; margin-top: 2px; }
        .profile-menu .logout-btn { color: #991b1b; font-weight: 700; }
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
            .menu-toggle { display: inline-flex; align-items: center; justify-content: center; flex: 0 0 42px; }
            .topbar {
                padding: 12px 14px 10px;
                gap: 8px;
                align-items: center;
                display: grid;
                grid-template-columns: 42px minmax(0, 1fr) auto;
            }
            .topbar-main {
                display: contents;
            }
            .menu-toggle { grid-column: 1; grid-row: 1; }
            .welcome {
                grid-column: 2;
                grid-row: 1;
                min-width: 0;
            }
            .top-utils {
                grid-column: 3;
                grid-row: 1;
                width: auto;
                order: unset;
                justify-content: flex-end;
                gap: 8px;
                padding-top: 0;
                border-top: 0;
                flex-wrap: nowrap;
            }
            .content { padding: 14px; }
            .welcome h1 {
                font-size: 1.15rem;
                white-space: normal;
                overflow: visible;
                text-overflow: unset;
                line-height: 1.2;
            }
            .welcome p { display: none; }
            .top-date { display: none; }
            .admin-footer { padding: 8px 14px 16px; font-size: 11px; }
        }
        @media (max-width: 480px) {
            .welcome h1 { font-size: 1.05rem; }
        }
    </style>
    @stack('styles')
</head>
<body>
@php
    $rawLogoPath = (is_object($settings) && method_exists($settings, 'getRawOriginal'))
        ? $settings->getRawOriginal('logo')
        : ($settings->logo ?? null);
    $adminLogo = $settings->showsLogoOnSidebar()
        ? (\App\Support\PublicStorageUrl::fromPath($rawLogoPath) ?? ($settings->logo ?? null))
        : null;
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
    $adminAvatar = $adminUser?->avatarUrl();
    $enquiryCount = \Illuminate\Support\Facades\Schema::hasTable('contact_messages') ? \App\Models\ContactMessage::count() : 0;
    $pendingOrderCount = \Illuminate\Support\Facades\Schema::hasTable('orders') ? \App\Models\Order::whereIn('status', ['pending', 'processing'])->count() : 0;
    $lowStockCount = \Illuminate\Support\Facades\Schema::hasTable('products') ? \App\Models\Product::where('is_active', true)->where('stock', '<=', 5)->count() : 0;
    $notifyCount = $pendingOrderCount + $lowStockCount + (int) $enquiryCount;
    $salesDeskOpen = request()->routeIs('admin.orders.*')
        || request()->routeIs('admin.returns.*')
        || request()->routeIs('admin.shifts.*')
        || request()->routeIs('admin.holds.*')
        || request()->routeIs('admin.training')
        || request()->routeIs('admin.coupons.*');
    $partnersOpen = request()->routeIs('admin.customers.*')
        || request()->routeIs('admin.suppliers.*')
        || request()->routeIs('admin.vendors.*')
        || request()->routeIs('admin.vendor-payouts.*');
    $financeOpen = request()->routeIs('admin.expenses.*');
    $reportsOpen = request()->routeIs('admin.reports.*');
    $inventoryOpen = request()->routeIs('admin.products.*')
        || request()->routeIs('admin.categories.*')
        || request()->routeIs('admin.stock-takes.*')
        || request()->routeIs('admin.brands.*')
        || request()->routeIs('admin.purchases.*')
        || request()->routeIs('admin.purchase-orders.*')
        || request()->routeIs('admin.stock-overview.*')
        || request()->routeIs('admin.stock-locations.*')
        || request()->routeIs('admin.stock-transfers.*')
        || request()->routeIs('admin.stock-adjustments.*')
        || request()->routeIs('admin.stock-movements.*');
    $administrationOpen = request()->routeIs('admin.users.*')
        || request()->routeIs('admin.roles.*')
        || request()->routeIs('admin.permissions.*')
        || request()->routeIs('admin.user-groups.*')
        || request()->routeIs('admin.backups.*')
        || request()->routeIs('admin.audit-logs.*');
    $cmsOpen = request()->routeIs('admin.blog.*')
        || request()->routeIs('admin.reviews.*')
        || request()->routeIs('admin.questions.*')
        || request()->routeIs('admin.enquiries.*')
        || request()->routeIs('admin.newsletter-subscribers.*')
        || request()->routeIs('admin.contact.*');
    $userSettingsOpen = request()->routeIs('admin.settings.*')
        || request()->routeIs('admin.profile.*');
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
        @if($can('view_dashboard'))
        <a class="nav-link {{ request()->routeIs('admin.dashboard') ? 'is-active' : '' }}" href="{{ route('admin.dashboard') }}">
            @include('admin.partials.icon', ['name' => 'home'])
            <span>Dashboard</span>
        </a>
        @endif

        @if($can('create_sale'))
        <a class="nav-link {{ request()->routeIs('admin.cashier.home') ? 'is-active' : '' }}" href="{{ route('admin.cashier.home') }}">
            @include('admin.partials.icon', ['name' => 'profile'])
            <span>Cashier Home</span>
        </a>
        <a class="nav-link {{ request()->routeIs('admin.pos.index') && ! request()->routeIs('admin.pos.scan') ? 'is-active' : '' }}" href="{{ route('admin.pos.index') }}">
            @include('admin.partials.icon', ['name' => 'pos'])
            <span>POS Terminal</span>
            <span class="nav-kbd">F2</span>
        </a>
        <a class="nav-link {{ request()->routeIs('admin.pos.scan') ? 'is-active' : '' }}" href="{{ route('admin.pos.scan') }}">
            @include('admin.partials.icon', ['name' => 'coupons'])
            <span>Scan &amp; Sell</span>
        </a>
        @endif

        @if($can('manage_products') || $can('manage_pos_categories') || $can('manage_stocktakes') || $can('manage_purchases') || $can('manage_purchase_orders') || $can('view_inventory') || $can('create_stock_transfers') || $can('adjust_stock') || $can('view_stock_movements') || $can('manage_stock_locations'))
        <button type="button" class="sidebar-toggle" id="inventory-toggle" aria-expanded="{{ $inventoryOpen ? 'true' : 'false' }}">
            <span>Inventory</span>
            <span class="sidebar-caret" id="inventory-caret" style="transform: rotate({{ $inventoryOpen ? '180deg' : '0deg' }});">⌄</span>
        </button>
        <div class="sidebar-submenu {{ $inventoryOpen ? '' : 'is-collapsed' }}" id="inventory-submenu">
            @if($can('manage_products'))
            <a class="nav-link {{ request()->routeIs('admin.products.*') ? 'is-active' : '' }}" href="{{ route('admin.products.index') }}">
                @include('admin.partials.icon', ['name' => 'products'])
                <span>Products</span>
            </a>
            @endif
            @if($can('view_inventory'))
            <a class="nav-link {{ request()->routeIs('admin.stock-overview.*') ? 'is-active' : '' }}" href="{{ route('admin.stock-overview.index') }}">
                @include('admin.partials.icon', ['name' => 'stocktake'])
                <span>Stock Overview</span>
            </a>
            @endif
            @if($can('create_stock_transfers'))
            <a class="nav-link {{ request()->routeIs('admin.stock-transfers.*') ? 'is-active' : '' }}" href="{{ route('admin.stock-transfers.index') }}">
                @include('admin.partials.icon', ['name' => 'orders'])
                <span>Stock Transfers</span>
            </a>
            @endif
            @if($can('adjust_stock'))
            <a class="nav-link {{ request()->routeIs('admin.stock-adjustments.*') ? 'is-active' : '' }}" href="{{ route('admin.stock-adjustments.create') }}">
                @include('admin.partials.icon', ['name' => 'stocktake'])
                <span>Stock Adjustments</span>
            </a>
            @endif
            @if($can('view_stock_movements'))
            <a class="nav-link {{ request()->routeIs('admin.stock-movements.*') ? 'is-active' : '' }}" href="{{ route('admin.stock-movements.index') }}">
                @include('admin.partials.icon', ['name' => 'orders'])
                <span>Stock Movements</span>
            </a>
            @endif
            @if($can('manage_stock_locations'))
            <a class="nav-link {{ request()->routeIs('admin.stock-locations.*') ? 'is-active' : '' }}" href="{{ route('admin.stock-locations.index') }}">
                @include('admin.partials.icon', ['name' => 'categories'])
                <span>Inventory Locations</span>
            </a>
            @endif
            @if($can('manage_pos_categories'))
            <a class="nav-link {{ request()->routeIs('admin.categories.*') ? 'is-active' : '' }}" href="{{ route('admin.categories.index') }}">
                @include('admin.partials.icon', ['name' => 'categories'])
                <span>Categories</span>
            </a>
            @endif
            @if($can('manage_products'))
            <a class="nav-link {{ request()->routeIs('admin.brands.*') ? 'is-active' : '' }}" href="{{ route('admin.brands.index') }}">
                @include('admin.partials.icon', ['name' => 'coupons'])
                <span>Brands</span>
            </a>
            @endif
            @if($can('manage_purchases'))
            <a class="nav-link {{ request()->routeIs('admin.purchases.*') ? 'is-active' : '' }}" href="{{ route('admin.purchases.index') }}">
                @include('admin.partials.icon', ['name' => 'stocktake'])
                <span>Purchases / Receive Stock</span>
            </a>
            @endif
            @if($can('manage_stocktakes'))
            <a class="nav-link {{ request()->routeIs('admin.stock-takes.*') ? 'is-active' : '' }}" href="{{ route('admin.stock-takes.index') }}">
                @include('admin.partials.icon', ['name' => 'stocktake'])
                <span>Stocktake</span>
            </a>
            @endif
            @if($can('manage_purchase_orders') || $can('manage_purchases'))
            <a class="nav-link {{ request()->routeIs('admin.purchase-orders.*') ? 'is-active' : '' }}" href="{{ route('admin.purchase-orders.index') }}">
                @include('admin.partials.icon', ['name' => 'orders'])
                <span>Purchase Orders</span>
            </a>
            @endif
        </div>
        @endif

        @if($can('view_sales') || $can('process_return') || $can('create_sale') || $can('manage_coupons') || $can('manage_shifts'))
        <button type="button" class="sidebar-toggle" id="sales-desk-toggle" aria-expanded="{{ $salesDeskOpen ? 'true' : 'false' }}">
            <span>Sales Desk</span>
            <span class="sidebar-caret" id="sales-desk-caret" style="transform: rotate({{ $salesDeskOpen ? '180deg' : '0deg' }});">⌄</span>
        </button>
        <div class="sidebar-submenu {{ $salesDeskOpen ? '' : 'is-collapsed' }}" id="sales-desk-submenu">
            @if($can('create_sale'))
            <a class="nav-link {{ request()->routeIs('admin.holds.*') ? 'is-active' : '' }}" href="{{ route('admin.holds.index') }}">
                @include('admin.partials.icon', ['name' => 'orders'])
                <span>Hold Sales</span>
            </a>
            @endif
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
            @if($can('manage_coupons'))
            <a class="nav-link {{ request()->routeIs('admin.coupons.*') ? 'is-active' : '' }}" href="{{ route('admin.coupons.index') }}">
                @include('admin.partials.icon', ['name' => 'coupons'])
                <span>Coupons</span>
            </a>
            @endif
            @if($can('manage_shifts'))
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

        @if($can('manage_customers') || $can('manage_suppliers') || $can('manage_vendors'))
        <button type="button" class="sidebar-toggle" id="partners-toggle" aria-expanded="{{ $partnersOpen ? 'true' : 'false' }}">
            <span>Partners</span>
            <span class="sidebar-caret" id="partners-caret" style="transform: rotate({{ $partnersOpen ? '180deg' : '0deg' }});">⌄</span>
        </button>
        <div class="sidebar-submenu {{ $partnersOpen ? '' : 'is-collapsed' }}" id="partners-submenu">
            @if($can('manage_customers'))
            <a class="nav-link {{ request()->routeIs('admin.customers.*') ? 'is-active' : '' }}" href="{{ route('admin.customers.index') }}">
                @include('admin.partials.icon', ['name' => 'customers'])
                <span>Customers</span>
            </a>
            @endif
            @if($can('manage_suppliers'))
            <a class="nav-link {{ request()->routeIs('admin.suppliers.*') ? 'is-active' : '' }}" href="{{ route('admin.suppliers.index') }}">
                @include('admin.partials.icon', ['name' => 'vendors'])
                <span>Suppliers</span>
            </a>
            @endif
            @if($can('manage_vendors'))
            <a class="nav-link {{ request()->routeIs('admin.vendors.*') ? 'is-active' : '' }}" href="{{ route('admin.vendors.index') }}">
                @include('admin.partials.icon', ['name' => 'vendors'])
                <span>Marketplace Vendors</span>
            </a>
            <a class="nav-link {{ request()->routeIs('admin.vendor-payouts.*') ? 'is-active' : '' }}" href="{{ route('admin.vendor-payouts.index') }}">
                @include('admin.partials.icon', ['name' => 'payouts'])
                <span>Payouts</span>
            </a>
            @endif
        </div>
        @endif

        @if($can('manage_expenses'))
        <button type="button" class="sidebar-toggle" id="finance-toggle" aria-expanded="{{ $financeOpen ? 'true' : 'false' }}">
            <span>Finance</span>
            <span class="sidebar-caret" id="finance-caret" style="transform: rotate({{ $financeOpen ? '180deg' : '0deg' }});">⌄</span>
        </button>
        <div class="sidebar-submenu {{ $financeOpen ? '' : 'is-collapsed' }}" id="finance-submenu">
            <a class="nav-link {{ request()->routeIs('admin.expenses.*') ? 'is-active' : '' }}" href="{{ route('admin.expenses.index') }}">
                @include('admin.partials.icon', ['name' => 'reports'])
                <span>Expenses</span>
            </a>
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
            <a class="nav-link {{ request()->routeIs('admin.reports.stock-valuation*') ? 'is-active' : '' }}" href="{{ route('admin.reports.stock-valuation') }}">
                @include('admin.partials.icon', ['name' => 'reports'])
                <span>Stock Valuation</span>
            </a>
            <a class="nav-link {{ request()->routeIs('admin.reports.online-sales-by-location') ? 'is-active' : '' }}" href="{{ route('admin.reports.online-sales-by-location') }}">
                @include('admin.partials.icon', ['name' => 'orders'])
                <span>Online Sales by Location</span>
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

        @if($can('view_users') || $can('view_roles') || $can('view_permissions') || $can('view_user_groups') || $can('manage_user_groups') || $can('backup_database') || $can('restore_database') || $can('view_audit_logs'))
        <button type="button" class="sidebar-toggle" id="administration-toggle" aria-expanded="{{ $administrationOpen ? 'true' : 'false' }}">
            <span>Administration</span>
            <span class="sidebar-caret" id="administration-caret" style="transform: rotate({{ $administrationOpen ? '180deg' : '0deg' }});">⌄</span>
        </button>
        <div class="sidebar-submenu {{ $administrationOpen ? '' : 'is-collapsed' }}" id="administration-submenu">
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
            @if($can('view_audit_logs'))
            <a class="nav-link {{ request()->routeIs('admin.audit-logs.*') ? 'is-active' : '' }}" href="{{ route('admin.audit-logs.index') }}">
                @include('admin.partials.icon', ['name' => 'orders'])
                <span>Audit Logs</span>
            </a>
            @endif
            @if($can('backup_database') || $can('restore_database'))
            <a class="nav-link {{ request()->routeIs('admin.backups.*') ? 'is-active' : '' }}" href="{{ route('admin.backups.index') }}">
                @include('admin.partials.icon', ['name' => 'backup'])
                <span>Backup & Restore</span>
            </a>
            @endif
        </div>
        @endif

        <button type="button" class="sidebar-toggle" id="user-settings-toggle" aria-expanded="{{ $userSettingsOpen ? 'true' : 'false' }}">
            <span>Settings</span>
            <span class="sidebar-caret" id="user-settings-caret" style="transform: rotate({{ $userSettingsOpen ? '180deg' : '0deg' }});">⌄</span>
        </button>
        <div class="sidebar-submenu {{ $userSettingsOpen ? '' : 'is-collapsed' }}" id="user-settings-submenu">
            @if($can('manage_system_settings'))
            <a class="nav-link {{ request()->routeIs('admin.settings.*') ? 'is-active' : '' }}" href="{{ route('admin.settings.edit') }}">
                @include('admin.partials.icon', ['name' => 'settings'])
                <span>Store Settings</span>
            </a>
            @endif
            @if($can('manage_loyalty'))
            <a class="nav-link {{ request()->routeIs('admin.loyalty.settings*') ? 'is-active' : '' }}" href="{{ route('admin.loyalty.settings') }}">
                @include('admin.partials.icon', ['name' => 'settings'])
                <span>Customer Loyalty</span>
            </a>
            <a class="nav-link {{ request()->routeIs('admin.loyalty.dashboard') || request()->routeIs('admin.loyalty.history') ? 'is-active' : '' }}" href="{{ route('admin.loyalty.dashboard') }}">
                @include('admin.partials.icon', ['name' => 'customers'])
                <span>Loyalty Dashboard</span>
            </a>
            @endif
            <a class="nav-link {{ request()->routeIs('admin.profile.*') ? 'is-active' : '' }}" href="{{ route('admin.profile.edit') }}">
                @include('admin.partials.icon', ['name' => 'profile'])
                <span>Profile</span>
            </a>
        </div>

        @if($can('manage_blog') || $can('manage_reviews') || $can('manage_website'))
        <button type="button" class="sidebar-toggle" id="cms-toggle" aria-expanded="{{ $cmsOpen ? 'true' : 'false' }}">
            <span>Website</span>
            <span class="sidebar-caret" id="cms-caret" style="transform: rotate({{ $cmsOpen ? '180deg' : '0deg' }});">⌄</span>
        </button>
        <div class="sidebar-submenu {{ $cmsOpen ? '' : 'is-collapsed' }}" id="cms-submenu">
            @if($can('manage_blog'))
            <a class="nav-link {{ request()->routeIs('admin.blog.*') ? 'is-active' : '' }}" href="{{ route('admin.blog.index') }}">
                @include('admin.partials.icon', ['name' => 'blog'])
                <span>Blog</span>
            </a>
            @endif
            @if($can('manage_reviews'))
            <a class="nav-link {{ request()->routeIs('admin.reviews.*') ? 'is-active' : '' }}" href="{{ route('admin.reviews.index') }}">
                @include('admin.partials.icon', ['name' => 'reviews'])
                <span>Reviews</span>
            </a>
            <a class="nav-link {{ request()->routeIs('admin.questions.*') ? 'is-active' : '' }}" href="{{ route('admin.questions.index') }}">
                @include('admin.partials.icon', ['name' => 'qa'])
                <span>Q&amp;A</span>
            </a>
            @endif
            @if($can('manage_website'))
            <a class="nav-link {{ request()->routeIs('admin.enquiries.*') ? 'is-active' : '' }}" href="{{ route('admin.enquiries.index') }}">
                @include('admin.partials.icon', ['name' => 'enquiries'])
                <span>Enquiries</span>
                @if($enquiryCount > 0)<span class="nav-badge">{{ $enquiryCount }}</span>@endif
            </a>
            <a class="nav-link {{ request()->routeIs('admin.newsletter-subscribers.*') ? 'is-active' : '' }}" href="{{ route('admin.newsletter-subscribers.index') }}">
                @include('admin.partials.icon', ['name' => 'subscribers'])
                <span>Subscribers</span>
            </a>
            <a class="nav-link {{ request()->routeIs('admin.contact.*') ? 'is-active' : '' }}" href="{{ route('admin.contact.index') }}">
                @include('admin.partials.icon', ['name' => 'contacts'])
                <span>Contacts</span>
            </a>
            @endif
        </div>
        @endif
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
        <div class="topbar-main">
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
            <div class="dropdown" id="profile-dropdown">
                <button type="button" class="avatar-btn" id="profile-toggle" aria-label="Account menu" aria-haspopup="true" aria-expanded="false">
                    @if(!empty($adminAvatar))
                        <img src="{{ $adminAvatar }}" alt="" class="avatar" style="object-fit:cover;padding:0;">
                    @else
                        <span class="avatar">{{ $adminInitial }}</span>
                    @endif
                </button>
                <div class="dropdown-menu profile-menu">
                    <div class="profile-menu-head">
                        <strong>{{ $adminUser?->name ?? 'Admin' }}</strong>
                        <small>{{ $adminRole }}</small>
                    </div>
                    <a href="{{ route('admin.profile.edit') }}">My profile</a>
                    <form method="POST" action="{{ route('admin.logout') }}" style="margin:0;">
                        @csrf
                        <button type="submit" class="logout-btn">Logout</button>
                    </form>
                </div>
            </div>
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
        const profile = document.getElementById('profile-dropdown');
        const profileToggle = document.getElementById('profile-toggle');

        function closeDropdowns(except) {
            [notify, profile].forEach(function (node) {
                if (node && node !== except) {
                    node.classList.remove('open');
                }
            });
            if (profileToggle) {
                profileToggle.setAttribute('aria-expanded', profile && profile.classList.contains('open') ? 'true' : 'false');
            }
        }

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
        bindSubmenu('inventory-toggle', 'inventory-submenu', 'inventory-caret');
        bindSubmenu('sales-desk-toggle', 'sales-desk-submenu', 'sales-desk-caret');
        bindSubmenu('partners-toggle', 'partners-submenu', 'partners-caret');
        bindSubmenu('finance-toggle', 'finance-submenu', 'finance-caret');
        bindSubmenu('reports-toggle', 'reports-submenu', 'reports-caret');
        bindSubmenu('administration-toggle', 'administration-submenu', 'administration-caret');
        bindSubmenu('user-settings-toggle', 'user-settings-submenu', 'user-settings-caret');
        bindSubmenu('cms-toggle', 'cms-submenu', 'cms-caret');
        document.addEventListener('keydown', function (event) {
            if (event.key === 'F2') {
                const tag = (event.target && event.target.tagName) ? event.target.tagName.toLowerCase() : '';
                if (['input', 'textarea', 'select'].includes(tag) || event.target?.isContentEditable) {
                    return;
                }
                event.preventDefault();
                window.location.href = @json(route('admin.pos.index'));
            }
        });
        if (notify && notifyToggle) {
            notifyToggle.addEventListener('click', function (event) {
                event.stopPropagation();
                const willOpen = !notify.classList.contains('open');
                closeDropdowns(willOpen ? notify : null);
                notify.classList.toggle('open');
            });
        }
        if (profile && profileToggle) {
            profileToggle.addEventListener('click', function (event) {
                event.stopPropagation();
                const willOpen = !profile.classList.contains('open');
                closeDropdowns(willOpen ? profile : null);
                profile.classList.toggle('open');
                profileToggle.setAttribute('aria-expanded', profile.classList.contains('open') ? 'true' : 'false');
            });
        }
        document.addEventListener('click', function () {
            closeDropdowns(null);
        });
        document.addEventListener('keydown', function (event) {
            if (event.key === 'Escape') {
                closeSidebar();
                closeDropdowns(null);
            }
        });
    })();
</script>
@stack('scripts')
</body>
</html>
