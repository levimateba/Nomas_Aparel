@extends('layouts.storefront')

@section('title', 'Home')

@push('styles')
<style>
    .home-grid {
        display: grid;
        grid-template-columns: 260px minmax(0, 1fr);
        gap: 16px;
        margin-top: 18px;
    }
    .side-cats {
        padding: 8px 0;
        overflow: hidden;
        align-self: start;
    }
    .side-cats h3 {
        font-size: 13px;
        margin: 0;
        padding: 14px 16px 10px;
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: .04em;
        color: #6b7280;
    }
    .side-cats a {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 11px 16px;
        font-size: 14px;
        font-weight: 600;
        color: #374151;
        border-top: 1px solid #f3f4f6;
        transition: background .15s ease, color .15s ease, padding-left .15s ease;
    }
    .side-cats a:hover {
        background: #faf8f2;
        color: var(--brand);
        padding-left: 20px;
    }
    .side-cats .cat-left {
        display: inline-flex;
        align-items: center;
        gap: 10px;
        min-width: 0;
    }
    .side-cats .cat-ico {
        width: 28px; height: 28px; border-radius: 8px;
        display: grid; place-items: center;
        background: #f6f0df; color: var(--brand);
        flex: 0 0 28px;
    }
    .side-cats .cat-ico svg { width: 15px; height: 15px; }

    .hero {
        min-height: 380px;
        padding: 40px 36px;
        color: #fff;
        position: relative;
        overflow: hidden;
        border: 0;
        border-radius: 18px;
        background:
            linear-gradient(105deg, rgba(15,23,42,.88) 0%, rgba(15,23,42,.55) 48%, rgba(15,23,42,.25) 100%),
            #1f2937 center/cover no-repeat;
        display: flex;
        flex-direction: column;
        justify-content: center;
        animation: heroIn .7s ease both;
    }
    .hero.has-img { background-blend-mode: normal; }
    .hero-kicker {
        margin: 0 0 10px;
        font-size: 12px;
        font-weight: 800;
        letter-spacing: .14em;
        text-transform: uppercase;
        color: var(--brand-soft);
    }
    .hero h1 {
        margin: 0;
        font-family: "Instrument Serif", Georgia, serif;
        font-size: clamp(2rem, 4vw, 3.1rem);
        font-weight: 400;
        max-width: 520px;
        line-height: 1.1;
        letter-spacing: -.02em;
    }
    .hero h1 em {
        font-style: italic;
        color: #f6e7b2;
    }
    .hero p {
        margin: 14px 0 0;
        max-width: 460px;
        opacity: .92;
        font-size: 15px;
        line-height: 1.55;
        font-weight: 500;
    }
    .hero-actions { margin-top: 22px; }
    .hero-actions .btn {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        border-radius: 999px;
        padding: 13px 22px;
        font-size: 14px;
        font-weight: 800;
        box-shadow: 0 10px 24px rgba(165,129,18,.35);
        transition: transform .15s ease, box-shadow .15s ease;
    }
    .hero-actions .btn:hover {
        transform: translateY(-2px);
        box-shadow: 0 14px 28px rgba(165,129,18,.4);
    }
    .hero-badge {
        position: absolute;
        right: 22px;
        bottom: 22px;
        width: min(42%, 280px);
        padding: 16px;
        border-radius: 16px;
        background: rgba(255,255,255,.12);
        border: 1px solid rgba(255,255,255,.2);
        backdrop-filter: blur(10px);
        box-shadow: 0 12px 28px rgba(0,0,0,.2);
        animation: badgeIn .8s .15s ease both;
    }
    .hero-badge strong {
        display: block;
        font-size: 14px;
        font-weight: 800;
        margin-bottom: 6px;
    }
    .hero-badge span {
        display: block;
        font-size: 12px;
        opacity: .85;
        line-height: 1.4;
    }
    .hero-badge img {
        width: 100%;
        height: 120px;
        object-fit: cover;
        border-radius: 12px;
        margin-bottom: 10px;
        display: block;
        background: #111;
    }
    .trust {
        display: flex;
        gap: 18px;
        margin-top: 28px;
        flex-wrap: wrap;
        font-size: 13px;
        font-weight: 700;
    }
    .trust span {
        display: inline-flex;
        align-items: center;
        gap: 8px;
    }
    .trust svg { width: 16px; height: 16px; color: var(--brand-soft); flex: 0 0 auto; }

    .features {
        display: grid;
        grid-template-columns: repeat(5, minmax(0, 1fr));
        gap: 12px;
        margin-top: 18px;
    }
    .feature {
        background: #fff;
        border: 1px solid #ececec;
        border-radius: 14px;
        padding: 16px 14px;
        display: flex;
        align-items: flex-start;
        gap: 12px;
        transition: transform .18s ease, border-color .18s ease, box-shadow .18s ease;
    }
    .feature:hover {
        transform: translateY(-2px);
        border-color: rgba(165,129,18,.35);
        box-shadow: 0 10px 22px rgba(15,23,42,.06);
    }
    .feature-ico {
        width: 40px; height: 40px; border-radius: 12px;
        display: grid; place-items: center;
        background: #f6f0df; color: var(--brand);
        flex: 0 0 40px;
    }
    .feature-ico svg { width: 20px; height: 20px; }
    .feature strong { display: block; font-size: 13px; font-weight: 800; color: #111827; }
    .feature small { display: block; margin-top: 3px; font-size: 12px; color: #6b7280; line-height: 1.35; }

    .section { margin-top: 28px; }
    .section-head {
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 12px;
        margin: 0 0 14px;
        flex-wrap: wrap;
    }
    .section-head h2 {
        font-size: 22px;
        margin: 0;
        font-weight: 800;
        letter-spacing: -.02em;
        display: inline-flex;
        align-items: center;
        gap: 8px;
    }
    .section-head h2 svg { color: #dc2626; }
    .section-head a { color: var(--brand); font-weight: 700; font-size: 14px; }
    .flash-timer {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        margin-left: 8px;
    }
    .flash-timer .unit {
        display: inline-flex;
        flex-direction: column;
        align-items: center;
        min-width: 42px;
        padding: 6px 8px;
        border-radius: 10px;
        background: #dc2626;
        color: #fff;
        line-height: 1.1;
    }
    .flash-timer .unit b { font-size: 14px; font-weight: 800; }
    .flash-timer .unit small { font-size: 9px; font-weight: 700; opacity: .9; text-transform: uppercase; }
    .flash-timer .sep { font-weight: 800; color: #dc2626; }

    .deal-grid { display: grid; gap: 14px; grid-template-columns: repeat(6, minmax(0, 1fr)); }
    .product-grid { display: grid; gap: 14px; grid-template-columns: repeat(4, minmax(0, 1fr)); }

    @keyframes heroIn {
        from { opacity: 0; transform: translateY(10px); }
        to { opacity: 1; transform: none; }
    }
    @keyframes badgeIn {
        from { opacity: 0; transform: translateY(12px) scale(.98); }
        to { opacity: 1; transform: none; }
    }

    @media (max-width: 1100px) {
        .deal-grid { grid-template-columns: repeat(3, minmax(0, 1fr)); }
        .product-grid { grid-template-columns: repeat(3, minmax(0, 1fr)); }
        .features { grid-template-columns: repeat(3, minmax(0, 1fr)); }
        .hero-badge { display: none; }
    }
    @media (max-width: 900px) {
        .home-grid { grid-template-columns: 1fr; }
        .side-cats { display: none; }
        .deal-grid, .product-grid { grid-template-columns: repeat(2, minmax(0, 1fr)); }
        .features { grid-template-columns: repeat(2, minmax(0, 1fr)); }
        .hero { min-height: 320px; padding: 28px 22px; border-radius: 14px; }
    }
    @media (max-width: 520px) {
        .features { grid-template-columns: 1fr; }
        .flash-timer { width: 100%; margin: 8px 0 0; }
    }
</style>
@endpush

@section('content')
@php
    $heroBg = ($heroImages ?? collect())->first();
    $badgeImg = ($heroImages ?? collect())->skip(1)->first() ?: $heroBg;
    $catIcons = [
        'Suits' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 4l-3 4v12h12V8l-3-4H9zm0 0h6M9 20v-6h6v6"/></svg>',
        'Clothes' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M16 4l4 4-3 2v10H7V10L4 8l4-4h8z"/></svg>',
        'Fashion' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 3l2 5 5 .5-4 3.5 1.5 5L12 14.5 7.5 17 9 12 5 8.5 10 8z"/></svg>',
        'Bags' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 8h12l1 12H5L6 8zm3-3h6v3H9V5z"/></svg>',
        'Uniforms' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 3l7 3v4c0 5-3 8-7 10-4-2-7-5-7-10V6l7-3z"/></svg>',
        'Shoes' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M4 15l8-2 8 1v3H4v-2zm0 0l1-5 4-1"/></svg>',
    ];
    $defaultCatIcon = '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M4 7h16M4 12h16M4 17h16"/></svg>';
@endphp
<div class="container">
    <div class="home-grid">
        <aside class="card side-cats">
            <h3>Shop by Category</h3>
            @forelse($categories as $category)
                <a href="{{ route('shop.index', ['category_id' => $category->id]) }}">
                    <span class="cat-left">
                        <span class="cat-ico">{!! $catIcons[$category->name] ?? $defaultCatIcon !!}</span>
                        {{ $category->name }}
                    </span>
                    <span>›</span>
                </a>
            @empty
                <a href="{{ route('shop.index') }}"><span class="cat-left"><span class="cat-ico">{!! $defaultCatIcon !!}</span>All Products</span><span>›</span></a>
            @endforelse
        </aside>

        <section class="card hero {{ $heroBg ? 'has-img' : '' }}" @if($heroBg) style="background-image: linear-gradient(105deg, rgba(15,23,42,.88) 0%, rgba(15,23,42,.55) 48%, rgba(15,23,42,.28) 100%), url('{{ $heroBg }}'); background-size: cover; background-position: center;" @endif>
            <p class="hero-kicker">Premium Apparel</p>
            <h1>Style for <em>Every Occasion</em></h1>
            <p>Discover suits, fashion, bags, uniforms and shoes crafted for confidence — with fast delivery across Kenya.</p>
            <div class="hero-actions">
                <a class="btn btn-primary" href="{{ route('shop.index') }}">Shop Now →</a>
            </div>
            <div class="trust">
                <span>
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M3 13h2l2-6h10l2 6h2M5 17h2m10 0h2M7 17a2 2 0 104 0 2 2 0 00-4 0zm6 0a2 2 0 104 0 2 2 0 00-4 0z"/></svg>
                    Fast Delivery
                </span>
                <span>
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="7" width="18" height="12" rx="2"/><path stroke-linecap="round" d="M3 11h18"/></svg>
                    Secure Payments
                </span>
                <span>
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 3l7 3v4c0 5-3 8-7 10-4-2-7-5-7-10V6l7-3z"/></svg>
                    Premium Quality
                </span>
            </div>
            @if($badgeImg)
                <div class="hero-badge">
                    <img src="{{ $badgeImg }}" alt="New collection">
                    <strong>New Collection 2026</strong>
                    <span>Fresh drops in suits, shoes &amp; everyday wear.</span>
                </div>
            @endif
        </section>
    </div>

    <div class="features">
        <div class="feature">
            <span class="feature-ico"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M3 13h2l2-6h10l2 6h2M5 17h2m10 0h2M7 17a2 2 0 104 0 2 2 0 00-4 0zm6 0a2 2 0 104 0 2 2 0 00-4 0z"/></svg></span>
            <div><strong>Free Delivery</strong><small>On orders over KES 10,000</small></div>
        </div>
        <div class="feature">
            <span class="feature-ico"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M4 7h16v10H4V7zm4-3h8v3H8V4z"/></svg></span>
            <div><strong>Easy Returns</strong><small>Hassle-free within 7 days</small></div>
        </div>
        <div class="feature">
            <span class="feature-ico"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="7" width="18" height="12" rx="2"/><path stroke-linecap="round" d="M3 11h18"/></svg></span>
            <div><strong>Secure Payments</strong><small>M-Pesa, Card &amp; more</small></div>
        </div>
        <div class="feature">
            <span class="feature-ico"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8c-1.7 0-3 .9-3 2s1.3 2 3 2 3 .9 3 2-1.3 2-3 2m0-8V7m0 9v1"/></svg></span>
            <div><strong>Best Prices</strong><small>Value without compromise</small></div>
        </div>
        <div class="feature">
            <span class="feature-ico"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M22 16.9v3a2 2 0 01-2.2 2 19.8 19.8 0 01-8.6-3.1 19.5 19.5 0 01-6-6A19.8 19.8 0 012.1 4.2 2 2 0 014.1 2h3a2 2 0 012 1.7c.1 1 .4 1.9.7 2.8a2 2 0 01-.5 2.1L8.1 9.9a16 16 0 006 6l1.3-1.3a2 2 0 012.1-.4c.9.3 1.8.6 2.8.7a2 2 0 011.7 2z"/></svg></span>
            <div><strong>24/7 Support</strong><small>We’re here when you need us</small></div>
        </div>
    </div>

    <section class="section">
        <div class="section-head">
            <div style="display:flex;align-items:center;flex-wrap:wrap;gap:8px;">
                <h2>
                    <svg width="22" height="22" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M13 2L4 14h7l-1 8 10-14h-7l0-6z"/></svg>
                    Flash Sale
                </h2>
                <div class="flash-timer" id="flash-timer" aria-label="Sale countdown">
                    <span class="unit"><b data-d>00</b><small>Days</small></span>
                    <span class="sep">:</span>
                    <span class="unit"><b data-h>00</b><small>Hrs</small></span>
                    <span class="sep">:</span>
                    <span class="unit"><b data-m>00</b><small>Min</small></span>
                    <span class="sep">:</span>
                    <span class="unit"><b data-s>00</b><small>Sec</small></span>
                </div>
            </div>
            <a href="{{ route('shop.index', ['sale' => 1]) }}">View all ›</a>
        </div>
        <div class="deal-grid">
            @forelse($deals as $deal)
                @include('frontend.partials.product-card', ['product' => $deal, 'showRating' => false, 'showActions' => true])
            @empty
                <article class="card" style="padding:16px;grid-column:1/-1;">No deals configured yet. Add sale prices to products to populate Flash Sale.</article>
            @endforelse
        </div>
    </section>

    <section class="section">
        <div class="section-head">
            <h2>Featured Products</h2>
            <a href="{{ route('shop.index') }}">View all ›</a>
        </div>
        <div class="product-grid">
            @forelse($featuredProducts as $product)
                @include('frontend.partials.product-card', ['product' => $product, 'showRating' => true, 'showActions' => true])
            @empty
                <article class="card" style="padding:16px;grid-column:1/-1;">No products available.</article>
            @endforelse
        </div>
    </section>
</div>
@endsection

@push('scripts')
<script>
(function () {
    var end = new Date();
    end.setHours(23, 59, 59, 999);
    var root = document.getElementById('flash-timer');
    if (!root) return;
    var dEl = root.querySelector('[data-d]');
    var hEl = root.querySelector('[data-h]');
    var mEl = root.querySelector('[data-m]');
    var sEl = root.querySelector('[data-s]');
    function pad(n) { return String(n).padStart(2, '0'); }
    function tick() {
        var now = new Date();
        var diff = Math.max(0, end - now);
        var s = Math.floor(diff / 1000);
        var days = Math.floor(s / 86400); s %= 86400;
        var hrs = Math.floor(s / 3600); s %= 3600;
        var mins = Math.floor(s / 60); s %= 60;
        dEl.textContent = pad(days);
        hEl.textContent = pad(hrs);
        mEl.textContent = pad(mins);
        sEl.textContent = pad(s);
    }
    tick();
    setInterval(tick, 1000);
})();
</script>
@endpush
