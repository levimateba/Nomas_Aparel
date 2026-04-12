<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>@yield('title', $settings->site_name ?? 'Website')</title>
    <link rel="stylesheet" href="{{ asset('css/app.css') }}" />
    <!-- Google Web Fonts -->
    <link rel="preconnect" href="https://fonts.gstatic.com">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@100;200;300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.10.0/css/all.min.css" rel="stylesheet">
    <!-- Bootstrap CSS -->
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.4.1/css/bootstrap.min.css" rel="stylesheet">
    <style>
        :root {
            --primary-dark: #16456e;
            --primary-green: #165752;
            --primary-brown: #573c16;
            --primary-accent: #2f6f8e;
            --secondary: #f8f9fa;
        }
        body { font-family: 'Poppins', sans-serif; }
        .text-primary { color: var(--primary-dark) !important; }
        .bg-primary { background: var(--primary-dark) !important; }
        .bg-secondary { background: var(--secondary) !important; }
        .btn-primary { background: var(--primary-green); border-color: var(--primary-green); }
        .btn-primary:hover { background: var(--primary-brown); border-color: var(--primary-brown); }
        .navbar-brand .text-primary { color: var(--primary-dark) !important; }
        .jumbotron { background: linear-gradient(rgba(22, 69, 110, 0.7), rgba(22, 87, 82, 0.7)), url('/img/hero-bg.jpg') center center no-repeat; background-size: cover; }
        .team .bg-primary { background: var(--primary-green) !important; }
        .pricing .bg-primary { background: var(--primary-brown) !important; }
        .footer { background: #343a40; }
        .topbar-shell {
            background: linear-gradient(120deg, rgba(22, 69, 110, 0.98), rgba(22, 87, 82, 0.92));
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            width: 100%;
            z-index: 1060;
            overflow: hidden;
        }
        .topbar-shell::after {
            content: "";
            position: absolute;
            inset: 0;
            background: radial-gradient(circle at top right, rgba(255,255,255,0.18), transparent 35%);
            pointer-events: none;
        }
        .main-nav {
            background: linear-gradient(180deg, rgba(255,255,255,0.98), rgba(246,250,252,0.98));
            backdrop-filter: blur(14px);
            box-shadow: 0 18px 40px rgba(22, 69, 110, 0.08);
            position: fixed;
            top: 44px;
            left: 0;
            right: 0;
            width: 100%;
            z-index: 1050;
        }
        body {
            padding-top: 136px;
        }
        @media (max-width: 991.98px) {
            .main-nav {
                top: 72px;
            }
            body {
                padding-top: 158px;
            }
        }
        .site-brand { display: flex; align-items: center; gap: 12px; }
        .site-brand-logo { width: 52px; height: 52px; object-fit: cover; border-radius: 12px; }
        .site-brand-copy { display: flex; flex-direction: column; line-height: 1.1; }
        .site-brand-name { font-size: 1.5rem; font-weight: 700; text-transform: uppercase; color: var(--primary-dark); }
        .site-brand-tagline { font-size: 0.85rem; letter-spacing: 0.08em; text-transform: uppercase; color: var(--primary-green); }
        .navbar-nav .nav-link {
            position: relative;
            margin: 0 4px;
            padding: 12px 18px !important;
            border-radius: 999px;
            color: var(--primary-dark) !important;
            font-weight: 600;
            letter-spacing: 0.01em;
            transition: transform 0.25s ease, color 0.25s ease, background 0.25s ease, box-shadow 0.25s ease;
        }
        .navbar-nav .nav-link::after {
            content: "";
            position: absolute;
            left: 18px;
            right: 18px;
            bottom: 8px;
            height: 3px;
            border-radius: 999px;
            background: linear-gradient(90deg, var(--primary-green), var(--primary-accent));
            transform: scaleX(0);
            transform-origin: center;
            transition: transform 0.25s ease;
        }
        .navbar-nav .nav-link:hover,
        .navbar-nav .nav-link:focus,
        .navbar-nav .nav-link.active,
        .navbar-nav .show > .nav-link {
            background: rgba(22, 87, 82, 0.08);
            color: var(--primary-green) !important;
            transform: translateY(-2px);
            box-shadow: 0 14px 24px rgba(22, 69, 110, 0.08);
        }
        .navbar-nav .nav-link:hover::after,
        .navbar-nav .nav-link:focus::after,
        .navbar-nav .nav-link.active::after,
        .navbar-nav .show > .nav-link::after {
            transform: scaleX(1);
        }
        .navbar-nav .dropdown-menu {
            border: none;
            border-radius: 18px;
            padding: 12px;
            box-shadow: 0 22px 38px rgba(22, 69, 110, 0.15);
            background: rgba(255,255,255,0.98);
        }
        .navbar-nav .dropdown-item {
            border-radius: 12px;
            padding: 10px 14px;
            font-weight: 600;
            color: var(--primary-dark);
            transition: background 0.2s ease, transform 0.2s ease, color 0.2s ease;
        }
        .navbar-nav .dropdown-item:hover {
            background: rgba(22, 87, 82, 0.08);
            color: var(--primary-green);
            transform: translateX(4px);
        }
        .nav-cta {
            flex-shrink: 0;
            white-space: nowrap;
        }
        .get-quote-btn {
            white-space: nowrap;
        }
        .mobile-quote-cta {
            display: none;
        }
        @media (max-width: 991.98px) {
            .nav-cta {
                display: block !important;
                width: 100%;
                margin-top: 12px;
            }
            .mobile-quote-cta {
                display: block;
                width: 100%;
                margin-top: 12px;
            }
        }
        .footer-panel {
            background: rgba(255, 255, 255, 0.06);
            border: 1px solid rgba(255, 255, 255, 0.08);
            border-radius: 16px;
            padding: 24px;
            height: 100%;
        }
        .footer-panel h3,
        .footer-panel p,
        .footer-panel a,
        .footer-panel small,
        .footer-panel span {
            color: #fff !important;
        }

        @keyframes chatIconDance {
            0%, 100% {
                transform: translateY(0) rotate(0deg) scale(1);
            }
            12% {
                transform: translateY(-4px) rotate(-6deg) scale(1.03);
            }
            24% {
                transform: translateY(0) rotate(5deg) scale(1);
            }
            36% {
                transform: translateY(-3px) rotate(-4deg) scale(1.02);
            }
            48% {
                transform: translateY(0) rotate(3deg) scale(1);
            }
            60% {
                transform: translateY(-2px) rotate(0deg) scale(1.01);
            }
        }

        .whatsapp-float {
            position: fixed;
            right: 24px;
            bottom: 104px;
            width: 58px;
            height: 58px;
            border-radius: 50%;
            background: #25d366;
            color: #fff;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 30px;
            box-shadow: 0 10px 28px rgba(7, 94, 84, 0.35);
            z-index: 1200;
            text-decoration: none;
            transition: transform 0.2s ease, box-shadow 0.2s ease;
            animation: chatIconDance 3.2s ease-in-out infinite;
            transform-origin: center;
        }
        .whatsapp-float:hover,
        .whatsapp-float:focus {
            color: #fff;
            transform: translateY(-3px) scale(1.03);
            box-shadow: 0 14px 34px rgba(7, 94, 84, 0.42);
            text-decoration: none;
            animation-play-state: paused;
        }
        @media (max-width: 576px) {
            .whatsapp-float {
                right: 18px;
                bottom: 92px;
                width: 54px;
                height: 54px;
                font-size: 28px;
            }
        }

    </style>
</head>
<body>
    @php
        $brandName = $settings->site_name ?? 'Elgon Tech';
        $brandTagline = $settings->site_tagline ?? 'ICT Consultancy';
        $rawLogoPath = (is_object($settings) && method_exists($settings, 'getRawOriginal'))
            ? $settings->getRawOriginal('logo')
            : ($settings->logo ?? null);
        $brandLogo = \App\Support\PublicStorageUrl::fromPath($rawLogoPath) ?? ($settings->logo ?? null);
        $brandFullName = trim($brandName . ' ' . $brandTagline);
        $footerText = $settings->footer_text ?: $brandFullName;
        $contactItems = collect($siteContacts ?? []);
        $primaryPhone = optional($contactItems->firstWhere('type', 'phone'))->value ?? '+254...';
        $primaryEmail = optional($contactItems->firstWhere('type', 'email'))->value ?? 'info@.....';
        $whatsAppDigits = preg_replace('/\D+/', '', (string) $primaryPhone);
        if (str_starts_with($whatsAppDigits, '0')) {
            $whatsAppDigits = '254' . substr($whatsAppDigits, 1);
        } elseif (str_starts_with($whatsAppDigits, '7') && strlen($whatsAppDigits) === 9) {
            $whatsAppDigits = '254' . $whatsAppDigits;
        }
        $whatsAppLink = !empty($whatsAppDigits)
            ? 'https://wa.me/' . $whatsAppDigits . '?text=' . rawurlencode('Hello ' . $brandName . ', I would like to inquire about your services.')
            : null;
    @endphp
    <!-- Topbar Start -->
    <div class="container-fluid topbar-shell">
        <div class="row py-2 px-lg-5">
            <div class="col-lg-6 text-center text-lg-left mb-2 mb-lg-0">
                <div class="d-inline-flex align-items-center text-white">
                    <small><i class="fa fa-phone-alt mr-2"></i>{{ $primaryPhone }}</small>
                    <small class="px-3">|</small>
                    <small><i class="fa fa-envelope mr-2"></i>{{ $primaryEmail }}</small>
                </div>
            </div>
            <div class="col-lg-6 text-center text-lg-right">
                <div class="d-inline-flex align-items-center">
                    <a class="text-white px-2" href="#"><i class="fab fa-facebook-f"></i></a>
                    <a class="text-white px-2" href="#"><i class="fab fa-twitter"></i></a>
                    <a class="text-white px-2" href="#"><i class="fab fa-linkedin-in"></i></a>
                    <a class="text-white px-2" href="#"><i class="fab fa-instagram"></i></a>
                    <a class="text-white pl-2" href="#"><i class="fab fa-youtube"></i></a>
                </div>
            </div>
        </div>
    </div>
    <!-- Topbar End -->

    <!-- Navbar Start -->
    <div class="container-fluid p-0">
        <nav class="navbar navbar-expand-lg navbar-light py-3 py-lg-0 px-lg-5 main-nav">
            <a href="{{ route('home') }}" class="navbar-brand ml-lg-3">
                <div class="site-brand">
                    @if(!empty($brandLogo))
                        <img src="{{ $brandLogo }}" alt="{{ $brandName }}" class="site-brand-logo">
                    @else
                        <i class="fa fa-truck text-primary" style="font-size: 2rem;"></i>
                    @endif
                    <div class="site-brand-copy">
                        <span class="site-brand-name">{{ $brandName }}</span>
                        @if($brandTagline)
                            <span class="site-brand-tagline">{{ $brandTagline }}</span>
                        @endif
                    </div>
                </div>
            </a>
            <button type="button" class="navbar-toggler" data-toggle="collapse" data-target="#navbarCollapse">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse justify-content-between px-lg-3" id="navbarCollapse">
                <div class="navbar-nav m-auto py-0">
                    <a href="{{ route('home') }}" class="nav-item nav-link {{ request()->routeIs('home') ? 'active' : '' }}">Home</a>
                    <a href="#about" class="nav-item nav-link">About</a>
                    <a href="#services" class="nav-item nav-link">Service</a>
                    <div class="nav-item dropdown">
                        <a href="#" class="nav-link dropdown-toggle" data-toggle="dropdown">Media</a>
                        <div class="dropdown-menu rounded-0 m-0">
                            <a href="#gallery" class="dropdown-item">Gallery</a>
                            <a href="#news-events" class="dropdown-item">News and Events</a>
                            <a href="#videos" class="dropdown-item">Videos</a>
                        </div>
                    </div>
                    <a href="#team" class="nav-item nav-link">Team</a>
                    <a href="#pricing" class="nav-item nav-link">Price</a>
                    <a href="{{ route('blog.index') }}" class="nav-item nav-link {{ request()->routeIs('blog.*') ? 'active' : '' }}">Blog</a>
                    <a href="#contact" class="nav-item nav-link">Contact</a>
                </div>
                <div class="d-flex align-items-center nav-cta">
                    @auth
                        <span class="mr-3 text-dark">{{ Auth::user()->name }}</span>
                        <form method="POST" action="{{ route('logout') }}" style="display: inline;">
                            @csrf
                            <button type="submit" class="btn btn-outline-danger btn-sm py-2 px-3">Logout</button>
                        </form>
                    @else
                        <a href="{{ route('login') }}" class="btn btn-outline-primary py-2 px-3 mr-2 d-none d-lg-block">Login</a>
                        <a href="{{ route('register') }}" class="btn btn-primary py-2 px-3 d-none d-lg-block">Register</a>
                    @endauth
                                    <button type="button" class="btn btn-success py-2 px-4 ml-2 d-none d-lg-block get-quote-btn"
                                        onclick="document.getElementById('quoteModal').style.display='flex'"
                                        style="background:linear-gradient(135deg,#165752,#1a7a6e);border:none;font-weight:700;letter-spacing:0.04em;box-shadow:0 6px 20px rgba(22,87,82,0.3);">
                                        <i class="fa fa-file-invoice mr-2"></i>Get Quote
                                    </button>
                    <button type="button" class="btn btn-success py-3 px-4 mobile-quote-cta"
                        onclick="document.getElementById('quoteModal').style.display='flex'"
                        style="background:linear-gradient(135deg,#165752,#1a7a6e);border:none;font-weight:700;letter-spacing:0.04em;box-shadow:0 6px 20px rgba(22,87,82,0.22);">
                        <i class="fa fa-file-invoice mr-2"></i>Get Quote
                    </button>
                </div>
            </div>
        </nav>
    </div>
    <!-- Navbar End -->

    @yield('content')

    @if($whatsAppLink)
        <a href="{{ $whatsAppLink }}" class="whatsapp-float" target="_blank" rel="noopener" aria-label="Chat on WhatsApp" title="Chat on WhatsApp">
            <i class="fab fa-whatsapp" aria-hidden="true"></i>
        </a>
    @endif

    <!-- ===== Quote Modal ===== -->
    @if(session('quote_success'))
    <div id="quoteSuccessAlert" style="position:fixed;top:24px;right:24px;z-index:9999;background:#fff;border-radius:16px;padding:20px 28px;box-shadow:0 12px 40px rgba(22,87,82,0.22);display:flex;align-items:center;gap:14px;max-width:380px;border-left:5px solid #165752;animation:quoteSlideIn 0.4s ease;">
        <i class="fa fa-check-circle" style="font-size:1.5rem;color:#165752;"></i>
        <div>
            <strong style="display:block;color:#165752;margin-bottom:4px;">Request Submitted!</strong>
            <span style="color:#555;font-size:0.9rem;">{{ session('quote_success') }}</span>
        </div>
        <button onclick="this.parentElement.remove()" style="background:none;border:none;color:#aaa;font-size:1.2rem;padding:0;margin-left:8px;cursor:pointer;">&times;</button>
    </div>
    @endif

    <div id="quoteModal" style="display:none;position:fixed;inset:0;z-index:9000;align-items:center;justify-content:center;background:rgba(22,69,110,0.55);backdrop-filter:blur(4px);padding:16px;" onclick="if(event.target===this)this.style.display='none'">
        <div style="background:#fff;border-radius:22px;width:100%;max-width:640px;max-height:92vh;overflow-y:auto;box-shadow:0 30px 80px rgba(22,69,110,0.25);animation:quoteFadeIn 0.3s ease;">
            <!-- Header -->
            <div style="background:linear-gradient(135deg,#16456e,#165752);border-radius:22px 22px 0 0;padding:28px 32px;position:relative;">
                <h3 style="margin:0;color:#fff;font-size:1.5rem;font-weight:700;">Request a Quote</h3>
                <p style="margin:8px 0 0;color:rgba(255,255,255,0.75);font-size:0.92rem;">Tell us about your project and we'll get back to you within 24 hours.</p>
                <button onclick="document.getElementById('quoteModal').style.display='none'" style="position:absolute;top:18px;right:22px;background:rgba(255,255,255,0.15);border:none;color:#fff;width:34px;height:34px;border-radius:50%;font-size:1.1rem;cursor:pointer;display:flex;align-items:center;justify-content:center;">&times;</button>
            </div>
            <!-- Body -->
            <div style="padding:28px 32px;">
                @if($errors->any() && old('_quote_form'))
                    <div style="background:#fff0f0;border-left:4px solid #e74c3c;border-radius:8px;padding:12px 16px;margin-bottom:18px;color:#c0392b;font-size:0.9rem;">
                        <ul style="margin:0;padding-left:16px;">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
                    </div>
                @endif
                <form method="POST" action="{{ route('quote.store') }}">
                    @csrf
                    <input type="hidden" name="_quote_form" value="1">
                    <!-- Row 1: Name + Email -->
                    <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;margin-bottom:16px;">
                        <div>
                            <label style="display:block;font-weight:600;font-size:0.88rem;color:#16456e;margin-bottom:6px;">Full Name <span style="color:#e74c3c;">*</span></label>
                            <input type="text" name="name" value="{{ old('name') }}" placeholder="John Doe" required
                                style="width:100%;padding:10px 14px;border:1.5px solid #dde3ea;border-radius:10px;font-size:0.95rem;box-sizing:border-box;outline:none;">
                        </div>
                        <div>
                            <label style="display:block;font-weight:600;font-size:0.88rem;color:#16456e;margin-bottom:6px;">Email Address <span style="color:#e74c3c;">*</span></label>
                            <input type="email" name="email" value="{{ old('email') }}" placeholder="you@company.com" required
                                style="width:100%;padding:10px 14px;border:1.5px solid #dde3ea;border-radius:10px;font-size:0.95rem;box-sizing:border-box;outline:none;">
                        </div>
                    </div>
                    <!-- Row 2: Phone + Company -->
                    <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;margin-bottom:16px;">
                        <div>
                            <label style="display:block;font-weight:600;font-size:0.88rem;color:#16456e;margin-bottom:6px;">Phone</label>
                            <input type="text" name="phone" value="{{ old('phone') }}" placeholder="+254 700 000 000"
                                style="width:100%;padding:10px 14px;border:1.5px solid #dde3ea;border-radius:10px;font-size:0.95rem;box-sizing:border-box;outline:none;">
                        </div>
                        <div>
                            <label style="display:block;font-weight:600;font-size:0.88rem;color:#16456e;margin-bottom:6px;">Company / Organisation</label>
                            <input type="text" name="company" value="{{ old('company') }}" placeholder="Acme Ltd"
                                style="width:100%;padding:10px 14px;border:1.5px solid #dde3ea;border-radius:10px;font-size:0.95rem;box-sizing:border-box;outline:none;">
                        </div>
                    </div>
                    <!-- Row 3: Service Type + Budget -->
                    <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;margin-bottom:16px;">
                        <div>
                            <label style="display:block;font-weight:600;font-size:0.88rem;color:#16456e;margin-bottom:6px;">Service Needed <span style="color:#e74c3c;">*</span></label>
                            <select name="service_type" required
                                style="width:100%;padding:10px 14px;border:1.5px solid #dde3ea;border-radius:10px;font-size:0.95rem;box-sizing:border-box;outline:none;background:#fff;">
                                <option value="" disabled {{ old('service_type') ? '' : 'selected' }}>Select a service…</option>
                                <option value="Software Development" {{ old('service_type')=='Software Development'?'selected':'' }}>Software Development</option>
                                <option value="Systems Integration" {{ old('service_type')=='Systems Integration'?'selected':'' }}>Systems Integration</option>
                                <option value="ICT Consultancy" {{ old('service_type')=='ICT Consultancy'?'selected':'' }}>ICT Consultancy</option>
                                <option value="Network Infrastructure" {{ old('service_type')=='Network Infrastructure'?'selected':'' }}>Network Infrastructure</option>
                                <option value="Web Design" {{ old('service_type')=='Web Design'?'selected':'' }}>Web Design &amp; Development</option>
                                <option value="Mobile App" {{ old('service_type')=='Mobile App'?'selected':'' }}>Mobile App Development</option>
                                <option value="Data & Analytics" {{ old('service_type')=='Data & Analytics'?'selected':'' }}>Data &amp; Analytics</option>
                                <option value="Support & Maintenance" {{ old('service_type')=='Support & Maintenance'?'selected':'' }}>Support &amp; Maintenance</option>
                                <option value="Other" {{ old('service_type')=='Other'?'selected':'' }}>Other</option>
                            </select>
                        </div>
                        <div>
                            <label style="display:block;font-weight:600;font-size:0.88rem;color:#16456e;margin-bottom:6px;">Budget Range</label>
                            <select name="budget_range"
                                style="width:100%;padding:10px 14px;border:1.5px solid #dde3ea;border-radius:10px;font-size:0.95rem;box-sizing:border-box;outline:none;background:#fff;">
                                <option value="">Prefer not to say</option>
                                <option value="KES 0.00 – KES 15,000.00" {{ old('budget_range')=='KES 0.00 – KES 15,000.00'?'selected':'' }}>KES 0.00 – KES 15,000.00</option>
                                <option value="KES 15,000.00 – KES 50,000.00" {{ old('budget_range')=='KES 15,000.00 – KES 50,000.00'?'selected':'' }}>KES 15,000.00 – KES 50,000.00</option>
                                <option value="KES 50,000.00 – KES 150,000.00" {{ old('budget_range')=='KES 50,000.00 – KES 150,000.00'?'selected':'' }}>KES 50,000.00 – KES 150,000.00</option>
                                <option value="KES 150,000.00 – KES 500,000.00" {{ old('budget_range')=='KES 150,000.00 – KES 500,000.00'?'selected':'' }}>KES 150,000.00 – KES 500,000.00</option>
                                <option value="KES 500,000.00+" {{ old('budget_range')=='KES 500,000.00+'?'selected':'' }}>KES 500,000.00+</option>
                            </select>
                        </div>
                    </div>
                    <!-- Timeline -->
                    <div style="margin-bottom:16px;">
                        <label style="display:block;font-weight:600;font-size:0.88rem;color:#16456e;margin-bottom:6px;">Desired Start / Deadline</label>
                        <input type="date" name="timeline" value="{{ old('timeline') }}"
                            style="width:100%;padding:10px 14px;border:1.5px solid #dde3ea;border-radius:10px;font-size:0.95rem;box-sizing:border-box;outline:none;">
                    </div>
                    <!-- Project Details -->
                    <div style="margin-bottom:22px;">
                        <label style="display:block;font-weight:600;font-size:0.88rem;color:#16456e;margin-bottom:6px;">Project Details <span style="color:#e74c3c;">*</span></label>
                        <textarea name="project_details" rows="4" required placeholder="Briefly describe what you need, goals, key features, or any relevant context…"
                            style="width:100%;padding:10px 14px;border:1.5px solid #dde3ea;border-radius:10px;font-size:0.95rem;box-sizing:border-box;resize:vertical;outline:none;">{{ old('project_details') }}</textarea>
                    </div>
                    <!-- Submit -->
                    <div style="display:flex;gap:12px;align-items:center;">
                        <button type="submit"
                            style="flex:1;padding:13px;background:linear-gradient(135deg,#16456e,#165752);color:#fff;border:none;border-radius:12px;font-size:1rem;font-weight:700;cursor:pointer;letter-spacing:0.04em;transition:opacity 0.2s;">
                            <i class="fa fa-paper-plane mr-2"></i>Submit Quote Request
                        </button>
                        <button type="button" onclick="document.getElementById('quoteModal').style.display='none'"
                            style="padding:13px 20px;background:#f1f3f5;color:#555;border:none;border-radius:12px;font-size:0.95rem;font-weight:600;cursor:pointer;">
                            Cancel
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <style>
        @keyframes quoteFadeIn { from { opacity:0; transform:translateY(30px) scale(0.97); } to { opacity:1; transform:none; } }
        @keyframes quoteSlideIn { from { opacity:0; transform:translateX(40px); } to { opacity:1; transform:none; } }
        @media (max-width:576px) {
            #quoteModal > div > div:last-child { padding: 20px 16px; }
            #quoteModal div[style*="grid-template-columns:1fr 1fr"] { grid-template-columns: 1fr !important; }
        }
    </style>
    @if(session('quote_success') || (old('_quote_form') && $errors->any()))
    <script>document.getElementById('quoteModal').style.display = 'flex';</script>
    @endif
    <script>
        if (window.location.hash === '#quote') {
            document.getElementById('quoteModal').style.display = 'flex';
        }
    </script>
    <!-- ===== End Quote Modal ===== -->

    <!-- Footer Start -->
    <div class="container-fluid footer text-white mt-5 py-5 px-sm-3 px-md-5">
        <div class="row pt-5">
            <div class="col-lg-7 col-md-6">
                <div class="row">
                    <div class="col-md-6 mb-5">
                        <div class="footer-panel">
                            <h3 class="mb-4">Get In Touch</h3>
                            @foreach($siteContacts ?? [] as $contact)
                                @if($contact->type == 'address')
                                    <p><i class="fa fa-map-marker-alt mr-2"></i>{{ $contact->value }}</p>
                                @elseif($contact->type == 'phone')
                                    <p><i class="fa fa-phone-alt mr-2"></i>{{ $contact->value }}</p>
                                @elseif($contact->type == 'email')
                                    <p><i class="fa fa-envelope mr-2"></i>{{ $contact->value }}</p>
                                @endif
                            @endforeach
                            <div class="d-flex justify-content-start mt-4">
                                <a class="btn btn-outline-light btn-social mr-2" href="#"><i class="fab fa-twitter"></i></a>
                                <a class="btn btn-outline-light btn-social mr-2" href="#"><i class="fab fa-facebook-f"></i></a>
                                <a class="btn btn-outline-light btn-social mr-2" href="#"><i class="fab fa-linkedin-in"></i></a>
                                <a class="btn btn-outline-light btn-social" href="#"><i class="fab fa-instagram"></i></a>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6 mb-5">
                        <div class="footer-panel">
                            <h3 class="mb-4">Quick Links</h3>
                            <div class="d-flex flex-column justify-content-start">
                                <a class="mb-2" href="{{ route('home') }}"><i class="fa fa-angle-right mr-2"></i>Home</a>
                                <a class="mb-2" href="#about"><i class="fa fa-angle-right mr-2"></i>About Us</a>
                                <a class="mb-2" href="#services"><i class="fa fa-angle-right mr-2"></i>Our Services</a>
                                <a class="mb-2" href="#pricing"><i class="fa fa-angle-right mr-2"></i>Pricing Plan</a>
                                <a href="#contact"><i class="fa fa-angle-right mr-2"></i>Contact Us</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-5 col-md-6 mb-5">
                <div class="footer-panel" id="newsletter-signup">
                    <h3 class="mb-4">Newsletter</h3>
                    <p>Subscribe to our newsletter for updates from {{ $brandName }}.</p>
                    @if(session('newsletter_success'))
                        <div class="alert" style="margin-bottom:14px;">{{ session('newsletter_success') }}</div>
                    @endif
                    @if($errors->newsletter->any())
                        <div class="alert alert-danger" style="margin-bottom:14px;">
                            <ul style="margin:0;padding-left:18px;">
                                @foreach($errors->newsletter->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif
                    <form method="POST" action="{{ route('newsletter.subscribe') }}" class="w-100">
                        @csrf
                        <div class="input-group">
                            <input type="email" name="newsletter_email" class="form-control border-light" style="padding: 30px;" placeholder="Your Email Address" value="{{ old('newsletter_email') }}" required>
                            <div class="input-group-append">
                                <button class="btn btn-primary px-4" type="submit">Sign Up</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    <div class="container-fluid footer border-top py-4 px-sm-3 px-md-5" style="border-color: #3E3E4E !important;">
        <div class="row">
            <div class="col-lg-6 text-center text-md-left mb-3 mb-md-0">
                <p class="m-0 text-white">&copy; {{ date('Y') }} {{ $footerText }}. All Rights Reserved.</p>
            </div>
            <div class="col-lg-6 text-center text-md-right">
                <ul class="nav d-inline-flex">
                    <li class="nav-item">
                        <a class="nav-link text-white py-0" href="#">Privacy</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link text-white py-0" href="#">Terms</a>
                    </li>
                </ul>
            </div>
        </div>
    </div>
    <!-- Footer End -->

    <!-- Back to Top -->
    <a href="#" class="btn btn-lg btn-primary back-to-top"><i class="fa fa-angle-double-up"></i></a>

    <!-- JavaScript Libraries -->
    <script src="https://code.jquery.com/jquery-3.4.1.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.4.1/js/bootstrap.bundle.min.js"></script>

    <script>
        (function(){if(!window.chatbase||window.chatbase("getState")!=="initialized"){window.chatbase=(...arguments)=>{if(!window.chatbase.q){window.chatbase.q=[]}window.chatbase.q.push(arguments)};window.chatbase=new Proxy(window.chatbase,{get(target,prop){if(prop==="q"){return target.q}return(...args)=>target(prop,...args)}})}const onLoad=function(){const script=document.createElement("script");script.src="https://www.chatbase.co/embed.min.js";script.id="kANxIBFeOdNi3HtiUL5ZW";script.domain="www.chatbase.co";document.body.appendChild(script)};if(document.readyState==="complete"){onLoad()}else{window.addEventListener("load",onLoad)}})();
    </script>

    <script>
        (function() {
            const launcherSelector = [
                'iframe[src*="chatbase"]',
                'button[id*="chatbase"]',
                'button[class*="chatbase"]',
                'div[id*="chatbase"]',
                'div[class*="chatbase"]'
            ].join(',');

            function isLauncher(element) {
                const rect = element.getBoundingClientRect();
                const style = window.getComputedStyle(element);

                return rect.width > 0
                    && rect.height > 0
                    && rect.width <= 100
                    && rect.height <= 100
                    && (style.position === 'fixed' || style.position === 'absolute');
            }

            function animateLaunchers() {
                document.querySelectorAll(launcherSelector).forEach(function(element) {
                    if (!isLauncher(element) || element.dataset.chatDanceApplied === 'true') {
                        return;
                    }

                    element.style.animation = 'chatIconDance 3.2s ease-in-out infinite';
                    element.style.transformOrigin = 'center';
                    element.style.willChange = 'transform';
                    element.dataset.chatDanceApplied = 'true';

                    element.addEventListener('mouseenter', function() {
                        element.style.animationPlayState = 'paused';
                    });

                    element.addEventListener('mouseleave', function() {
                        element.style.animationPlayState = 'running';
                    });
                });
            }

            const intervalId = window.setInterval(animateLaunchers, 1200);
            window.setTimeout(function() {
                window.clearInterval(intervalId);
            }, 30000);

            if (document.readyState === 'complete') {
                animateLaunchers();
            } else {
                window.addEventListener('load', animateLaunchers);
            }
        })();
    </script>

    <script>
        function toggleGalleryDesc(btn) {
            const card = btn.closest('.media-content');
            const full = card.querySelector('.gallery-full-desc');
            const isHidden = full.style.display === 'none' || full.style.display === '';
            full.style.display = isHidden ? 'block' : 'none';
            btn.innerHTML = isHidden ? '&lsaquo; Show less' : 'Read more &rsaquo;';
        }

        function initializeGalleryTabs() {
            const tabs = document.querySelectorAll('[data-gallery-tab]');
            const cards = document.querySelectorAll('[data-gallery-category]');

            if (!tabs.length || !cards.length) {
                return;
            }

            tabs.forEach(function(tab) {
                tab.addEventListener('click', function() {
                    const target = tab.getAttribute('data-gallery-tab');

                    tabs.forEach(function(node) {
                        node.classList.remove('active');
                    });

                    tab.classList.add('active');

                    cards.forEach(function(card) {
                        const category = card.getAttribute('data-gallery-category');
                        const shouldShow = target === 'all' || category === target;
                        card.style.display = shouldShow ? '' : 'none';
                    });
                });
            });
        }

        function initializeServiceTabs() {
            const tabs = document.querySelectorAll('[data-service-tab]');
            const cards = document.querySelectorAll('[data-service-category]');

            if (!tabs.length || !cards.length) {
                return;
            }

            tabs.forEach(function(tab) {
                tab.addEventListener('click', function() {
                    const target = tab.getAttribute('data-service-tab');

                    tabs.forEach(function(node) {
                        node.classList.remove('active');
                    });

                    tab.classList.add('active');

                    cards.forEach(function(card) {
                        const category = card.getAttribute('data-service-category');
                        const shouldShow = target === 'all' || category === target;
                        card.style.display = shouldShow ? '' : 'none';
                    });
                });
            });
        }

        function toggleServiceContent(btn) {
            const parent = btn.closest('.service-card');
            const preview = parent.querySelector('.service-preview');
            const full = parent.querySelector('.service-full');
            
            if (full.classList.contains('d-none')) {
                preview.classList.add('d-none');
                full.classList.remove('d-none');
                btn.textContent = '« Read Less';
            } else {
                full.classList.add('d-none');
                preview.classList.remove('d-none');
                btn.textContent = 'Read More »';
            }
        }

        function togglePricingContent(btn) {
            const parent = btn.closest('.pricing-card');
            const preview = parent.querySelector('.pricing-preview');
            const full = parent.querySelector('.pricing-full');
            
            if (full.classList.contains('d-none')) {
                preview.classList.add('d-none');
                full.classList.remove('d-none');
                btn.textContent = '« Read Less';
            } else {
                full.classList.add('d-none');
                preview.classList.remove('d-none');
                btn.textContent = 'Read More »';
            }
        }

        function toggleTeamContent(btn) {
            const parent = btn.closest('.team-member-content');
            const preview = parent.querySelector('.team-member-preview');
            const full = parent.querySelector('.team-member-full');

            if (!preview || !full) {
                return;
            }

            if (full.classList.contains('d-none')) {
                preview.classList.add('d-none');
                full.classList.remove('d-none');
                btn.textContent = '« Read Less';
            } else {
                full.classList.add('d-none');
                preview.classList.remove('d-none');
                btn.textContent = 'Read More »';
            }
        }

        function animateCounter($element) {
            var target = +$element.data('target');
            var duration = 1400;
            var stepTime = Math.abs(Math.floor(duration / target));
            var current = 0;
            var increment = target > 0 ? 1 : 0;

            var timer = setInterval(function() {
                current += increment;
                $element.text(current);
                if (current >= target) {
                    clearInterval(timer);
                    $element.text(target);
                }
            }, stepTime);
        }

        function runCounterOnVisible() {
            var $counters = $('.counter');
            var triggerPoint = $(window).scrollTop() + $(window).height();

            $counters.each(function() {
                var $this = $(this);
                var position = $this.offset().top;

                if (!$this.hasClass('counted') && triggerPoint > position) {
                    $this.addClass('counted');
                    animateCounter($this);
                }
            });
        }

        function enableShortVideoAutoplay() {
            document.querySelectorAll('.short-autoplay-video').forEach(function(video) {
                const threshold = Number(video.dataset.autoplayThreshold || 30);

                const startPlayback = function() {
                    video.autoplay = true;
                    video.muted = true;
                    video.loop = true;
                    video.playsInline = true;

                    const playPromise = video.play();
                    if (playPromise && typeof playPromise.catch === 'function') {
                        playPromise.catch(function() {});
                    }
                };

                startPlayback();

                video.addEventListener('loadedmetadata', function() {
                    if (Number.isFinite(video.duration) && video.duration <= threshold) {
                        startPlayback();
                    } else if (Number.isFinite(video.duration) && video.duration > threshold) {
                        video.removeAttribute('autoplay');
                        video.pause();
                    }
                }, { once: true });
            });
        }

        $(window).on('scroll load', runCounterOnVisible);
        window.addEventListener('load', function() {
            enableShortVideoAutoplay();
            initializeGalleryTabs();
            initializeServiceTabs();
        });
    </script>
</body>
</html>
