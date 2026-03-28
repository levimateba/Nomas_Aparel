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
            position: relative;
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
            position: relative;
            z-index: 5;
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
        .floating-quick-nav {
            position: fixed;
            right: 24px;
            bottom: 96px;
            z-index: 999;
            display: flex;
            flex-direction: column;
            align-items: flex-end;
            gap: 10px;
        }
        .floating-quick-link {
            border-radius: 999px;
            padding: 12px 18px;
            min-width: 118px;
            text-align: center;
            box-shadow: 0 18px 30px rgba(22, 69, 110, 0.2);
            transition: transform 0.25s ease, box-shadow 0.25s ease, background 0.25s ease;
        }
        .floating-quick-link:hover,
        .floating-quick-link:focus {
            transform: translateY(-3px);
            box-shadow: 0 22px 34px rgba(22, 69, 110, 0.26);
        }
        @media (max-width: 767.98px) {
            .floating-quick-nav {
                right: 16px;
                bottom: 84px;
                gap: 8px;
            }
            .floating-quick-link {
                min-width: 104px;
                padding: 10px 14px;
                font-size: 0.9rem;
            }
        }
    </style>
</head>
<body>
    @php
        $brandName = $settings->site_name ?? 'Elgon Tech';
        $brandTagline = $settings->site_tagline ?? 'ICT Consultancy';
        $brandFullName = trim($brandName . ' ' . $brandTagline);
        $footerText = $settings->footer_text ?: $brandFullName;
        $contactItems = collect($siteContacts ?? []);
        $primaryPhone = optional($contactItems->firstWhere('type', 'phone'))->value ?? '+254...';
        $primaryEmail = optional($contactItems->firstWhere('type', 'email'))->value ?? 'info@.....';
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
                    @if(!empty($settings->logo))
                        <img src="{{ \Illuminate\Support\Facades\Storage::url($settings->logo) }}" alt="{{ $brandName }}" class="site-brand-logo">
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
                <div class="d-flex align-items-center">
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
                    <a href="{{ route('admin.login') }}" class="btn btn-primary py-2 px-4 ml-2 d-none d-lg-block">Admin</a>
                </div>
            </div>
        </nav>
    </div>
    <!-- Navbar End -->

    @yield('content')

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
                <div class="footer-panel">
                    <h3 class="mb-4">Newsletter</h3>
                    <p>Subscribe to our newsletter for updates from {{ $brandName }}.</p>
                    <div class="w-100">
                        <div class="input-group">
                            <input type="text" class="form-control border-light" style="padding: 30px;" placeholder="Your Email Address">
                            <div class="input-group-append">
                                <button class="btn btn-primary px-4">Sign Up</button>
                            </div>
                        </div>
                    </div>
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

    <div class="floating-quick-nav">
        <a href="{{ route('home') }}" class="btn btn-primary floating-quick-link"><i class="fa fa-home mr-2"></i>Home</a>
        <a href="{{ route('home') }}#about" class="btn btn-primary floating-quick-link">About</a>
        <a href="{{ route('home') }}#services" class="btn btn-primary floating-quick-link">Service</a>
        <a href="{{ route('home') }}#gallery" class="btn btn-primary floating-quick-link">Media</a>
        <a href="{{ route('home') }}#team" class="btn btn-primary floating-quick-link">Team</a>
        <a href="{{ route('home') }}#pricing" class="btn btn-primary floating-quick-link">Price</a>
        <a href="{{ route('blog.index') }}" class="btn btn-primary floating-quick-link">Blog</a>
        <a href="{{ route('home') }}#contact" class="btn btn-primary floating-quick-link">Contact</a>
    </div>

    <!-- Back to Top -->
    <a href="#" class="btn btn-lg btn-primary back-to-top"><i class="fa fa-angle-double-up"></i></a>

    <!-- JavaScript Libraries -->
    <script src="https://code.jquery.com/jquery-3.4.1.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.4.1/js/bootstrap.bundle.min.js"></script>

    <script>
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
        window.addEventListener('load', enableShortVideoAutoplay);
    </script>
</body>
</html>
