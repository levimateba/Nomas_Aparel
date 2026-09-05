<!doctype html>
<html lang="en" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login — {{ $settings->displayName() }}</title>
    @include('partials.pwa-head', ['pwaContext' => 'admin'])
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@400;500;600;700;800&family=Instrument+Serif:ital@0;1&display=swap" rel="stylesheet">
    <style>
        :root {
            --gold: #a58112;
            --gold-soft: #c9a227;
            --ink: #0b0f14;
            --ink-soft: #1a1f2a;
            --panel: #f4f2ee;
            --card: #ffffff;
            --text: #12151c;
            --muted: #6b7280;
            --line: #e5e2db;
        }
        html.dark {
            --panel: #0b0f14;
            --card: #141820;
            --text: #f5f5f4;
            --muted: #9ca3af;
            --line: #2a303c;
        }
        * { box-sizing: border-box; }
        html, body { height: 100%; margin: 0; }
        body {
            font-family: Outfit, ui-sans-serif, system-ui, sans-serif;
            color: var(--text);
            background: var(--panel);
            -webkit-font-smoothing: antialiased;
        }
        a { color: inherit; text-decoration: none; }
        button, input { font: inherit; }

        .login-page {
            min-height: 100%;
            display: grid;
            grid-template-columns: 1fr;
        }

        /* ——— Brand panel ——— */
        .brand-panel {
            position: relative;
            color: #fff;
            overflow: hidden;
            background:
                linear-gradient(165deg, rgba(8,10,14,.88) 0%, rgba(8,10,14,.72) 45%, rgba(12,14,20,.92) 100%),
                url('{{ asset('images/login-hero.jpg') }}') center / cover no-repeat;
            min-height: 42vh;
            padding: 28px 22px 48px;
            display: flex;
            flex-direction: column;
            gap: 28px;
        }
        .brand-panel::after {
            content: '';
            position: absolute;
            left: 0; right: 0; bottom: -1px;
            height: 28px;
            background: linear-gradient(135deg, transparent 48%, var(--gold) 48.5%, var(--gold) 51.5%, var(--card) 52%);
            pointer-events: none;
        }
        .brand-top { display: flex; align-items: center; gap: 14px; }
        .brand-logo {
            width: 56px; height: 56px; border-radius: 14px; object-fit: cover;
            border: 1px solid rgba(165,129,18,.45); background: rgba(0,0,0,.25);
            flex: 0 0 auto;
        }
        .brand-logo-fallback {
            width: 56px; height: 56px; border-radius: 14px; flex: 0 0 auto;
            display: grid; place-items: center;
            background: linear-gradient(145deg, var(--gold), #7a5f0c);
            color: #111; font-weight: 800; font-size: 22px;
            box-shadow: 0 8px 24px rgba(165,129,18,.35);
        }
        .brand-name {
            font-size: 13px; font-weight: 800; letter-spacing: .08em; text-transform: uppercase; line-height: 1.25;
        }
        .brand-tag {
            margin-top: 3px; font-size: 12px; color: var(--gold-soft); font-weight: 600;
        }
        .brand-copy { margin-top: auto; max-width: 520px; }
        .brand-kicker {
            margin: 0 0 8px; font-size: 28px; font-weight: 800; letter-spacing: -.02em; line-height: 1.15;
        }
        .brand-sub {
            margin: 0; font-size: 16px; font-weight: 700; color: var(--gold-soft);
        }
        .brand-lead {
            display: none; margin: 10px 0 0; color: rgba(255,255,255,.78); font-size: 15px; line-height: 1.55;
        }
        .feature-row {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 10px;
            margin-top: 22px;
        }
        .feature {
            text-align: center;
            padding: 10px 6px;
        }
        .feature-icon {
            width: 40px; height: 40px; margin: 0 auto 8px;
            border-radius: 12px; border: 1px solid rgba(165,129,18,.45);
            background: rgba(165,129,18,.12);
            display: grid; place-items: center; color: var(--gold-soft);
        }
        .feature-icon svg { width: 18px; height: 18px; }
        .feature strong { display: block; font-size: 12px; font-weight: 700; }
        .feature span { display: block; margin-top: 2px; font-size: 11px; color: rgba(255,255,255,.62); }
        .brand-quote {
            display: none;
            margin-top: 28px;
            padding-top: 18px;
            border-top: 1px solid rgba(165,129,18,.35);
            font-family: 'Instrument Serif', Georgia, serif;
            font-style: italic;
            font-size: 20px;
            color: rgba(255,255,255,.92);
        }
        .brand-foot {
            display: none;
            margin-top: auto;
            padding-top: 20px;
            gap: 18px;
            font-size: 12px;
            color: rgba(255,255,255,.7);
        }
        .brand-foot-item { display: flex; align-items: flex-start; gap: 10px; }
        .brand-foot-item svg { width: 16px; height: 16px; color: var(--gold-soft); flex: 0 0 auto; margin-top: 1px; }
        .brand-foot-item strong { display: block; color: #fff; font-size: 12px; font-weight: 700; }
        .brand-copy-right {
            display: none;
            margin-top: 18px;
            font-size: 11px;
            color: rgba(255,255,255,.45);
        }

        /* ——— Form panel ——— */
        .form-panel {
            position: relative;
            background: var(--panel);
            padding: 0 18px 28px;
            display: flex;
            flex-direction: column;
            align-items: center;
        }
        .theme-toggle {
            position: absolute;
            top: 14px; right: 16px;
            z-index: 2;
            display: inline-flex;
            align-items: center;
            gap: 2px;
            padding: 4px;
            border-radius: 999px;
            border: 1px solid var(--line);
            background: var(--card);
        }
        .theme-toggle button {
            width: 34px; height: 30px; border: 0; border-radius: 999px;
            background: transparent; color: var(--muted); cursor: pointer;
            display: grid; place-items: center;
        }
        .theme-toggle button.is-active {
            background: rgba(165,129,18,.14); color: var(--gold);
        }
        .theme-toggle svg { width: 15px; height: 15px; }

        .login-card {
            width: 100%;
            max-width: 420px;
            margin-top: -18px;
            background: var(--card);
            border-radius: 28px 28px 22px 22px;
            box-shadow: 0 22px 50px rgba(10, 12, 18, .16);
            border: 1px solid var(--line);
            padding: 28px 22px 22px;
            position: relative;
            z-index: 1;
            animation: rise .55s ease both;
        }
        @keyframes rise {
            from { opacity: 0; transform: translateY(14px); }
            to { opacity: 1; transform: none; }
        }
        .card-brand {
            text-align: center;
            margin-bottom: 22px;
        }
        .card-brand img, .card-brand .card-logo-fallback {
            width: 64px; height: 64px; border-radius: 16px; object-fit: cover;
            margin: 0 auto 12px; display: block;
            border: 1px solid rgba(165,129,18,.35);
        }
        .card-logo-fallback {
            display: grid; place-items: center;
            background: linear-gradient(145deg, var(--gold), #7a5f0c);
            color: #111; font-weight: 800; font-size: 24px;
        }
        .card-brand h1 {
            margin: 0; font-size: 26px; font-weight: 800; letter-spacing: -.02em; color: var(--text);
        }
        .card-brand .mobile-welcome { display: block; }
        .card-brand .desktop-title { display: none; }
        .card-brand p {
            margin: 6px 0 0; color: var(--muted); font-size: 13px; line-height: 1.45;
        }
        .card-brand .store-line {
            margin-top: 4px; font-size: 13px; font-weight: 700; color: var(--text); opacity: .85;
        }

        .field { margin-bottom: 14px; }
        .field label {
            display: block; margin-bottom: 7px;
            font-size: 13px; font-weight: 700; color: var(--text);
        }
        .field-wrap {
            position: relative;
            display: flex; align-items: center;
        }
        .field-wrap .ico {
            position: absolute; left: 14px; color: #9ca3af; display: grid; place-items: center;
            pointer-events: none;
        }
        .field-wrap .ico svg { width: 18px; height: 18px; }
        .field-wrap input {
            width: 100%;
            border: 1px solid var(--line);
            background: var(--card);
            color: var(--text);
            border-radius: 14px;
            padding: 13px 44px 13px 44px;
            outline: none;
            transition: border-color .15s ease, box-shadow .15s ease;
        }
        .field-wrap input::placeholder { color: #9ca3af; }
        .field-wrap input:focus {
            border-color: var(--gold);
            box-shadow: 0 0 0 3px rgba(165,129,18,.18);
        }
        .toggle-pass {
            position: absolute; right: 10px;
            width: 36px; height: 36px; border: 0; border-radius: 10px;
            background: transparent; color: #9ca3af; cursor: pointer;
            display: grid; place-items: center;
        }
        .toggle-pass:hover { color: var(--gold); }
        .toggle-pass svg { width: 18px; height: 18px; }

        .row-actions {
            display: flex; align-items: center; justify-content: space-between;
            gap: 12px; margin: 4px 0 18px; font-size: 13px;
        }
        .remember {
            display: inline-flex; align-items: center; gap: 8px; color: var(--muted); cursor: pointer; user-select: none;
        }
        .remember input {
            appearance: none; width: 18px; height: 18px; border-radius: 5px;
            border: 1.5px solid #c4b483; background: #fff; position: relative; cursor: pointer;
        }
        html.dark .remember input { background: #1c2230; border-color: rgba(165,129,18,.55); }
        .remember input:checked {
            background: var(--gold); border-color: var(--gold);
        }
        .remember input:checked::after {
            content: '';
            position: absolute; left: 5px; top: 2px;
            width: 5px; height: 9px;
            border: solid #fff; border-width: 0 2px 2px 0;
            transform: rotate(45deg);
        }

        .btn-primary {
            width: 100%;
            display: inline-flex; align-items: center; justify-content: center; gap: 10px;
            border: 0; border-radius: 14px;
            background: var(--gold); color: #111;
            font-weight: 800; font-size: 15px;
            padding: 14px 18px; cursor: pointer;
            transition: transform .15s ease, box-shadow .15s ease, background .15s ease;
            box-shadow: 0 10px 24px rgba(165,129,18,.28);
        }
        .btn-primary:hover {
            background: var(--gold-soft);
            transform: translateY(-1px);
            box-shadow: 0 14px 28px rgba(165,129,18,.34);
        }
        .btn-primary svg { width: 18px; height: 18px; }

        .error {
            margin-bottom: 14px;
            padding: 12px 14px;
            border-radius: 12px;
            background: rgba(180, 35, 24, .08);
            border: 1px solid rgba(180, 35, 24, .18);
            color: #b42318;
            font-size: 13px;
            font-weight: 600;
        }

        .secure-note {
            margin: 18px 0 0;
            text-align: center;
            font-size: 11px;
            color: var(--muted);
            line-height: 1.45;
        }

        .mobile-foot {
            margin-top: 22px;
            text-align: center;
            color: rgba(17,17,17,.75);
        }
        html.dark .mobile-foot { color: rgba(255,255,255,.7); }
        .mobile-foot .hanger {
            width: 28px; height: 28px; margin: 0 auto 8px; color: var(--gold);
            display: grid; place-items: center;
        }
        .mobile-foot .hanger svg { width: 24px; height: 24px; }
        .mobile-foot p {
            margin: 0;
            font-family: 'Instrument Serif', Georgia, serif;
            font-style: italic;
            font-size: 16px;
        }
        .dots {
            display: flex; justify-content: center; gap: 6px; margin-top: 14px;
        }
        .dots span {
            width: 7px; height: 7px; border-radius: 999px; background: #cfc8b8;
        }
        .dots span.is-on {
            width: 22px; background: var(--gold);
        }

        @media (min-width: 980px) {
            .login-page {
                grid-template-columns: minmax(0, 1.35fr) minmax(380px, .85fr);
                min-height: 100vh;
            }
            .brand-panel {
                min-height: 100vh;
                padding: 36px 48px 32px;
                justify-content: space-between;
            }
            .brand-panel::after { display: none; }
            .brand-logo, .brand-logo-fallback { width: 64px; height: 64px; }
            .brand-name { font-size: 15px; }
            .brand-copy { margin-top: 0; max-width: 640px; }
            .brand-kicker {
                font-size: clamp(34px, 3.4vw, 48px);
                max-width: 14ch;
            }
            .brand-kicker .accent { color: var(--gold-soft); }
            .brand-sub { display: none; }
            .brand-lead { display: block; max-width: 42ch; }
            .feature-row {
                grid-template-columns: repeat(4, minmax(0, 1fr));
                gap: 14px;
                margin-top: 28px;
            }
            .feature {
                text-align: left;
                padding: 14px;
                border-radius: 16px;
                border: 1px solid rgba(255,255,255,.08);
                background: rgba(255,255,255,.04);
                backdrop-filter: blur(6px);
            }
            .feature-icon { margin: 0 0 10px; }
            .feature strong { font-size: 13px; }
            .feature span { font-size: 12px; }
            .brand-quote { display: block; }
            .brand-foot {
                display: grid;
                grid-template-columns: repeat(3, minmax(0, 1fr));
            }
            .brand-copy-right { display: block; }

            .form-panel {
                min-height: 100vh;
                justify-content: center;
                padding: 40px 36px;
            }
            .theme-toggle { top: 24px; right: 28px; }
            .login-card {
                margin-top: 0;
                padding: 36px 32px 28px;
                border-radius: 24px;
                animation: rise .6s ease .05s both;
            }
            .card-brand .mobile-welcome { display: none; }
            .card-brand .desktop-title { display: block; }
            .card-brand h1 { font-size: 28px; }
            .mobile-foot { display: none; }
        }
    </style>
</head>
@php
    $brand = $settings->displayName();
    $tagline = $settings->site_tagline ?: 'Best Quality For You';
    $logo = $settings->showsLogoOnLogin() ? $settings->logo : null;
@endphp
<body>
<div class="login-page">
    <aside class="brand-panel" aria-label="Brand">
        <div class="brand-top">
            @if($logo)
                <img src="{{ $logo }}" alt="{{ $brand }}" class="brand-logo">
            @else
                <div class="brand-logo-fallback" aria-hidden="true">N</div>
            @endif
            <div>
                <div class="brand-name">{{ $brand }}</div>
                <div class="brand-tag">{{ $tagline }}</div>
            </div>
        </div>

        <div class="brand-copy">
            <h2 class="brand-kicker">
                <span class="mobile-only">Admin Login</span>
                <span class="desktop-only" style="display:none;">Manage Your Business <span class="accent">With Confidence</span></span>
            </h2>
            <p class="brand-sub">Manage Your Business Anywhere</p>
            <p class="brand-lead">Sign in to access your admin dashboard, manage products, sales, stock, and more.</p>

            <div class="feature-row">
                <div class="feature">
                    <div class="feature-icon">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                    </div>
                    <strong>Products</strong>
                    <span>Manage inventory</span>
                </div>
                <div class="feature desktop-feature" style="display:none;">
                    <div class="feature-icon">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M3 3h2l.4 2M7 13h10l3-8H6.4M7 13L5.4 5M7 13l-2 9m12-9l2 9"/></svg>
                    </div>
                    <strong>Point of Sale</strong>
                    <span>Fast &amp; simple</span>
                </div>
                <div class="feature">
                    <div class="feature-icon">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6m6 0V9a2 2 0 012-2h2a2 2 0 012 2v10m6 0V5a2 2 0 00-2-2h-2a2 2 0 00-2 2v14"/></svg>
                    </div>
                    <strong>Analytics</strong>
                    <span>Track growth</span>
                </div>
                <div class="feature">
                    <div class="feature-icon">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M17 21v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2"/><circle cx="9" cy="7" r="4"/><path stroke-linecap="round" stroke-linejoin="round" d="M23 21v-2a4 4 0 00-3-3.87M16 3.13a4 4 0 010 7.75"/></svg>
                    </div>
                    <strong>Team</strong>
                    <span>Secure access</span>
                </div>
            </div>

            <p class="brand-quote">“Quality Apparel. Stronger Businesses.”</p>
        </div>

        <div>
            <div class="brand-foot">
                <div class="brand-foot-item">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
                    <div><strong>Secure Access</strong><span>Your data is protected</span></div>
                </div>
                <div class="brand-foot-item">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><rect x="2" y="4" width="20" height="14" rx="2"/><path stroke-linecap="round" d="M8 21h8M12 18v3"/></svg>
                    <div><strong>Access Anywhere</strong><span>On desktop or mobile</span></div>
                </div>
                <div class="brand-foot-item">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M13 2L3 14h9l-1 8 10-12h-9l1-8z"/></svg>
                    <div><strong>Built for Growth</strong><span>Powering your business</span></div>
                </div>
            </div>
            <p class="brand-copy-right">© {{ date('Y') }} {{ $brand }}. All rights reserved.</p>
        </div>
    </aside>

    <main class="form-panel">
        <div class="theme-toggle" role="group" aria-label="Theme">
            <button type="button" id="theme-light" class="is-active" title="Light">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><circle cx="12" cy="12" r="4"/><path stroke-linecap="round" d="M12 2v2M12 20v2M4.9 4.9l1.4 1.4M17.7 17.7l1.4 1.4M2 12h2M20 12h2M4.9 19.1l1.4-1.4M17.7 6.3l1.4-1.4"/></svg>
            </button>
            <button type="button" id="theme-dark" title="Dark">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M21 14.5A8.5 8.5 0 119.5 3a7 7 0 0011.5 11.5z"/></svg>
            </button>
        </div>

        <div class="login-card">
            <div class="card-brand">
                @if($logo)
                    <img src="{{ $logo }}" alt="{{ $brand }}">
                @else
                    <span class="card-logo-fallback" aria-hidden="true">N</span>
                @endif
                <h1 class="mobile-welcome">Welcome Back</h1>
                <h1 class="desktop-title">Admin Login</h1>
                <p class="store-line desktop-title">{{ $brand }}</p>
                <p class="mobile-welcome">Sign in to access your admin dashboard</p>
                <p class="desktop-title">Access your dashboard to manage your business.</p>
            </div>

            @if($errors->any())
                <div class="error">{{ $errors->first() }}</div>
            @endif

            <form method="POST" action="{{ route('admin.login.post') }}" autocomplete="on">
                @csrf
                <div class="field">
                    <label for="email">Email Address</label>
                    <div class="field-wrap">
                        <span class="ico" aria-hidden="true">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16v12H4z"/><path stroke-linecap="round" stroke-linejoin="round" d="M4 7l8 6 8-6"/></svg>
                        </span>
                        <input id="email" type="email" name="email" value="{{ old('email') }}" placeholder="Enter your email" required autofocus>
                    </div>
                </div>

                <div class="field">
                    <label for="password">Password</label>
                    <div class="field-wrap">
                        <span class="ico" aria-hidden="true">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><rect x="5" y="11" width="14" height="10" rx="2"/><path stroke-linecap="round" d="M8 11V8a4 4 0 018 0v3"/></svg>
                        </span>
                        <input id="password" type="password" name="password" placeholder="Enter your password" required>
                        <button type="button" class="toggle-pass" id="toggle-pass" aria-label="Show password">
                            <svg id="eye-open" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M1 12s4-7 11-7 11 7 11 7-4 7-11 7S1 12 1 12z"/><circle cx="12" cy="12" r="3"/></svg>
                            <svg id="eye-closed" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" style="display:none;"><path stroke-linecap="round" stroke-linejoin="round" d="M17.94 17.94A10.07 10.07 0 0112 19c-7 0-11-7-11-7a18.45 18.45 0 015.06-5.94M9.9 4.24A9.12 9.12 0 0112 4c7 0 11 7 11 7a18.5 18.5 0 01-2.16 3.19M1 1l22 22"/><path stroke-linecap="round" d="M14.12 14.12A3 3 0 019.88 9.88"/></svg>
                        </button>
                    </div>
                </div>

                <div class="row-actions">
                    <label class="remember">
                        <input type="checkbox" name="remember" value="1" @checked(old('remember'))>
                        <span>Remember me</span>
                    </label>
                </div>

                <button class="btn-primary" type="submit">
                    Sign In
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><path stroke-linecap="round" stroke-linejoin="round" d="M5 12h14M13 6l6 6-6 6"/></svg>
                </button>
            </form>

            <p class="secure-note">Authorized personnel only. Unauthorized access is prohibited.</p>
        </div>

        <div class="mobile-foot">
            <div class="hanger" aria-hidden="true">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4a2 2 0 012 2v1.2L21 14H3l7-6.8V6a2 2 0 012-2z"/><path stroke-linecap="round" d="M3 14h18v2a2 2 0 01-2 2H5a2 2 0 01-2-2v-2z"/></svg>
            </div>
            <p>Quality Apparel. Stronger Businesses.</p>
            <div class="dots" aria-hidden="true"><span class="is-on"></span><span></span><span></span></div>
        </div>
    </main>
</div>

<style>
    @media (min-width: 980px) {
        .mobile-only { display: none !important; }
        .desktop-only { display: inline !important; }
        .desktop-feature { display: block !important; }
    }
</style>

<script>
(function () {
    var root = document.documentElement;
    var lightBtn = document.getElementById('theme-light');
    var darkBtn = document.getElementById('theme-dark');
    var saved = localStorage.getItem('nomas-admin-login-theme');
    function apply(theme) {
        if (theme === 'dark') {
            root.classList.add('dark');
            darkBtn.classList.add('is-active');
            lightBtn.classList.remove('is-active');
        } else {
            root.classList.remove('dark');
            lightBtn.classList.add('is-active');
            darkBtn.classList.remove('is-active');
        }
        localStorage.setItem('nomas-admin-login-theme', theme);
    }
    apply(saved === 'dark' ? 'dark' : 'light');
    lightBtn.addEventListener('click', function () { apply('light'); });
    darkBtn.addEventListener('click', function () { apply('dark'); });

    var input = document.getElementById('password');
    var toggle = document.getElementById('toggle-pass');
    var open = document.getElementById('eye-open');
    var closed = document.getElementById('eye-closed');
    toggle.addEventListener('click', function () {
        var show = input.type === 'password';
        input.type = show ? 'text' : 'password';
        open.style.display = show ? 'none' : 'block';
        closed.style.display = show ? 'block' : 'none';
        toggle.setAttribute('aria-label', show ? 'Hide password' : 'Show password');
    });
})();
</script>
</body>
</html>
