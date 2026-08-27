@extends('layouts.storefront')

@section('title', 'Home')

@push('styles')
<style>
    .home-grid { display: grid; grid-template-columns: 260px 1fr; gap: 16px; margin-top: 16px; }
    .side-cats { padding: 14px; }
    .side-cats h3 { font-size: 15px; margin: 0 0 10px; }
    .side-cats a { display: flex; justify-content: space-between; align-items: center; padding: 9px 8px; border-radius: 8px; font-size: 14px; color: #374151; }
    .side-cats a:hover { background: #f3f4f6; color: var(--brand); }
    .hero {
        min-height: 340px; padding: 28px; color: #fff; position: relative; overflow: hidden;
        background: linear-gradient(130deg, #121212 0%, #1a1a1a 48%, #c9a227 160%);
    }
    .hero h1 { margin: 0 0 10px; font-size: 34px; max-width: 420px; }
    .hero p { margin: 0; max-width: 480px; opacity: 0.95; }
    .hero-actions { margin-top: 18px; }
    .hero-collage {
        position: absolute; right: 18px; top: 18px; bottom: 64px; width: min(46%, 420px);
        display: grid; grid-template-columns: 1.2fr 1fr; grid-template-rows: 1fr 1fr; gap: 8px;
    }
    .hero-collage span { border-radius: 12px; background: #fff center/cover no-repeat; box-shadow: 0 10px 20px rgba(0,0,0,.18); }
    .hero-collage span:first-child { grid-row: 1 / span 2; }
    .trust { display: flex; gap: 18px; margin-top: 28px; flex-wrap: wrap; font-size: 13px; font-weight: 700; }
    .section { margin-top: 22px; }
    .section-head { display: flex; justify-content: space-between; align-items: center; margin: 0 0 12px; }
    .section-head h2 { font-size: 20px; margin: 0; }
    .section-head a { color: var(--brand); font-weight: 700; font-size: 14px; }
    .deal-grid { display: grid; gap: 12px; grid-template-columns: repeat(6, minmax(0, 1fr)); }
    .product-grid { display: grid; gap: 12px; grid-template-columns: repeat(4, minmax(0, 1fr)); }
    .cat-ico { margin-right: 8px; }
    @media (max-width: 1100px) {
        .deal-grid { grid-template-columns: repeat(4, minmax(0, 1fr)); }
        .product-grid { grid-template-columns: repeat(3, minmax(0, 1fr)); }
        .hero-collage { display: none; }
    }
    @media (max-width: 900px) {
        .home-grid { grid-template-columns: 1fr; }
        .deal-grid, .product-grid { grid-template-columns: repeat(2, minmax(0, 1fr)); }
        .hero { min-height: auto; }
    }
</style>
@endpush

@section('content')
<div class="container">
    <div class="home-grid">
        <aside class="card side-cats">
            <h3>Shop by Categories</h3>
            @forelse($categories as $category)
                <a href="{{ route('shop.index', ['category_id' => $category->id]) }}">
                    <span><span class="cat-ico">{{ [
                        'Supermarket' => '🛒',
                        'Phones' => '📱',
                        'Computing' => '💻',
                        'Fashion' => '👗',
                        'Electronics' => '📺',
                        'Suits' => '👔',
                        'Clothes' => '👕',
                        'Bags' => '👜',
                        'Uniforms' => '🦺',
                        'Shoes' => '👟',
                    ][$category->name] ?? '•' }}</span>{{ $category->name }}</span>
                    <span>›</span>
                </a>
            @empty
                <a href="{{ route('shop.index') }}"><span>All Products</span><span>›</span></a>
            @endforelse
        </aside>

        <section class="card hero">
            <h1>Welcome to your new marketplace</h1>
            <p>Shop suits, clothes, bags, uniforms and shoes. Discover daily deals and enjoy fast delivery.</p>
            <div class="hero-actions">
                <a class="btn btn-primary" href="{{ route('shop.index') }}">Start Shopping</a>
            </div>
            @if(($heroImages ?? collect())->isNotEmpty())
                <div class="hero-collage">
                    @foreach($heroImages->take(3) as $image)
                        <span style="background-image:url('{{ $image }}');"></span>
                    @endforeach
                </div>
            @endif
            <div class="trust">
                <span>✓ Fast Delivery</span>
                <span>✓ Secure Payments</span>
                <span>✓ Best Prices</span>
            </div>
        </section>
    </div>

    <section class="section">
        <div class="section-head">
            <h2>Flash Sale</h2>
            <a href="{{ route('shop.index') }}">View all ›</a>
        </div>
        <div class="deal-grid">
            @forelse($deals as $deal)
                @include('frontend.partials.product-card', ['product' => $deal, 'showRating' => false, 'showActions' => false])
            @empty
                <article class="card" style="padding:16px;">No deals configured yet.</article>
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
                <article class="card" style="padding:16px;">No products available.</article>
            @endforelse
        </div>
    </section>
</div>
@endsection
