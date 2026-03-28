@extends('layouts.frontend')

@section('title','Home')

@section('content')
    @php
        $fallbackTeamMembers = [
            [
                'name' => 'Leah Wanjiku',
                'role' => 'Managing Consultant',
                'bio' => 'Leads digital transformation programs, stakeholder alignment, and strategy delivery for clients across sectors.',
                'image' => asset('views/frontend/img/team-1.jpg'),
            ],
            [
                'name' => 'Brian Kiptoo',
                'role' => 'Solutions Architect',
                'bio' => 'Designs reliable software platforms, integrations, and cloud-ready systems that scale with client growth.',
                'image' => asset('views/frontend/img/team-2.jpg'),
            ],
            [
                'name' => 'Mercy Achieng',
                'role' => 'Project and Support Lead',
                'bio' => 'Coordinates delivery, user onboarding, and responsive support to keep every rollout smooth and measurable.',
                'image' => asset('views/frontend/img/team-3.jpg'),
            ],
        ];

        $fallbackGalleryItems = [
            ['title' => 'Project Workshops', 'image' => asset('views/frontend/img/about.jpg')],
            ['title' => 'Solution Delivery', 'image' => asset('views/frontend/img/feature.jpg')],
            ['title' => 'Client Success Moments', 'image' => asset('views/frontend/img/blog-1.jpg')],
        ];

        $fallbackVideoItems = [
            ['title' => 'Company Profile', 'description' => 'A quick look at how we approach software, support, and digital growth.'],
            ['title' => 'Client Success Stories', 'description' => 'Highlights from recent implementation wins and transformation journeys.'],
            ['title' => 'Product Demos', 'description' => 'Short product walkthroughs that show the value behind our delivery process.'],
        ];
    @endphp

    <section id="slider" class="mb-5">
        <div id="homeCarousel" class="carousel slide" data-ride="carousel" data-interval="5000">
            <ol class="carousel-indicators">
                @forelse($posts as $post)
                    <li data-target="#homeCarousel" data-slide-to="{{ $loop->index }}" class="{{ $loop->first ? 'active' : '' }}"></li>
                @empty
                    <li data-target="#homeCarousel" data-slide-to="0" class="active"></li>
                @endforelse
            </ol>
            <div class="carousel-inner" style="min-height: 450px;">
                @forelse($posts as $post)
                    <div class="carousel-item {{ $loop->first ? 'active' : '' }}">
                        <div class="w-100 h-100" style="background: linear-gradient(rgba(22, 69, 110, 0.45), rgba(22, 87, 82, 0.45)), url('{{ $post->image ?? '/img/slider-default.jpg' }}') center center / cover no-repeat; min-height: 450px;">
                            <div class="carousel-caption d-none d-md-block text-left p-4" style="background: rgba(0,0,0,0.35); border-radius: 10px;">
                                <h3 class="text-white">{{ $post->title }}</h3>
                                <p class="text-light">{{ $post->excerpt ?: \Illuminate\Support\Str::limit($post->content, 130) }}</p>
                                <a href="{{ route('blog.show', $post->slug) }}" class="btn btn-primary">Read More</a>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="carousel-item active">
                        <div class="w-100 h-100" style="background: linear-gradient(rgba(22, 69, 110, 0.45), rgba(22, 87, 82, 0.45)), url('/img/slider-default.jpg') center center / cover no-repeat; min-height: 450px;">
                            <div class="carousel-caption d-none d-md-block text-left p-4" style="background: rgba(0,0,0,0.35); border-radius: 10px;">
                                <h3 class="text-white">Welcome to {{ $settings->site_name }}</h3>
                                <p class="text-light">Quality software development, systems integration, and consulting services.</p>
                                <a href="{{ route('blog.index') }}" class="btn btn-primary">Read More</a>
                            </div>
                        </div>
                    </div>
                @endforelse
            </div>
            <a class="carousel-control-prev" href="#homeCarousel" role="button" data-slide="prev">
                <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                <span class="sr-only">Previous</span>
            </a>
            <a class="carousel-control-next" href="#homeCarousel" role="button" data-slide="next">
                <span class="carousel-control-next-icon" aria-hidden="true"></span>
                <span class="sr-only">Next</span>
            </a>
        </div>
    </section>

    <section id="counting" class="py-5 text-white" style="background: linear-gradient(110deg, rgba(22, 69, 110, 0.95) 0%, rgba(22, 87, 82, 0.95) 100%);">
        <div class="container">
            <div class="row text-center">
                <div class="col-md-4 mb-4">
                    <h4 class="mb-2">Custom Product Solutions</h4>
                    <div class="display-4 font-weight-bold counter" data-target="572">0</div>
                </div>
                <div class="col-md-4 mb-4">
                    <h4 class="mb-2">Completed Projects</h4>
                    <div class="display-4 font-weight-bold counter" data-target="331">0</div>
                </div>
                <div class="col-md-4 mb-4">
                    <h4 class="mb-2">Satisfied Customers</h4>
                    <div class="display-4 font-weight-bold counter" data-target="123">0</div>
                </div>
            </div>
        </div>
    </section>

    <section id="about" class="py-5">
        <div class="container">
            <div class="section-heading text-center mb-5">
                <h2>About {{ $settings->site_name }}</h2>
                <div class="line mx-auto"></div>
            </div>
            @forelse($about as $section)
                <div class="row align-items-center mb-5 about-item fade-in-up">
                    @if($loop->odd)
                        <div class="col-md-6 mb-4 mb-md-0 order-md-1">
                            <div class="about-image-wrapper overflow-hidden" style="border-radius: 15px; max-height: 450px; box-shadow: 0 10px 40px rgba(22, 69, 110, 0.15);">
                                <img src="{{ $section->image_url ?? 'https://via.placeholder.com/500x400?text=About+Image' }}" 
                                     alt="{{ $section->title }}" 
                                     class="img-fluid w-100 about-image hover-scale"
                                     style="object-fit: cover; height: 100%; display: block;">
                            </div>
                        </div>
                        <div class="col-md-6 pl-md-4 order-md-2">
                    @else
                        <div class="col-md-6 pr-md-4 order-md-1">
                    @endif
                        <h3 class="text-primary mb-3" style="font-size: 1.8rem; font-weight: 600;">{{ $section->title }}</h3>
                        <div class="about-content" style="line-height: 1.8; color: #555;">
                            <p class="about-text-preview">{{ \Illuminate\Support\Str::limit($section->content, 200) }}</p>
                            @if(strlen($section->content) > 200)
                                <p class="about-text-full d-none">{{ $section->content }}</p>
                                <button class="btn btn-outline-primary read-more-btn" data-toggle="collapse" data-target=".collapse-{{ $loop->index }}">Read More</button>
                            @else
                                <p>{{ $section->content }}</p>
                            @endif
                        </div>
                    </div>
                    @if($loop->even)
                        <div class="col-md-6 mb-4 mb-md-0 order-md-2">
                            <div class="about-image-wrapper overflow-hidden" style="border-radius: 15px; max-height: 450px; box-shadow: 0 10px 40px rgba(22, 69, 110, 0.15);">
                                <img src="{{ $section->image_url ?? 'https://via.placeholder.com/500x400?text=About+Image' }}" 
                                     alt="{{ $section->title }}" 
                                     class="img-fluid w-100 about-image hover-scale"
                                     style="object-fit: cover; height: 100%; display: block;">
                            </div>
                        </div>
                    @endif
                </div>
            @empty
                <p class="text-center">No about content yet.</p>
            @endforelse
        </div>
    </section>

    <style>
        .about-item {
            animation: fadeInUp 0.8s ease-out forwards;
            opacity: 0;
        }
        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        .about-image {
            transition: transform 0.4s ease-out;
        }
        .about-image-wrapper {
            position: relative;
            overflow: hidden;
            isolation: isolate;
            padding: 2px;
        }
        .about-image-wrapper:hover .about-image {
            transform: scale(1.08);
        }
        .about-image-wrapper::before {
            content: "";
            position: absolute;
            inset: -35%;
            background: conic-gradient(
                from 0deg,
                transparent 0deg,
                transparent 250deg,
                rgba(22, 87, 82, 0.95) 300deg,
                rgba(47, 111, 142, 0.95) 335deg,
                rgba(255, 255, 255, 0.18) 360deg
            );
            opacity: 0;
            transition: opacity 0.35s ease;
            z-index: 0;
            pointer-events: none;
        }
        .about-image-wrapper:hover::before {
            opacity: 1;
            animation: imageOrbit 2.2s linear infinite;
        }
        .read-more-btn {
            margin-top: 15px;
            border-radius: 25px;
            padding: 8px 25px;
            font-weight: 500;
            transition: all 0.3s ease;
        }
        .read-more-btn:hover {
            background: var(--primary-green);
            border-color: var(--primary-green);
            color: white;
        }
    </style>

    <section id="services" class="py-5" style="background: #f8f9fa;">
        <div class="container">
            <div class="section-heading text-center mb-5">
                <h2 style="font-size: 2.5rem; font-weight: 700; color: var(--primary-dark); margin-bottom: 10px;">Innovative IT Services</h2>
                <p style="font-size: 1.1rem; color: #666; margin-bottom: 20px;">Aligned with Your Goals</p>
                <div class="line mx-auto"></div>
            </div>
            <div class="row">
                @forelse($services as $service)
                    <div class="col-md-6 col-lg-4 mb-4">
                        <div class="service-card h-100 fade-in-up" style="animation-delay: {{ $loop->index * 0.15 }}s;">
                            <div class="service-icon-wrapper">
                                @if($service->icon)
                                    <i class="fas {{ $service->icon }}" style="font-size: 2.5rem; color: var(--primary-green);"></i>
                                @else
                                    <i class="fas fa-code" style="font-size: 2.5rem; color: var(--primary-green);"></i>
                                @endif
                            </div>
                            <h4 class="service-title">{{ $service->title }}</h4>
                            <p class="service-preview">{{ \Illuminate\Support\Str::limit($service->description, 120) }}</p>
                            @if(strlen($service->description) > 120)
                                <p class="service-full d-none">{{ $service->description }}</p>
                                <button class="read-more-link" onclick="toggleServiceContent(this)">Read More »</button>
                            @else
                                <p>{{ $service->description }}</p>
                            @endif
                        </div>
                    </div>
                @empty
                    <div class="col-12">
                        <p class="text-center">No services available yet.</p>
                    </div>
                @endforelse
            </div>
        </div>
    </section>

    <style>
        .service-card {
            background: white;
            border-radius: 12px;
            padding: 30px;
            text-align: center;
            box-shadow: 0 4px 15px rgba(22, 69, 110, 0.08);
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            border-top: 4px solid transparent;
            animation: fadeInUp 0.8s ease-out forwards;
            opacity: 0;
            cursor: pointer;
        }

        .service-card:hover {
            transform: translateY(-10px) scale(1.02);
            box-shadow: 0 12px 30px rgba(22, 69, 110, 0.2);
            border-top-color: var(--primary-green);
        }

        .service-icon-wrapper {
            display: flex;
            justify-content: center;
            margin-bottom: 20px;
            width: 80px;
            height: 80px;
            background: rgba(22, 87, 82, 0.08);
            border-radius: 50%;
            align-items: center;
            margin-left: auto;
            margin-right: auto;
            transition: all 0.3s ease;
        }

        .service-card:hover .service-icon-wrapper {
            background: rgba(22, 87, 82, 0.2);
            transform: rotateY(360deg);
        }

        .service-title {
            font-size: 1.3rem;
            font-weight: 600;
            color: var(--primary-dark);
            margin-bottom: 15px;
        }

        .service-preview, .service-full {
            color: #666;
            line-height: 1.6;
            font-size: 0.95rem;
        }

        .read-more-link {
            background: none;
            border: none;
            color: var(--primary-green);
            font-weight: 600;
            cursor: pointer;
            padding: 0;
            margin-top: 10px;
            transition: all 0.3s ease;
            text-decoration: none;
        }

        .read-more-link:hover {
            color: var(--primary-brown);
        }

        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
    </style>

    <section id="pricing" class="py-5">
        <div class="container">
            <div class="section-heading text-center mb-5">
                <h2 style="font-size: 2.5rem; font-weight: 700; color: var(--primary-dark); margin-bottom: 10px;">Pricing Plans</h2>
                <p style="font-size: 1.1rem; color: #666; margin-bottom: 20px;">Get More Value at the Right Price</p>
                <div class="line mx-auto"></div>
            </div>
            <div class="row">
                @forelse($pricePackages as $plan)
                    <div class="col-md-6 col-lg-4 mb-4">
                        <div class="pricing-card {{ $plan->featured ? 'featured-plan' : '' }} fade-in-up" style="animation-delay: {{ $loop->index * 0.15 }}s;">
                            @if($plan->featured)
                                <div class="featured-badge">POPULAR</div>
                            @endif
                            <div class="pricing-header">
                                <h3 class="pricing-title">{{ $plan->title }}</h3>
                                <div class="pricing-amount">
                                    <span class="currency">Kshs.</span>
                                    <span class="amount">{{ number_format($plan->amount, 0) }}</span>
                                    <span class="period">/ {{ $plan->billing_period }}</span>
                                </div>
                            </div>
                            
                            @if($plan->description)
                                <p class="pricing-description pricing-preview">{{ \Illuminate\Support\Str::limit($plan->description, 100) }}</p>
                                @if(strlen($plan->description) > 100)
                                    <p class="pricing-description pricing-full d-none">{{ $plan->description }}</p>
                                    <button class="read-more-price-btn" onclick="togglePricingContent(this)">Read More »</button>
                                @else
                                    <p class="pricing-description">{{ $plan->description }}</p>
                                @endif
                            @endif
                            
                            @if($plan->features && is_array($plan->features) && count($plan->features) > 0)
                                <ul class="pricing-features">
                                    @foreach($plan->features as $feature => $value)
                                        <li class="feature-item" style="animation-delay: {{ $loop->index * 0.1 }}s;">
                                            <i class="fas fa-check-circle" style="color: var(--primary-green); margin-right: 8px; font-size: 0.9rem;"></i>
                                            <span class="feature-label">{{ $feature }}:</span>
                                            <span class="feature-value">{{ $value }}</span>
                                        </li>
                                    @endforeach
                                </ul>
                            @endif
                            
                            <a href="#quote" class="pricing-btn">Get Started</a>
                        </div>
                    </div>
                @empty
                    <div class="col-12">
                        <p class="text-center">No pricing plans yet.</p>
                    </div>
                @endforelse
            </div>
            <div class="text-center mt-4">
                <a href="#quote" class="btn btn-outline-primary" style="border-radius: 25px; padding: 10px 30px;">More Plans »</a>
            </div>
        </div>
    </section>

    <style>
        .pricing-card {
            background: white;
            border-radius: 12px;
            padding: 30px;
            box-shadow: 0 4px 15px rgba(22, 69, 110, 0.08);
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
            position: relative;
            animation: fadeInUp 0.8s ease-out forwards;
            opacity: 0;
            display: flex;
            flex-direction: column;
            height: 100%;
            border: 2px solid transparent;
            overflow: hidden;
            isolation: isolate;
        }

        .pricing-card::before {
            content: "";
            position: absolute;
            inset: -35%;
            background: conic-gradient(
                from 0deg,
                transparent 0deg,
                transparent 250deg,
                rgba(22, 87, 82, 0.95) 300deg,
                rgba(47, 111, 142, 0.95) 335deg,
                rgba(255, 255, 255, 0.25) 360deg
            );
            opacity: 0;
            transform: rotate(0deg);
            transition: opacity 0.35s ease;
            z-index: -2;
        }

        .pricing-card::after {
            content: "";
            position: absolute;
            inset: 2px;
            border-radius: 10px;
            background: white;
            z-index: -1;
        }

        .pricing-card:hover {
            transform: translateY(-15px) scale(1.02);
            box-shadow: 0 15px 40px rgba(22, 69, 110, 0.2);
            border-color: var(--primary-green);
        }

        .pricing-card:hover::before {
            opacity: 1;
            animation: pricingOrbit 2.2s linear infinite;
        }

        .pricing-card.featured-plan {
            border: 2px solid var(--primary-green);
            transform: scale(1.05);
        }

        .pricing-card.featured-plan:hover {
            box-shadow: 0 20px 50px rgba(22, 87, 82, 0.3);
        }

        .featured-badge {
            position: absolute;
            top: -12px;
            left: 20px;
            background: var(--primary-green);
            color: white;
            padding: 3px 15px;
            border-radius: 20px;
            font-size: 0.75rem;
            font-weight: 700;
            letter-spacing: 1px;
        }

        .pricing-header {
            margin-bottom: 20px;
        }

        .pricing-title {
            font-size: 1.4rem;
            font-weight: 600;
            color: var(--primary-dark);
            margin-bottom: 15px;
        }

        .pricing-amount {
            display: flex;
            align-items: baseline;
            gap: 5px;
            margin-bottom: 10px;
        }

        .currency {
            font-size: 0.9rem;
            color: #999;
            font-weight: 500;
        }

        .amount {
            font-size: 2.5rem;
            font-weight: 700;
            color: var(--primary-green);
        }

        .period {
            font-size: 0.9rem;
            color: #666;
        }

        .pricing-description {
            color: #666;
            font-size: 0.95rem;
            line-height: 1.5;
            margin-bottom: 10px;
        }

        .read-more-price-btn {
            background: none;
            border: none;
            color: var(--primary-green);
            font-weight: 600;
            cursor: pointer;
            padding: 0;
            margin-bottom: 15px;
            transition: all 0.3s ease;
            text-decoration: none;
        }

        .read-more-price-btn:hover {
            color: var(--primary-brown);
        }

        .pricing-features {
            list-style: none;
            padding: 0;
            margin: 15px 0;
            flex-grow: 1;
        }

        .feature-item {
            display: flex;
            align-items: flex-start;
            padding: 8px 0;
            border-bottom: 1px solid #f0f0f0;
            font-size: 0.9rem;
            color: #666;
            animation: slideInLeft 0.6s ease-out forwards;
            opacity: 0;
        }

        .feature-item:last-child {
            border-bottom: none;
        }

        .feature-label {
            font-weight: 600;
            color: var(--primary-dark);
            min-width: 140px;
        }

        .feature-value {
            color: var(--primary-green);
            font-weight: 600;
        }

        .pricing-btn {
            display: inline-block;
            background: var(--primary-green);
            color: white;
            padding: 12px 30px;
            border-radius: 25px;
            text-decoration: none;
            font-weight: 600;
            transition: all 0.3s ease;
            border: 2px solid var(--primary-green);
            text-align: center;
            margin-top: auto;
        }

        .pricing-btn:hover {
            background: var(--primary-brown);
            border-color: var(--primary-brown);
            color: white;
        }

        @keyframes slideInLeft {
            from {
                opacity: 0;
                transform: translateX(-20px);
            }
            to {
                opacity: 1;
                transform: translateX(0);
            }
        }

        @keyframes pricingOrbit {
            from {
                transform: rotate(0deg);
            }
            to {
                transform: rotate(360deg);
            }
        }
    </style>

    <section id="blog" class="py-5" style="background: #f8f9fa;">
        <div class="container">
            <div class="section-heading text-center mb-5">
                <h2 style="font-size: 2.5rem; font-weight: 700; color: var(--primary-dark); margin-bottom: 10px;">Latest Blog</h2>
                <p style="font-size: 1.1rem; color: #666; margin-bottom: 20px;">Latest Articles & Blogs</p>
                <div class="line mx-auto"></div>
            </div>
            <div class="row">
                @forelse($posts->take(3) as $post)
                    <div class="col-md-6 col-lg-4 mb-4">
                        <div class="blog-card h-100 fade-in-up" style="animation-delay: {{ $loop->index * 0.15 }}s;">
                            <div class="blog-image-wrapper">
                                <img src="{{ $post->image ?? 'https://via.placeholder.com/400x250?text=Blog+Image' }}" 
                                     alt="{{ $post->title }}" 
                                     class="blog-image">
                                <div class="blog-category">{{ $post->category ?? 'General' }}</div>
                            </div>
                            <div class="blog-content">
                                <div class="blog-meta">
                                    <span class="blog-author">{{ $post->author ?? 'Admin Admin' }}</span>
                                    <span class="blog-date">{{ $post->published_at ? $post->published_at->format('d M, Y') : now()->format('d M, Y') }}</span>
                                </div>
                                <h4 class="blog-title">
                                    <a href="{{ route('blog.show', $post->slug) }}">{{ $post->title }}</a>
                                </h4>
                                <p class="blog-excerpt">{{ $post->excerpt ?: \Illuminate\Support\Str::limit($post->content, 120) }}</p>
                                <a href="{{ route('blog.show', $post->slug) }}" class="blog-read-more">Read More »</a>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="col-12">
                        <p class="text-center">No blog posts yet.</p>
                    </div>
                @endforelse
            </div>
            <div class="text-center mt-4">
                <a href="{{ route('blog.index') }}" class="btn btn-outline-primary" style="border-radius: 25px; padding: 10px 30px;">View All Posts</a>
            </div>
        </div>
    </section>

    <style>
        .blog-card {
            background: white;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 4px 15px rgba(22, 69, 110, 0.08);
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
            animation: fadeInUp 0.8s ease-out forwards;
            opacity: 0;
            display: flex;
            flex-direction: column;
            position: relative;
            isolation: isolate;
        }

        .blog-card:hover {
            transform: translateY(-10px) scale(1.02);
            box-shadow: 0 15px 40px rgba(22, 69, 110, 0.2);
        }

        .blog-card::before {
            content: "";
            position: absolute;
            inset: -35%;
            background: conic-gradient(
                from 0deg,
                transparent 0deg,
                transparent 250deg,
                rgba(22, 87, 82, 0.95) 300deg,
                rgba(47, 111, 142, 0.95) 335deg,
                rgba(255, 255, 255, 0.18) 360deg
            );
            opacity: 0;
            transition: opacity 0.35s ease;
            z-index: -2;
            pointer-events: none;
        }

        .blog-card::after {
            content: "";
            position: absolute;
            inset: 2px;
            border-radius: 10px;
            background: white;
            z-index: -1;
            pointer-events: none;
        }

        .blog-card:hover::before {
            opacity: 1;
            animation: imageOrbit 2.2s linear infinite;
        }

        .blog-image-wrapper {
            position: relative;
            overflow: hidden;
            height: 200px;
        }

        .blog-image {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.4s ease;
        }

        .blog-card:hover .blog-image {
            transform: scale(1.1);
        }

        .blog-category {
            position: absolute;
            top: 15px;
            left: 15px;
            background: var(--primary-green);
            color: white;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 0.75rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .blog-content {
            padding: 25px;
            flex-grow: 1;
            display: flex;
            flex-direction: column;
        }

        .blog-meta {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 15px;
            font-size: 0.85rem;
            color: #666;
        }

        .blog-author {
            font-weight: 600;
            color: var(--primary-dark);
        }

        .blog-date {
            color: #999;
        }

        .blog-title {
            font-size: 1.2rem;
            font-weight: 600;
            color: var(--primary-dark);
            margin-bottom: 15px;
            line-height: 1.4;
        }

        .blog-title a {
            color: inherit;
            text-decoration: none;
            transition: color 0.3s ease;
        }

        .blog-title a:hover {
            color: var(--primary-green);
        }

        .blog-excerpt {
            color: #666;
            line-height: 1.6;
            margin-bottom: 20px;
            flex-grow: 1;
        }

        .blog-read-more {
            color: var(--primary-green);
            font-weight: 600;
            text-decoration: none;
            transition: all 0.3s ease;
            align-self: flex-start;
        }

        .blog-read-more:hover {
            color: var(--primary-brown);
            text-decoration: underline;
        }

        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
    </style>

    <section id="gallery" class="py-5" style="background: #f8f9fa;">
        <div class="container">
            <div class="section-heading text-center mb-5">
                <h2 style="font-size: 2.5rem; font-weight: 700; color: var(--primary-dark); margin-bottom: 10px;">Gallery</h2>
                <p style="font-size: 1.1rem; color: #666; margin-bottom: 20px;">A quick look at our work, people, and project moments.</p>
                <div class="line mx-auto"></div>
            </div>
            <div class="row">
                @forelse($galleryItems as $item)
                    <div class="col-md-6 col-lg-4 mb-4">
                        <div class="media-card">
                            <div class="media-image" style="background-image: linear-gradient(rgba(22, 69, 110, 0.15), rgba(22, 87, 82, 0.25)), url('{{ $item->image }}');"></div>
                            <div class="media-content">
                                <h4>{{ $item->title }}</h4>
                            </div>
                        </div>
                    </div>
                @empty
                    @foreach($fallbackGalleryItems as $item)
                        <div class="col-md-6 col-lg-4 mb-4">
                            <div class="media-card">
                                <div class="media-image" style="background-image: linear-gradient(rgba(22, 69, 110, 0.15), rgba(22, 87, 82, 0.25)), url('{{ $item['image'] }}');"></div>
                                <div class="media-content">
                                    <h4>{{ $item['title'] }}</h4>
                                </div>
                            </div>
                        </div>
                    @endforeach
                @endforelse
            </div>
        </div>
    </section>

    <section id="news-events" class="py-5">
        <div class="container">
            <div class="section-heading text-center mb-5">
                <h2 style="font-size: 2.5rem; font-weight: 700; color: var(--primary-dark); margin-bottom: 10px;">News and Events</h2>
                <p style="font-size: 1.1rem; color: #666; margin-bottom: 20px;">Latest stories, launches, and highlights from {{ $settings->site_name }}.</p>
                <div class="line mx-auto"></div>
            </div>
            <div class="row">
                @forelse($newsEvents as $item)
                    <div class="col-md-6 col-lg-4 mb-4">
                        <div class="media-card">
                            <div class="media-image" style="background-image: linear-gradient(rgba(22, 69, 110, 0.2), rgba(22, 87, 82, 0.3)), url('{{ $item->image ?? asset('views/frontend/img/blog-2.jpg') }}');"></div>
                            <div class="media-content">
                                <span class="media-badge">{{ $item->event_date ? 'Event' : 'News' }}</span>
                                <h4>{{ $item->title }}</h4>
                                <p>{{ $item->excerpt ?: \Illuminate\Support\Str::limit($item->content, 110) }}</p>
                                @if($item->event_date)
                                    <a href="#contact" class="media-link">{{ $item->event_date->format('M d, Y H:i') }}</a>
                                @endif
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="col-md-6 col-lg-4 mb-4">
                        <div class="media-card">
                            <div class="media-image" style="background-image: linear-gradient(rgba(22, 69, 110, 0.2), rgba(22, 87, 82, 0.3)), url('{{ asset('views/frontend/img/blog-2.jpg') }}');"></div>
                            <div class="media-content">
                                <span class="media-badge">Event</span>
                                <h4>Upcoming client engagement sessions</h4>
                                <p>We are preparing more updates, launches, and events to share here soon.</p>
                            </div>
                        </div>
                    </div>
                @endforelse
            </div>
        </div>
    </section>

    <section id="videos" class="py-5" style="background: #f8f9fa;">
        <div class="container">
            <div class="section-heading text-center mb-5">
                <h2 style="font-size: 2.5rem; font-weight: 700; color: var(--primary-dark); margin-bottom: 10px;">Videos</h2>
                <p style="font-size: 1.1rem; color: #666; margin-bottom: 20px;">Short visual stories about our work, products, and impact.</p>
                <div class="line mx-auto"></div>
            </div>
            <div class="row">
                @forelse($videos as $video)
                    <div class="col-md-6 col-lg-4 mb-4">
                        <div class="media-card video-card">
                            @php
                                $videoUrl = $video->video_url;
                                $embedUrl = null;

                                if ($videoUrl && str_contains($videoUrl, 'youtube.com/watch?v=')) {
                                    $embedUrl = 'https://www.youtube.com/embed/' . \Illuminate\Support\Str::after($videoUrl, 'v=');
                                } elseif ($videoUrl && str_contains($videoUrl, 'youtu.be/')) {
                                    $embedUrl = 'https://www.youtube.com/embed/' . \Illuminate\Support\Str::afterLast($videoUrl, '/');
                                } elseif ($videoUrl && str_contains($videoUrl, 'vimeo.com/')) {
                                    $embedUrl = 'https://player.vimeo.com/video/' . \Illuminate\Support\Str::afterLast($videoUrl, '/');
                                }
                            @endphp

                            @if($video->video_path)
                                <video
                                    src="{{ $video->video_path }}"
                                    autoplay
                                    controls
                                    muted
                                    loop
                                    playsinline
                                    preload="metadata"
                                    class="short-autoplay-video"
                                    data-autoplay-threshold="30"
                                    style="width:100%;height:220px;object-fit:cover;"
                                ></video>
                            @elseif($embedUrl)
                                <iframe src="{{ $embedUrl }}" title="{{ $video->title }}" style="width:100%;height:220px;border:0;" allowfullscreen></iframe>
                            @elseif($videoUrl)
                                <a href="{{ $videoUrl }}" target="_blank" rel="noopener" class="video-external-link">
                                    <div class="video-icon">
                                        <i class="fas fa-play"></i>
                                    </div>
                                    <span>Open Video</span>
                                </a>
                            @else
                                <div class="video-icon">
                                    <i class="fas fa-play"></i>
                                </div>
                            @endif
                            <div class="media-content">
                                <span class="media-badge">Video</span>
                                <h4>{{ $video->title }}</h4>
                                <p>{{ $video->description }}</p>
                            </div>
                        </div>
                    </div>
                @empty
                    @foreach($fallbackVideoItems as $video)
                        <div class="col-md-6 col-lg-4 mb-4">
                            <div class="media-card video-card">
                                <div class="video-icon">
                                    <i class="fas fa-play"></i>
                                </div>
                                <div class="media-content">
                                    <span class="media-badge">Video</span>
                                    <h4>{{ $video['title'] }}</h4>
                                    <p>{{ $video['description'] }}</p>
                                </div>
                            </div>
                        </div>
                    @endforeach
                @endforelse
            </div>
        </div>
    </section>

    <section id="team" class="py-5">
        <div class="container">
            <div class="section-heading text-center mb-5">
                <h2 style="font-size: 2.5rem; font-weight: 700; color: var(--primary-dark); margin-bottom: 10px;">Our Team</h2>
                <p style="font-size: 1.1rem; color: #666; margin-bottom: 20px;">The people behind our delivery, support, and innovation.</p>
                <div class="line mx-auto"></div>
            </div>
            <div class="row">
                @forelse($teamMembers as $member)
                    <div class="col-md-6 col-lg-4 mb-4">
                        <div class="team-member-card">
                            <img src="{{ $member->image ?? asset('views/frontend/img/team-4.jpg') }}" alt="{{ $member->name }}" class="team-member-image">
                            <div class="team-member-content">
                                <h4>{{ $member->name }}</h4>
                                <div class="team-member-role">{{ $member->position }}</div>
                                <p class="team-member-preview">{{ \Illuminate\Support\Str::limit($member->description, 120) }}</p>
                                @if(strlen($member->description) > 120)
                                    <p class="team-member-full d-none">{{ $member->description }}</p>
                                    <button class="read-more-link" onclick="toggleTeamContent(this)">Read More »</button>
                                @endif
                                <div class="mt-3">
                                    @foreach(['facebook_url' => 'facebook-f', 'twitter_url' => 'twitter', 'linkedin_url' => 'linkedin-in', 'instagram_url' => 'instagram'] as $key => $icon)
                                        @if($member->{$key})
                                            <a href="{{ $member->{$key} }}" target="_blank" rel="noopener" class="media-link mr-3"><i class="fab fa-{{ $icon }}"></i></a>
                                        @endif
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    </div>
                @empty
                    @foreach($fallbackTeamMembers as $member)
                        <div class="col-md-6 col-lg-4 mb-4">
                            <div class="team-member-card">
                                <img src="{{ $member['image'] }}" alt="{{ $member['name'] }}" class="team-member-image">
                                <div class="team-member-content">
                                    <h4>{{ $member['name'] }}</h4>
                                    <div class="team-member-role">{{ $member['role'] }}</div>
                                    <p>{{ $member['bio'] }}</p>
                                </div>
                            </div>
                        </div>
                    @endforeach
                @endforelse
            </div>
        </div>
    </section>

    <style>
        .media-card,
        .team-member-card {
            background: white;
            border-radius: 16px;
            overflow: hidden;
            box-shadow: 0 12px 30px rgba(22, 69, 110, 0.08);
            height: 100%;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            position: relative;
            isolation: isolate;
        }

        .media-card:hover,
        .team-member-card:hover {
            transform: translateY(-8px);
            box-shadow: 0 18px 36px rgba(22, 69, 110, 0.16);
        }

        .media-card::before,
        .team-member-card::before {
            content: "";
            position: absolute;
            inset: -35%;
            background: conic-gradient(
                from 0deg,
                transparent 0deg,
                transparent 250deg,
                rgba(22, 87, 82, 0.95) 300deg,
                rgba(47, 111, 142, 0.95) 335deg,
                rgba(255, 255, 255, 0.18) 360deg
            );
            opacity: 0;
            transition: opacity 0.35s ease;
            z-index: -2;
            pointer-events: none;
        }

        .media-card::after,
        .team-member-card::after {
            content: "";
            position: absolute;
            inset: 2px;
            border-radius: 14px;
            background: white;
            z-index: -1;
            pointer-events: none;
        }

        .media-card:hover::before,
        .team-member-card:hover::before {
            opacity: 1;
            animation: imageOrbit 2.2s linear infinite;
        }

        .media-image {
            min-height: 230px;
            background-position: center;
            background-size: cover;
        }

        .media-content,
        .team-member-content {
            padding: 24px;
        }

        .media-content h4,
        .team-member-content h4 {
            color: var(--primary-dark);
            font-weight: 700;
            margin-bottom: 10px;
        }

        .media-content p,
        .team-member-content p {
            color: #66727d;
            line-height: 1.7;
            margin-bottom: 0;
        }

        .media-badge {
            display: inline-block;
            margin-bottom: 12px;
            padding: 6px 12px;
            border-radius: 999px;
            background: rgba(22, 87, 82, 0.1);
            color: var(--primary-green);
            font-size: 0.78rem;
            font-weight: 700;
            letter-spacing: 0.08em;
            text-transform: uppercase;
        }

        .media-link {
            display: inline-block;
            margin-top: 14px;
            color: var(--primary-green);
            font-weight: 600;
            text-decoration: none;
            background: none;
            border: none;
            padding: 0;
            cursor: pointer;
        }

        .video-card {
            display: flex;
            flex-direction: column;
            justify-content: center;
            min-height: 100%;
            background: linear-gradient(145deg, rgba(22, 69, 110, 0.98), rgba(22, 87, 82, 0.94));
        }

        .video-card::after {
            background: linear-gradient(145deg, rgba(22, 69, 110, 0.98), rgba(22, 87, 82, 0.94));
        }

        .video-card .media-content h4,
        .video-card .media-content p,
        .video-card .media-badge {
            color: white;
        }

        .video-card .media-badge {
            background: rgba(255, 255, 255, 0.16);
        }

        .video-external-link {
            min-height: 220px;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-direction: column;
            color: white;
            text-decoration: none;
        }

        .video-icon {
            width: 82px;
            height: 82px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            background: rgba(255, 255, 255, 0.14);
            color: white;
            font-size: 1.4rem;
            margin: 28px 24px 0;
        }

        .team-member-image {
            width: 100%;
            height: 280px;
            object-fit: cover;
        }

        .team-member-role {
            margin-bottom: 12px;
            color: var(--primary-green);
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.06em;
            font-size: 0.82rem;
        }

        @keyframes imageOrbit {
            from {
                transform: rotate(0deg);
            }
            to {
                transform: rotate(360deg);
            }
        }
    </style>

    <section id="contact" class="py-5">
        @php
            $primaryPhone = optional(collect($contacts ?? [])->firstWhere('type', 'phone'))->value ?? '+254...';
            $primaryEmail = optional(collect($contacts ?? [])->firstWhere('type', 'email'))->value ?? 'info@.....';
            $primaryAddress = optional(collect($contacts ?? [])->firstWhere('type', 'address'))->value ?? 'Our office location will appear here.';
        @endphp
        <div class="container">
            <div class="section-heading text-center mb-5">
                <h2 style="font-size: 2.5rem; font-weight: 700; color: var(--primary-dark); margin-bottom: 10px;">Contact Us</h2>
                <p style="font-size: 1.1rem; color: #666; margin-bottom: 20px;">For Any Queries, Feel Free To Contact Us</p>
                <div class="line mx-auto"></div>
            </div>
            <div class="row">
                <div class="col-lg-6 mb-5">
                    <div class="contact-info">
                        <div class="contact-item fade-in-left" style="animation-delay: 0.1s;">
                            <div class="contact-icon">
                                <i class="fas fa-phone-alt" style="font-size: 2rem; color: var(--primary-green);"></i>
                            </div>
                            <div class="contact-content">
                                <h4>Call to ask any question</h4>
                                <p>{{ $primaryPhone }}</p>
                            </div>
                        </div>

                        <div class="contact-item fade-in-left" style="animation-delay: 0.2s;">
                            <div class="contact-icon">
                                <i class="fas fa-envelope" style="font-size: 2rem; color: var(--primary-green);"></i>
                            </div>
                            <div class="contact-content">
                                <h4>Email to get free quote</h4>
                                <p>{{ $primaryEmail }}</p>
                            </div>
                        </div>

                        <div class="contact-item fade-in-left" style="animation-delay: 0.3s;">
                            <div class="contact-icon">
                                <i class="fas fa-map-marker-alt" style="font-size: 2rem; color: var(--primary-green);"></i>
                            </div>
                            <div class="contact-content">
                                <h4>Visit our office</h4>
                                <p>{{ $primaryAddress }}</p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-lg-6">
                    <div class="contact-form-wrapper fade-in-right" style="animation-delay: 0.4s;">
                        <h3 class="mb-4" style="color: var(--primary-dark); font-weight: 600;">Enquire</h3>
                        
                        @if(session('success'))
                            <div class="alert alert-success alert-dismissible fade show" role="alert">
                                {{ session('success') }}
                                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                    <span aria-hidden="true">&times;</span>
                                </button>
                            </div>
                        @endif
                        
                        <form class="contact-form" method="POST" action="{{ route('contact.submit') }}">
                            @csrf
                            <div class="form-row">
                                <div class="col-md-6 mb-3">
                                    <input type="text" name="name" class="form-control contact-input" placeholder="Your Name" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <input type="email" name="email" class="form-control contact-input" placeholder="Your Email" required>
                                </div>
                            </div>
                            <div class="mb-3">
                                <input type="text" name="subject" class="form-control contact-input" placeholder="Subject" required>
                            </div>
                            <div class="mb-4">
                                <textarea name="message" class="form-control contact-textarea" rows="5" placeholder="Details of the Enquiry" required></textarea>
                            </div>
                            <button type="submit" class="btn contact-submit-btn">Send Message</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <style>
        .contact-info {
            padding: 20px;
        }

        .contact-item {
            display: flex;
            align-items: flex-start;
            margin-bottom: 30px;
            padding: 20px;
            background: white;
            border-radius: 12px;
            box-shadow: 0 4px 15px rgba(22, 69, 110, 0.08);
            transition: all 0.3s ease;
            animation: fadeInLeft 0.8s ease-out forwards;
            opacity: 0;
        }

        .contact-item:hover {
            transform: translateX(10px);
            box-shadow: 0 8px 25px rgba(22, 69, 110, 0.15);
        }

        .contact-icon {
            flex-shrink: 0;
            width: 60px;
            height: 60px;
            background: rgba(22, 87, 82, 0.08);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 20px;
            transition: all 0.3s ease;
        }

        .contact-item:hover .contact-icon {
            background: rgba(22, 87, 82, 0.2);
            transform: scale(1.1);
        }

        .contact-content h4 {
            font-size: 1.2rem;
            font-weight: 600;
            color: var(--primary-dark);
            margin-bottom: 5px;
        }

        .contact-content p {
            color: #666;
            margin: 0;
            font-size: 1rem;
        }

        .contact-form-wrapper {
            background: white;
            padding: 40px;
            border-radius: 12px;
            box-shadow: 0 4px 15px rgba(22, 69, 110, 0.08);
            animation: fadeInRight 0.8s ease-out forwards;
            opacity: 0;
        }

        .contact-input, .contact-textarea {
            border: 2px solid #e9ecef;
            border-radius: 8px;
            padding: 12px 16px;
            font-size: 1rem;
            transition: all 0.3s ease;
            background: #f8f9fa;
        }

        .contact-input:focus, .contact-textarea:focus {
            border-color: var(--primary-green);
            background: white;
            box-shadow: 0 0 0 0.2rem rgba(22, 87, 82, 0.25);
            outline: none;
        }

        .contact-input::placeholder, .contact-textarea::placeholder {
            color: #6c757d;
            opacity: 0.7;
        }

        .contact-submit-btn {
            background: var(--primary-green);
            color: white;
            border: 2px solid var(--primary-green);
            border-radius: 25px;
            padding: 12px 30px;
            font-weight: 600;
            font-size: 1rem;
            transition: all 0.3s ease;
            cursor: pointer;
            width: 100%;
        }

        .contact-submit-btn:hover {
            background: var(--primary-brown);
            border-color: var(--primary-brown);
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(22, 69, 110, 0.3);
        }

        @keyframes fadeInLeft {
            from {
                opacity: 0;
                transform: translateX(-50px);
            }
            to {
                opacity: 1;
                transform: translateX(0);
            }
        }

        @keyframes fadeInRight {
            from {
                opacity: 0;
                transform: translateX(50px);
            }
            to {
                opacity: 1;
                transform: translateX(0);
            }
        }

        @media (max-width: 768px) {
            .contact-form-wrapper {
                padding: 30px 20px;
            }

            .contact-item {
                flex-direction: column;
                text-align: center;
                padding: 20px 15px;
            }

            .contact-icon {
                margin-right: 0;
                margin-bottom: 15px;
            }
        }
    </style>
@endsection
