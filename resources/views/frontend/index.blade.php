@extends('layouts.storefront')

@section('title', 'Home')

@push('styles')
<style>
    .home-grid { display: grid; grid-template-columns: 240px 1fr; gap: 16px; margin-top: 16px; }
    .side-cats { padding: 12px; }
    .side-cats h3 { font-size: 14px; margin: 0 0 10px; }
    .side-cats a { display: block; padding: 8px; border-radius: 8px; font-size: 14px; color: #374151; }
    .side-cats a:hover { background: #f3f4f6; color: var(--brand); }
    .hero { min-height: 320px; padding: 34px; background: linear-gradient(130deg, #121212, #c9a227); color: #fff; }
    .hero h1 { margin: 0 0 10px; font-size: 34px; }
    .hero p { margin: 0; max-width: 540px; opacity: 0.95; }
    .section { margin-top: 20px; }
    .section h2 { font-size: 20px; margin: 0 0 12px; }
    .deal-grid, .product-grid { display: grid; gap: 12px; grid-template-columns: repeat(6, minmax(0, 1fr)); }
    .deal, .product { padding: 12px; }
    .product .title { font-weight: 600; font-size: 14px; margin: 4px 0 8px; min-height: 38px; }
    .price { font-weight: 800; color: #111827; }
    .muted { color: #6b7280; font-size: 12px; }
    @media (max-width: 1100px) {
        .deal-grid, .product-grid { grid-template-columns: repeat(4, minmax(0, 1fr)); }
    }
    @media (max-width: 900px) {
        .home-grid { grid-template-columns: 1fr; }
        .deal-grid, .product-grid { grid-template-columns: repeat(2, minmax(0, 1fr)); }
    }
</style>
@endpush

@section('content')
<div class="container">
    <div class="home-grid">
        <aside class="card side-cats">
            <h3>Categories</h3>
            @forelse($categories as $category)
                <a href="{{ route('shop.index', ['category_id' => $category->id]) }}">{{ $category->name }}</a>
            @empty
                <a href="{{ route('shop.index') }}">All Products</a>
            @endforelse
        </aside>

        <section class="card hero">
            <h1>Welcome to your new marketplace</h1>
            <p>Shop thousands of products, discover daily deals, and enjoy fast delivery.</p>
            <p style="margin-top: 16px;">
                <a class="btn btn-primary" href="{{ route('shop.index') }}">Start Shopping</a>
            </p>
        </section>
    </div>

    <section class="section">
        <h2>Flash Sale</h2>
        <div class="deal-grid">
            @forelse($deals as $deal)
                <article class="card deal">
                    <div class="title">{{ $deal->name }}</div>
                    <div class="price">KES {{ number_format((float) $deal->sale_price, 2) }}</div>
                    <div class="muted">Was KES {{ number_format((float) $deal->price, 2) }}</div>
                </article>
            @empty
                <article class="card deal">No deals configured yet.</article>
            @endforelse
        </div>
    </section>

    <section class="section">
        <h2>Featured Products</h2>
        <div class="product-grid">
            @forelse($featuredProducts as $product)
                <article class="card product">
                    <div style="height:140px;border-radius:8px;background:#f3f4f6 url('{{ $product->image_url ?: 'https://via.placeholder.com/300x220?text=Product' }}') center/cover no-repeat;"></div>
                    <div class="muted">{{ $product->category?->name ?: 'General' }}</div>
                    <div class="title">{{ $product->name }}</div>
                    <div class="price">KES {{ number_format((float) ($product->sale_price ?: $product->price), 2) }}</div>
                    <div style="margin-top: 10px; display:flex; gap:8px; flex-wrap:wrap;">
                        <a href="{{ route('shop.show', $product) }}" class="btn btn-primary" style="display: inline-block; padding: 8px 10px; font-size: 13px;">View</a>
                        <form method="POST" action="{{ route('cart.add', $product) }}">
                            @csrf
                            <button type="submit" class="btn" style="padding: 8px 10px; font-size: 13px;">Add</button>
                        </form>
                    </div>
                </article>
            @empty
                <article class="card product">No products available. Add services in admin to populate this page.</article>
            @endforelse
        </div>
    </section>
</div>
@endsection
