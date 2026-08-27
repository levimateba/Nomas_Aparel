<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Account')</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        :root {
            --black: #121212;
            --gold: #d4af37;
            --gold-dark: #b8942d;
            --white: #ffffff;
            --muted: #6b7280;
            --line: #e7e7e7;
        }
        * { box-sizing: border-box; }
        body {
            margin: 0;
            min-height: 100vh;
            font-family: Inter, system-ui, sans-serif;
            background: #0f0f0f;
            color: var(--black);
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 24px 16px;
        }
        .auth-wrap { width: min(440px, 100%); }
        .auth-card {
            background: var(--white);
            border-radius: 18px;
            overflow: hidden;
            box-shadow: 0 24px 50px rgba(0,0,0,0.28);
        }
        .auth-head {
            background: linear-gradient(135deg, #000000, #1a1a1a);
            color: var(--white);
            text-align: center;
            padding: 28px 22px 22px;
        }
        .auth-head img {
            width: 64px;
            height: 64px;
            object-fit: cover;
            border-radius: 14px;
            background: rgba(255,255,255,0.12);
            padding: 5px;
            margin-bottom: 12px;
        }
        .auth-head h1 { margin: 0; font-size: 1.6rem; }
        .auth-head p { margin: 8px 0 0; color: #e8d9a2; font-size: 0.95rem; }
        .auth-body { padding: 24px; }
        .notice {
            background: rgba(212,175,55,0.16);
            border-left: 4px solid var(--gold);
            padding: 10px 12px;
            margin-bottom: 16px;
            font-size: 0.92rem;
        }
        .alert {
            background: rgba(220,53,69,0.1);
            color: #8a1f2d;
            border-left: 4px solid #dc3545;
            padding: 10px 12px;
            margin-bottom: 16px;
            font-size: 0.92rem;
        }
        label { display: block; font-weight: 700; margin: 0 0 6px; font-size: 0.92rem; }
        input {
            width: 100%;
            padding: 12px 14px;
            border: 1px solid var(--line);
            border-radius: 10px;
            margin-bottom: 14px;
            font-size: 15px;
        }
        .btn {
            width: 100%;
            border: 0;
            border-radius: 10px;
            padding: 13px 16px;
            font-weight: 800;
            cursor: pointer;
            background: linear-gradient(135deg, var(--gold), var(--gold-dark));
            color: var(--black);
        }
        .auth-links { text-align: center; margin-top: 16px; color: var(--muted); font-size: 0.95rem; }
        .auth-links a { color: var(--black); font-weight: 700; }
        .back-link {
            display: block;
            text-align: center;
            margin-top: 16px;
            color: #e8d9a2;
            text-decoration: none;
            font-size: 0.92rem;
        }
    </style>
</head>
<body>
    @php
        $brandName = $settings->site_name ?? 'Store';
        $rawLogoPath = (is_object($settings) && method_exists($settings, 'getRawOriginal'))
            ? $settings->getRawOriginal('logo')
            : ($settings->logo ?? null);
        $brandLogo = \App\Support\PublicStorageUrl::fromPath($rawLogoPath) ?? ($settings->logo ?? null);
        $needsCheckout = str_contains((string) session('url.intended'), '/checkout');
    @endphp
    <div class="auth-wrap">
        <div class="auth-card">
            <div class="auth-head">
                @if(!empty($brandLogo))
                    <img src="{{ $brandLogo }}" alt="{{ $brandName }}">
                @endif
                <h1>@yield('heading', 'Account')</h1>
                <p>@yield('subheading', $brandName)</p>
            </div>
            <div class="auth-body">
                @if($needsCheckout)
                    <div class="notice">Please login or register to continue checkout.</div>
                @endif
                @if(session('error'))
                    <div class="alert">{{ session('error') }}</div>
                @endif
                @if($errors->any())
                    <div class="alert">
                        @foreach($errors->all() as $error)
                            <div>{{ $error }}</div>
                        @endforeach
                    </div>
                @endif
                @yield('content')
            </div>
        </div>
        <a class="back-link" href="{{ route('home') }}">Back to shop</a>
    </div>
</body>
</html>
