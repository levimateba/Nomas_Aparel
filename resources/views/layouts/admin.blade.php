<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>@yield('title', 'Admin panel')</title>
    <link rel="stylesheet" href="{{ asset('css/app.css') }}" />
    <style>
        :root {
            --primary-dark: #16456e;
            --primary-green: #165752;
            --primary-brown: #573c16;
            --primary-accent: #2f6f8e;
            --bg: #f6f7f8;
        }
        body { margin: 0; font-family: Inter, system-ui, -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif; background: var(--bg); }
        .navbar {
            background: linear-gradient(135deg, var(--primary-dark), var(--primary-green));
            color: white;
            padding: 14px 24px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            box-shadow: 0 18px 32px rgba(22, 69, 110, 0.18);
        }
        .brand-wrap { display: flex; align-items: center; gap: 12px; }
        .brand-logo { width: 42px; height: 42px; border-radius: 10px; object-fit: cover; background: rgba(255,255,255,0.12); padding: 4px; }
        .brand-title { font-weight: 700; }
        .brand-subtitle { font-size: 0.85rem; opacity: 0.8; }
        .sidebar {
            width: 220px;
            background: linear-gradient(180deg, #114741 0%, #165752 55%, #1a3e5d 100%);
            height: calc(100vh - 70px);
            position: fixed;
            top: 70px;
            left: 0;
            padding: 18px 12px;
            box-shadow: inset -1px 0 0 rgba(255,255,255,0.06);
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
            background: rgba(255,255,255,0.12);
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
            max-height: 260px;
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
        .content { margin-left: 240px; padding: 24px; }
        .card {
            background: white;
            border-radius: 18px;
            box-shadow: 0 14px 30px rgba(18, 51, 77, 0.08);
            padding: 18px;
            margin-bottom: 18px;
            border: 1px solid rgba(22, 69, 110, 0.05);
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
            color: white;
            background: linear-gradient(135deg, var(--primary-brown), #7b5620);
            padding: 9px 15px;
            cursor: pointer;
            transition: transform 0.2s ease, box-shadow 0.2s ease, opacity 0.2s ease;
        }
        .btn:hover, button:hover, input[type=submit]:hover {
            transform: translateY(-2px);
            box-shadow: 0 12px 20px rgba(87, 60, 22, 0.22);
        }
        .btn-secondary { background: var(--primary-dark); }
        .alert { background: rgba(22, 69, 110, 0.1); color: var(--primary-dark); padding: 10px; border-left: 4px solid var(--primary-green); margin-bottom: 16px; }
        .alert-danger { background: rgba(220, 53, 69, 0.1); color: #8a1f2d; border-left-color: #dc3545; }
        table { width: 100%; border-collapse: separate; border-spacing: 0; }
        th, td { text-align: left; padding: 10px 8px; border-bottom: 1px solid #e5e8eb; }
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
    </style>
</head>
<body>
<nav class="navbar">
    <div class="brand-wrap">
        @if(!empty($settings?->logo))
            <img src="{{ \Illuminate\Support\Facades\Storage::url($settings->logo) }}" alt="{{ $settings->site_name }}" class="brand-logo">
        @endif
        <div>
            <div class="brand-title">{{ $settings->site_name }}</div>
            <div class="brand-subtitle">{{ $settings->site_tagline ?: 'Admin Panel' }}</div>
        </div>
    </div>
    <form method="POST" action="{{ route('admin.logout') }}" style="margin:0;">
        @csrf
        <button type="submit" class="btn btn-secondary">Logout</button>
    </form>
</nav>
@php
    $userSettingsOpen = request()->routeIs('admin.users.*')
        || request()->routeIs('admin.roles.*')
        || request()->routeIs('admin.permissions.*')
        || request()->routeIs('admin.user-groups.*');
@endphp
<div class="sidebar">
    <div class="sidebar-section">Content</div>
    <a href="{{ route('admin.dashboard') }}">Dashboard</a>
    <a href="{{ route('admin.about.index') }}">About</a>
    <a href="{{ route('admin.service.index') }}">Services</a>
    <a href="{{ route('admin.contact.index') }}">Contacts</a>
    <a href="{{ route('admin.blog.index') }}">Blog</a>
    <a href="{{ route('admin.gallery.index') }}">Gallery</a>
    <a href="{{ route('admin.news-events.index') }}">News & Events</a>
    <a href="{{ route('admin.video.index') }}">Videos</a>
    <a href="{{ route('admin.team.index') }}">Team</a>
    <a href="{{ route('admin.price.index') }}">Pricing</a>
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
        const toggle = document.getElementById('user-settings-toggle');
        const submenu = document.getElementById('user-settings-submenu');
        const caret = document.getElementById('user-settings-caret');

        if (!toggle || !submenu || !caret) {
            return;
        }

        toggle.addEventListener('click', function () {
            const isCollapsed = submenu.classList.toggle('is-collapsed');
            toggle.setAttribute('aria-expanded', String(!isCollapsed));
            caret.style.transform = isCollapsed ? 'rotate(0deg)' : 'rotate(180deg)';
        });
    })();
</script>
</body>
</html>
