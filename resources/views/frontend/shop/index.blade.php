@extends('layouts.storefront')

@section('title', 'Shop')

@push('styles')
<style>
    .shop-wrap { display: grid; grid-template-columns: 260px 1fr; gap: 16px; margin-top: 16px; }
    .sidebar, .listing { padding: 14px; }
    .filters h3 { margin: 0 0 10px; font-size: 16px; }
    .filters label { display: block; font-size: 13px; color: #6b7280; margin-bottom: 6px; }
    .filters input, .filters select { width: 100%; padding: 10px; border: 1px solid #d1d5db; border-radius: 8px; margin-bottom: 10px; }
    .grid { display: grid; gap: 12px; grid-template-columns: repeat(4, minmax(0, 1fr)); }
    .item { padding: 12px; }
    .title { font-weight: 600; margin: 6px 0; min-height: 40px; font-size: 14px; }
    .meta { color: #6b7280; font-size: 12px; }
    .price { font-weight: 800; margin-top: 8px; }
    @media (max-width: 1000px) { .grid { grid-template-columns: repeat(3, minmax(0, 1fr)); } }
    @media (max-width: 850px) {
        .shop-wrap { grid-template-columns: 1fr; }
        .grid { grid-template-columns: repeat(2, minmax(0, 1fr)); }
    }
</style>
@endpush

@section('content')
<div class="container">
    <div class="shop-wrap">
        <aside class="card sidebar filters">
            <h3>Filter Products</h3>
            <form method="GET" action="{{ route('shop.index') }}">
                <label for="q">Search</label>
                <input id="q" type="text" name="q" value="{{ request('q') }}" placeholder="Search...">

                <label for="category">Category</label>
                <select id="category" name="category_id">
                    <option value="">All categories</option>
                    @foreach($categories as $category)
                        <option value="{{ $category->id }}" @selected((string) request('category_id') === (string) $category->id)>{{ $category->name }}</option>
                    @endforeach
                </select>

                <label for="sort">Sort by</label>
                <select id="sort" name="sort">
                    <option value="latest" @selected(request('sort') === 'latest')>Newest</option>
                    <option value="name_asc" @selected(request('sort') === 'name_asc')>Name A-Z</option>
                    <option value="name_desc" @selected(request('sort') === 'name_desc')>Name Z-A</option>
                </select>

                <button class="btn btn-primary" type="submit">Apply</button>
            </form>
        </aside>

        <section class="card listing">
            <h2 style="margin: 0 0 12px;">All Products</h2>
            <div class="grid">
                @forelse($products as $product)
                    @include('frontend.partials.product-card', ['product' => $product, 'showRating' => true, 'showActions' => true])
                @empty
                    <p>No products match your filters.</p>
                @endforelse
            </div>

            <div style="margin-top: 16px;">
                {{ $products->links() }}
            </div>
        </section>
    </div>
</div>
@endsection
