@extends('layouts.admin')

@section('title','Dashboard')

@section('content')
    <style>
        .dashboard-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
            gap: 16px;
            margin-bottom: 20px;
        }
        .stat-card {
            background: linear-gradient(145deg, #ffffff, #f6fbfb);
            border: 1px solid #e4ecec;
        }
        .stat-card h3 {
            margin: 0 0 8px;
            font-size: 2rem;
            color: var(--primary-dark);
        }
        .stat-card p {
            margin: 0;
            color: #4d5b66;
        }
        .two-col {
            display: grid;
            grid-template-columns: minmax(0, 1.2fr) minmax(0, 0.8fr);
            gap: 20px;
        }
        .form-grid {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 16px;
        }
        .form-grid .full {
            grid-column: 1 / -1;
        }
        .preview-logo {
            width: 72px;
            height: 72px;
            object-fit: cover;
            border-radius: 14px;
            border: 1px solid #d9e0e5;
            margin-top: 10px;
        }
        .feature-list {
            display: grid;
            gap: 12px;
        }
        .feature-item {
            padding: 14px;
            border-radius: 10px;
            background: #f8fbfc;
            border: 1px solid #e6eef2;
        }
        .feature-item strong {
            display: block;
            margin-bottom: 6px;
            color: var(--primary-dark);
        }
        label {
            display: block;
            font-weight: 600;
            margin-bottom: 6px;
        }
        input[type=text],
        input[type=file],
        textarea {
            width: 100%;
            padding: 10px 12px;
            border: 1px solid #d7dde2;
            border-radius: 8px;
            box-sizing: border-box;
        }
        textarea {
            min-height: 110px;
            resize: vertical;
        }
        .hint {
            color: #65727d;
            font-size: 0.9rem;
            margin-top: 6px;
        }
        .plan-list {
            margin: 0;
            padding-left: 18px;
        }
        @media (max-width: 960px) {
            .two-col,
            .form-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>

    <h2>Dashboard</h2>
    <div class="card">
        <p>Welcome, {{ auth()->user()?->name ?? 'Admin' }}. This dashboard now lets you manage the site branding and keep an eye on the frontend sections your visitors see.</p>
    </div>

    <div class="dashboard-grid">
        <div class="card stat-card">
            <h3>{{ $aboutCount }}</h3>
            <p>About sections</p>
        </div>
        <div class="card stat-card">
            <h3>{{ $serviceCount }}</h3>
            <p>Service cards</p>
        </div>
        <div class="card stat-card">
            <h3>{{ $productCount }}</h3>
            <p>Products</p>
        </div>
        <div class="card stat-card">
            <h3>{{ $activeProductCount }}</h3>
            <p>Active products</p>
        </div>
        <div class="card stat-card" style="border-left:4px solid #dc2626;">
            <h3 style="color:#b91c1c;">{{ $lowStockCount }}</h3>
            <p>Low stock (<= 5)</p>
        </div>
        <div class="card stat-card">
            <h3>{{ $orderCount }}</h3>
            <p>Total orders</p>
        </div>
        <div class="card stat-card" style="border-left:4px solid #c9a227;">
            <h3 style="color:#7b6116;">{{ $pendingOrderCount }}</h3>
            <p>Pending/processing orders</p>
        </div>
        <div class="card stat-card" style="border-left:4px solid #16a34a;">
            <h3 style="color:#15803d;">KES {{ number_format($monthlyRevenue, 2) }}</h3>
            <p>Revenue this month</p>
        </div>
        <div class="card stat-card">
            <h3>{{ $priceCount }}</h3>
            <p>Pricing plans</p>
        </div>
        <div class="card stat-card">
            <h3>{{ $pricingFeatureCount }}</h3>
            <p>Pricing features</p>
        </div>
        <div class="card stat-card">
            <h3>{{ $blogCount }}</h3>
            <p>Blog posts</p>
        </div>
        <div class="card stat-card">
            <h3>{{ $galleryCount }}</h3>
            <p>Gallery items</p>
        </div>
        <div class="card stat-card">
            <h3>{{ $newsEventCount }}</h3>
            <p>News & events</p>
        </div>
        <div class="card stat-card">
            <h3>{{ $videoCount }}</h3>
            <p>Videos</p>
        </div>
        <div class="card stat-card">
            <h3>{{ $teamCount }}</h3>
            <p>Team members</p>
        </div>
        <div class="card stat-card">
            <h3>{{ $contactCount }}</h3>
            <p>Contact entries</p>
        </div>
        <div class="card stat-card" style="border-left:4px solid #c9a227;">
            <h3 style="color:#7b6116;">{{ $enquiryCount }}</h3>
            <p>Enquiries received</p>
        </div>
        <div class="card stat-card" style="border-left:4px solid #7c3aed;">
            <h3 style="color:#6d28d9;">{{ $subscriberCount }}</h3>
            <p>Newsletter subscribers</p>
        </div>
        <div class="card stat-card" style="border-left:4px solid #2196f3;">
            <h3 style="color:#1565c0;">{{ $quoteCount }}</h3>
            <p>Quote requests <strong style="color:#2196f3;">({{ $newQuoteCount }} new)</strong></p>
        </div>
        <div class="card stat-card" style="border-left:4px solid #2e7d32;">
            <h3 style="color:#2e7d32;">{{ $quotationCount }}</h3>
            <p>Quotations generated</p>
        </div>
    </div>

    <div class="two-col">
        <div class="card">
            <h3>Brand Settings</h3>
            <form method="POST" action="{{ route('admin.settings.update') }}" enctype="multipart/form-data">
                @csrf
                <div class="form-grid">
                    <div>
                        <label for="site_name">System Name</label>
                        <input id="site_name" type="text" name="site_name" value="{{ old('site_name', $settings->site_name) }}" required>
                    </div>
                    <div>
                        <label for="site_tagline">Tagline</label>
                        <input id="site_tagline" type="text" name="site_tagline" value="{{ old('site_tagline', $settings->site_tagline) }}" placeholder="ICT Consultancy">
                    </div>
                    <div class="full">
                        <label for="footer_text">Footer Text</label>
                        <textarea id="footer_text" name="footer_text" placeholder="Shown in the footer copyright area">{{ old('footer_text', $settings->footer_text) }}</textarea>
                        <div class="hint">If left empty, the footer uses the system name and tagline automatically.</div>
                    </div>
                    <div class="full">
                        <label for="logo">Logo</label>
                        <input id="logo" type="file" name="logo" accept="image/*">
                        <div class="hint">Upload a logo once and it will be reused across the frontend and admin area.</div>
                        @if(!empty($settings->logo))
                            <img src="{{ $settings->logo }}" alt="{{ $settings->site_name }}" class="preview-logo">
                        @endif
                    </div>
                </div>
                <button type="submit" style="margin-top: 16px;">Save Settings</button>
            </form>
        </div>

        <div class="card">
            <h3>Frontend Features</h3>
            <div class="feature-list">
                <div class="feature-item">
                    <strong>Hero and content sections</strong>
                    Slider content pulls from published blog posts, while About and Services are managed from the side menu.
                </div>
                <div class="feature-item">
                    <strong>Pricing plans and plan features</strong>
                    {{ $featuredPlanCount }} featured plan(s) are highlighted on the homepage, and each plan can include its own list of frontend features.
                </div>
                <div class="feature-item">
                    <strong>Contact and trust content</strong>
                    Contact details feed the footer and top bar, while blog posts enrich the homepage and blog listing.
                </div>
                <div class="feature-item">
                    <strong>Media and people sections</strong>
                    Gallery, News and Events, Videos, and Team are now managed from the admin side and rendered on the homepage.
                </div>
                <div class="feature-item">
                    <strong>Latest plan preview</strong>
                    @if($latestPlans->isNotEmpty())
                        <ul class="plan-list">
                            @foreach($latestPlans as $plan)
                                <li>{{ $plan->title }} ({{ is_array($plan->features) ? count($plan->features) : 0 }} features)</li>
                            @endforeach
                        </ul>
                    @else
                        No pricing plans added yet.
                    @endif
                </div>
            </div>
        </div>
    </div>
@endsection
