<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>@yield('title', 'Admin panel')</title>
    <link rel="stylesheet" href="{{ asset('css/app.css') }}" />
    <style>
        :root {
            --primary-dark: #121212;
            --primary-green: #d4af37;
            --primary-brown: #b8942d;
            --primary-accent: #1f1f1f;
            --bg: #ffffff;
            --admin-nav-height: 70px;
        }
        body {
            margin: 0;
            font-family: Inter, system-ui, -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
            background: var(--bg);
            padding-top: var(--admin-nav-height);
        }
        .navbar {
            background: linear-gradient(135deg, #000000, #1a1a1a);
            color: white;
            padding: 14px 24px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            box-shadow: 0 18px 32px rgba(22, 69, 110, 0.18);
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            z-index: 1200;
        }
        .nav-left {
            display: flex;
            align-items: center;
            gap: 12px;
        }
        .menu-toggle {
            display: none;
            width: 42px;
            height: 42px;
            border-radius: 12px;
            border: 1px solid rgba(255, 255, 255, 0.2);
            background: rgba(255, 255, 255, 0.12);
            color: #fff;
            align-items: center;
            justify-content: center;
            font-size: 1.3rem;
            line-height: 1;
            padding: 0;
        }
        .menu-toggle:hover {
            transform: none;
            box-shadow: none;
            background: rgba(255, 255, 255, 0.2);
        }
        .brand-wrap { display: flex; align-items: center; gap: 12px; }
        .brand-logo { width: 42px; height: 42px; border-radius: 10px; object-fit: cover; background: rgba(255,255,255,0.12); padding: 4px; }
        .brand-title { font-weight: 700; }
        .brand-subtitle { font-size: 0.85rem; opacity: 0.8; }
        .sidebar {
            width: 220px;
            background: linear-gradient(180deg, #0f0f0f 0%, #181818 55%, #202020 100%);
            height: calc(100vh - var(--admin-nav-height));
            position: fixed;
            top: var(--admin-nav-height);
            left: 0;
            padding: 18px 12px;
            box-shadow: inset -1px 0 0 rgba(255,255,255,0.06);
            overflow-y: auto;
            z-index: 1160;
            transition: transform 0.25s ease;
        }
        .sidebar a {
            display: block;
            padding: 12px 16px;
            margin-bottom: 8px;
            color: white;
            text-decoration: none;
            border-radius: 14px;
            transition: transform 0.22s ease, background 0.22s ease, box-shadow 0.22s ease;
        }
        .sidebar a:hover {
            background: rgba(212,175,55,0.16);
            transform: translateX(6px);
            box-shadow: 0 12px 20px rgba(0,0,0,0.16);
        }
        .sidebar-section {
            padding: 10px 16px 6px;
            color: rgba(255,255,255,0.68);
            font-size: 0.78rem;
            font-weight: 700;
            letter-spacing: 0.12em;
            text-transform: uppercase;
        }
        .sidebar-toggle {
            width: 100%;
            text-align: left;
            background: transparent;
            box-shadow: none;
            padding: 10px 16px 6px;
            color: rgba(255,255,255,0.78);
            font-size: 0.78rem;
            font-weight: 700;
            letter-spacing: 0.12em;
            text-transform: uppercase;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }
        .sidebar-toggle:hover {
            transform: none;
            background: transparent;
            box-shadow: none;
            color: #fff;
        }
        .sidebar-caret {
            transition: transform 0.2s ease;
            font-size: 0.9rem;
        }
        .sidebar-submenu {
            margin: 4px 0 10px 10px;
            padding-left: 10px;
            border-left: 1px solid rgba(255,255,255,0.14);
            overflow: hidden;
            max-height: 1800px;
            opacity: 1;
            transition: max-height 0.25s ease, opacity 0.2s ease;
        }
        .sidebar-submenu a {
            padding: 10px 14px;
            margin-bottom: 6px;
            font-size: 0.95rem;
            border-radius: 12px;
            background: rgba(255,255,255,0.04);
        }
        .sidebar-submenu.is-collapsed {
            max-height: 0;
            opacity: 0;
            margin-bottom: 0;
        }
        .sidebar-submenu.is-collapsed + .sidebar-spacer {
            display: none;
        }
        .sidebar-close {
            display: none;
            width: 34px;
            height: 34px;
            border-radius: 10px;
            border: 1px solid rgba(255,255,255,0.2);
            background: rgba(255,255,255,0.12);
            color: #fff;
            align-items: center;
            justify-content: center;
            font-size: 1.1rem;
            padding: 0;
            margin: 0 0 10px auto;
        }
        .sidebar-close:hover {
            transform: none;
            box-shadow: none;
        }
        .sidebar-backdrop {
            display: none;
            position: fixed;
            inset: 0;
            background: rgba(12, 24, 36, 0.46);
            z-index: 1150;
        }
        body.sidebar-open .sidebar-backdrop {
            display: block;
        }
        .content { margin-left: 240px; padding: 24px; }
        .card {
            background: white;
            border-radius: 18px;
            box-shadow: 0 12px 28px rgba(0, 0, 0, 0.08);
            padding: 18px;
            margin-bottom: 18px;
            border: 1px solid rgba(212, 175, 55, 0.25);
            transition: transform 0.22s ease, box-shadow 0.22s ease;
            animation: adminFadeIn 0.35s ease-out;
        }
        .card:hover {
            transform: translateY(-4px);
            box-shadow: 0 22px 38px rgba(18, 51, 77, 0.12);
        }
        .btn, button, input[type=submit] {
            border: none;
            border-radius: 999px;
            color: #121212;
            background: linear-gradient(135deg, #d4af37, #b8942d);
            padding: 9px 15px;
            cursor: pointer;
            transition: transform 0.2s ease, box-shadow 0.2s ease, opacity 0.2s ease;
        }
        .btn:hover, button:hover, input[type=submit]:hover {
            transform: translateY(-2px);
            box-shadow: 0 12px 20px rgba(212, 175, 55, 0.25);
        }
        .btn-secondary { background: #121212; color: #ffffff; }
        .alert { background: rgba(212, 175, 55, 0.16); color: #121212; padding: 10px; border-left: 4px solid var(--primary-green); margin-bottom: 16px; }
        .alert-danger { background: rgba(220, 53, 69, 0.1); color: #8a1f2d; border-left-color: #dc3545; }
        table { width: 100%; border-collapse: separate; border-spacing: 0; }
        th, td { text-align: left; padding: 10px 8px; border-bottom: 1px solid #e5e8eb; }
        /* Treat the last table column as actions for admin index pages. */
        .card table td:last-child {
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            gap: 8px;
            white-space: normal;
        }
        .card table td:last-child .btn {
            margin: 0;
            min-height: 32px !important;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 6px 11px !important;
            font-size: 0.82rem !important;
            border-radius: 999px !important;
            line-height: 1.2;
        }
        .card table td:last-child form {
            display: inline-flex !important;
            margin: 0;
        }
        .card table td:last-child form .btn {
            width: 100%;
        }
        tr {
            transition: background 0.18s ease, transform 0.18s ease;
        }
        tbody tr:hover {
            background: rgba(22, 87, 82, 0.04);
        }
        @keyframes adminFadeIn {
            from {
                opacity: 0;
                transform: translateY(10px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        @media (max-width: 991.98px) {
            body {
                padding-top: 64px;
            }
            .navbar {
                padding: 10px 14px;
            }
            .menu-toggle {
                display: inline-flex;
            }
            .brand-title {
                font-size: 0.98rem;
            }
            .brand-subtitle {
                font-size: 0.75rem;
            }
            .brand-logo {
                width: 36px;
                height: 36px;
            }
            .sidebar {
                width: min(84vw, 290px);
                top: 0;
                height: 100vh;
                transform: translateX(-100%);
                padding-top: 12px;
            }
            body.sidebar-open .sidebar {
                transform: translateX(0);
            }
            .sidebar-close {
                display: inline-flex;
            }
            .content {
                margin-left: 0;
                padding: 16px;
            }
            .card {
                overflow-x: auto;
            }
            .card table td:last-child {
                min-width: 170px;
            }
        }
        @media (max-width: 576px) {
            .card table td:last-child .btn,
            .card table td:last-child form {
                width: 100%;
            }
        }
    </style>
</head>
<body>
@php
    $rawLogoPath = (is_object($settings) && method_exists($settings, 'getRawOriginal'))
        ? $settings->getRawOriginal('logo')
        : ($settings->logo ?? null);
    $adminLogo = \App\Support\PublicStorageUrl::fromPath($rawLogoPath) ?? ($settings->logo ?? null);
@endphp
<nav class="navbar">
    <div class="nav-left">
        <button type="button" class="menu-toggle" id="menu-toggle" aria-label="Open menu" aria-expanded="false">☰</button>
        <div class="brand-wrap">
            @if(!empty($adminLogo))
                <img src="{{ $adminLogo }}" alt="{{ $settings->site_name }}" class="brand-logo">
            @endif
            <div>
                <div class="brand-title">{{ $settings->site_name }}</div>
                <div class="brand-subtitle">{{ $settings->site_tagline ?: 'Admin Panel' }}</div>
            </div>
        </div>
    </div>
    <form method="POST" action="{{ route('admin.logout') }}" style="margin:0;">
        @csrf
        <button type="submit" class="btn btn-secondary">Logout</button>
    </form>
</nav>
@php
    $contentOpen = request()->routeIs('admin.dashboard')
        || request()->routeIs('admin.about.*')
        || request()->routeIs('admin.service.*')
        || request()->routeIs('admin.contact.*')
        || request()->routeIs('admin.enquiries.*')
        || request()->routeIs('admin.newsletter-subscribers.*')
        || request()->routeIs('admin.categories.*')
        || request()->routeIs('admin.vendors.*')
        || request()->routeIs('admin.vendor-payouts.*')
        || request()->routeIs('admin.products.*')
        || request()->routeIs('admin.coupons.*')
        || request()->routeIs('admin.reviews.*')
        || request()->routeIs('admin.questions.*')
        || request()->routeIs('admin.reports.*')
        || request()->routeIs('admin.orders.*')
        || request()->routeIs('admin.blog.*')
        || request()->routeIs('admin.gallery.*')
        || request()->routeIs('admin.quotations.*')
        || request()->routeIs('admin.quotes.*')
        || request()->routeIs('admin.news-events.*')
        || request()->routeIs('admin.video.*')
        || request()->routeIs('admin.team.*')
        || request()->routeIs('admin.price.*');
    $userSettingsOpen = request()->routeIs('admin.users.*')
        || request()->routeIs('admin.roles.*')
        || request()->routeIs('admin.permissions.*')
        || request()->routeIs('admin.user-groups.*');
@endphp
<div class="sidebar-backdrop" id="sidebar-backdrop"></div>
<div class="sidebar">
    <button type="button" class="sidebar-close" id="sidebar-close" aria-label="Close menu">✕</button>
    <button type="button" class="sidebar-toggle" id="content-toggle" aria-expanded="{{ $contentOpen ? 'true' : 'false' }}">
        <span>Content</span>
        <span class="sidebar-caret" id="content-caret" style="transform: rotate({{ $contentOpen ? '180deg' : '0deg' }});">⌄</span>
    </button>
    <div class="sidebar-submenu {{ $contentOpen ? '' : 'is-collapsed' }}" id="content-submenu">
        <a href="{{ route('admin.dashboard') }}" style="{{ request()->routeIs('admin.dashboard') ? 'background:rgba(255,255,255,0.15);' : '' }}">Dashboard</a>
        <a href="{{ route('admin.about.index') }}" style="{{ request()->routeIs('admin.about.*') ? 'background:rgba(255,255,255,0.15);' : '' }}">About</a>
        <a href="{{ route('admin.service.index') }}" style="{{ request()->routeIs('admin.service.*') ? 'background:rgba(255,255,255,0.15);' : '' }}">Services</a>
        <a href="{{ route('admin.contact.index') }}" style="{{ request()->routeIs('admin.contact.*') ? 'background:rgba(255,255,255,0.15);' : '' }}">Contacts</a>
        <a href="{{ route('admin.enquiries.index') }}" style="{{ request()->routeIs('admin.enquiries.*') ? 'background:rgba(255,255,255,0.15);' : '' }}">
            Enquiries
            @php $enquiryCount = \App\Models\ContactMessage::count(); @endphp
            @if($enquiryCount > 0)
                <span style="float:right;background:#c9a227;color:#121212;border-radius:999px;padding:1px 9px;font-size:0.75rem;font-weight:700;">{{ $enquiryCount }}</span>
            @endif
        </a>
        <a href="{{ route('admin.newsletter-subscribers.index') }}" style="{{ request()->routeIs('admin.newsletter-subscribers.*') ? 'background:rgba(255,255,255,0.15);' : '' }}">
            Subscribers
            @php $subscriberCount = \App\Models\NewsletterSubscriber::count(); @endphp
            @if($subscriberCount > 0)
                <span style="float:right;background:#7c3aed;color:#fff;border-radius:999px;padding:1px 9px;font-size:0.75rem;font-weight:700;">{{ $subscriberCount }}</span>
            @endif
        </a>
        <a href="{{ route('admin.categories.index') }}" style="{{ request()->routeIs('admin.categories.*') ? 'background:rgba(255,255,255,0.15);' : '' }}">Categories</a>
        <a href="{{ route('admin.vendors.index') }}" style="{{ request()->routeIs('admin.vendors.*') ? 'background:rgba(255,255,255,0.15);' : '' }}">Vendors</a>
        <a href="{{ route('admin.vendor-payouts.index') }}" style="{{ request()->routeIs('admin.vendor-payouts.*') ? 'background:rgba(255,255,255,0.15);' : '' }}">Payouts</a>
        <a href="{{ route('admin.products.index') }}" style="{{ request()->routeIs('admin.products.*') ? 'background:rgba(255,255,255,0.15);' : '' }}">Products</a>
        <a href="{{ route('admin.coupons.index') }}" style="{{ request()->routeIs('admin.coupons.*') ? 'background:rgba(255,255,255,0.15);' : '' }}">Coupons</a>
        <a href="{{ route('admin.reviews.index') }}" style="{{ request()->routeIs('admin.reviews.*') ? 'background:rgba(255,255,255,0.15);' : '' }}">Reviews</a>
        <a href="{{ route('admin.questions.index') }}" style="{{ request()->routeIs('admin.questions.*') ? 'background:rgba(255,255,255,0.15);' : '' }}">Q&A</a>
        <a href="{{ route('admin.reports.index') }}" style="{{ request()->routeIs('admin.reports.*') ? 'background:rgba(255,255,255,0.15);' : '' }}">Reports</a>
        <a href="{{ route('admin.orders.index') }}" style="{{ request()->routeIs('admin.orders.*') ? 'background:rgba(255,255,255,0.15);' : '' }}">Orders</a>
        <a href="{{ route('admin.blog.index') }}" style="{{ request()->routeIs('admin.blog.*') ? 'background:rgba(255,255,255,0.15);' : '' }}">Blog</a>
        <a href="{{ route('admin.gallery.index') }}" style="{{ request()->routeIs('admin.gallery.*') ? 'background:rgba(255,255,255,0.15);' : '' }}">Gallery</a>
        <a href="{{ route('admin.quotations.index') }}" style="{{ request()->routeIs('admin.quotations.*') ? 'background:rgba(255,255,255,0.15);' : '' }}">Quotations</a>
        <a href="{{ route('admin.quotes.index') }}" style="{{ request()->routeIs('admin.quotes.*') ? 'background:rgba(255,255,255,0.15);' : '' }}">
            Quote Requests
            @php $newQuotes = \App\Models\Quote::where('status','new')->count(); @endphp
            @if($newQuotes > 0)
                <span style="float:right;background:#2196f3;color:#fff;border-radius:999px;padding:1px 9px;font-size:0.75rem;font-weight:700;">{{ $newQuotes }}</span>
            @endif
        </a>
        <a href="{{ route('admin.news-events.index') }}" style="{{ request()->routeIs('admin.news-events.*') ? 'background:rgba(255,255,255,0.15);' : '' }}">News & Events</a>
        <a href="{{ route('admin.video.index') }}" style="{{ request()->routeIs('admin.video.*') ? 'background:rgba(255,255,255,0.15);' : '' }}">Videos</a>
        <a href="{{ route('admin.team.index') }}" style="{{ request()->routeIs('admin.team.*') ? 'background:rgba(255,255,255,0.15);' : '' }}">Team</a>
        <a href="{{ route('admin.price.index') }}" style="{{ request()->routeIs('admin.price.*') ? 'background:rgba(255,255,255,0.15);' : '' }}">Pricing</a>
    </div>
    <button type="button" class="sidebar-toggle" id="user-settings-toggle" aria-expanded="{{ $userSettingsOpen ? 'true' : 'false' }}">
        <span>User Settings</span>
        <span class="sidebar-caret" id="user-settings-caret" style="transform: rotate({{ $userSettingsOpen ? '180deg' : '0deg' }});">⌄</span>
    </button>
    <div class="sidebar-submenu {{ $userSettingsOpen ? '' : 'is-collapsed' }}" id="user-settings-submenu">
        <a href="{{ route('admin.users.index') }}">Users</a>
        <a href="{{ route('admin.roles.index') }}">Roles</a>
        <a href="{{ route('admin.permissions.index') }}">Permissions</a>
        <a href="{{ route('admin.user-groups.index') }}">User Groups</a>
    </div>
</div>
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
<script>
    (function () {
        const body = document.body;
        const menuToggle = document.getElementById('menu-toggle');
        const sidebarClose = document.getElementById('sidebar-close');
        const backdrop = document.getElementById('sidebar-backdrop');

        function closeSidebar() {
            body.classList.remove('sidebar-open');
            if (menuToggle) {
                menuToggle.setAttribute('aria-expanded', 'false');
            }
        }

        function openSidebar() {
            body.classList.add('sidebar-open');
            if (menuToggle) {
                menuToggle.setAttribute('aria-expanded', 'true');
            }
        }

        if (menuToggle) {
            menuToggle.addEventListener('click', function () {
                if (body.classList.contains('sidebar-open')) {
                    closeSidebar();
                    return;
                }
                openSidebar();
            });
        }

        if (sidebarClose) {
            sidebarClose.addEventListener('click', closeSidebar);
        }

        if (backdrop) {
            backdrop.addEventListener('click', closeSidebar);
        }

        window.addEventListener('resize', function () {
            if (window.innerWidth > 991) {
                closeSidebar();
            }
        });

        document.querySelectorAll('.sidebar a').forEach(function (link) {
            link.addEventListener('click', function () {
                if (window.innerWidth <= 991) {
                    closeSidebar();
                }
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

        bindSubmenu('content-toggle', 'content-submenu', 'content-caret');
        bindSubmenu('user-settings-toggle', 'user-settings-submenu', 'user-settings-caret');

        document.addEventListener('keydown', function (event) {
            if (event.key === 'Escape') {
                closeSidebar();
            }
        });

        if (window.innerWidth > 991) {
            body.classList.remove('sidebar-open');
            if (menuToggle) {
                menuToggle.setAttribute('aria-expanded', 'false');
            }
        }
    })();
</script>
</body>
</html>
