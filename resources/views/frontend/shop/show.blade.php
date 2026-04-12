@extends('layouts.storefront')

@section('title', $product->name)

@push('styles')
<style>
    .product-layout { display: grid; grid-template-columns: 1fr 360px; gap: 16px; margin-top: 16px; }
    .main, .side { padding: 16px; }
    .badge { display: inline-block; background: #fff7ed; color: #c2410c; font-size: 12px; border-radius: 999px; padding: 4px 10px; font-weight: 700; }
    .title { margin: 10px 0; font-size: 28px; }
    .price { font-size: 26px; font-weight: 800; margin: 8px 0; }
    .desc { color: #4b5563; line-height: 1.7; }
    .related { margin-top: 20px; }
    .related-grid { display: grid; gap: 12px; grid-template-columns: repeat(4, minmax(0, 1fr)); }
    .item { padding: 12px; }
    .mini { font-size: 12px; color: #6b7280; }
    @media (max-width: 1000px) { .related-grid { grid-template-columns: repeat(2, minmax(0, 1fr)); } }
    @media (max-width: 900px) { .product-layout { grid-template-columns: 1fr; } }
</style>
@endpush

@section('content')
<div class="container">
    <div class="product-layout">
        <article class="card main">
            <div style="height:320px;border-radius:10px;background:#f3f4f6 url('{{ $product->image_url ?: 'https://via.placeholder.com/900x500?text=Product' }}') center/cover no-repeat;"></div>
            <span class="badge">{{ $product->category?->name ?: 'General' }}</span>
            <h1 class="title">{{ $product->name }}</h1>
            <div class="mini">Sold by: <strong>{{ $product->vendor?->name ?: 'In-house' }}</strong></div>
            <div class="mini" style="margin-bottom:8px;">
                Rating: {{ $averageRating > 0 ? $averageRating . '/5' : 'No ratings yet' }} ({{ $product->reviews->count() }} reviews)
            </div>
            <div class="price">KES {{ number_format((float) ($product->sale_price ?: $product->price), 2) }}</div>
            @if($product->sale_price)
                <div style="color:#6b7280;">Was KES {{ number_format((float) $product->price, 2) }}</div>
            @endif
            <p class="desc">{{ $product->description }}</p>
        </article>

        <aside class="card side">
            <h3 style="margin-top: 0;">Delivery & Returns</h3>
            <p class="mini">Eligible for next-day delivery in selected areas.</p>
            <p class="mini">7-day return policy for unopened items.</p>
            <form method="POST" action="{{ route('cart.add', $product) }}">
                @csrf
                <button class="btn btn-primary" style="width: 100%;" type="submit">Add to Cart</button>
            </form>
            @auth
            <form method="POST" action="{{ route('wishlist.store', $product) }}" style="margin-top:8px;">
                @csrf
                <button class="btn" style="width: 100%;" type="submit">Add to Wishlist</button>
            </form>
            @endauth
            <a href="{{ route('shop.index') }}" style="display:block;margin-top:10px;text-align:center;">Back to shop</a>
        </aside>
    </div>

    <section class="related">
        <h2 style="margin-top: 18px;">Customer Reviews</h2>
        <div class="card" style="padding: 14px; margin-bottom: 16px;">
            @auth
                <form method="POST" action="{{ route('shop.reviews.store', $product) }}">
                    @csrf
                    <label>Rating</label>
                    <select name="rating" required style="width:100%;padding:10px;border:1px solid #d1d5db;border-radius:8px;margin-bottom:8px;">
                        <option value="">Select rating</option>
                        @for($i=5;$i>=1;$i--)
                            <option value="{{ $i }}">{{ $i }} / 5</option>
                        @endfor
                    </select>
                    <label>Comment</label>
                    <textarea name="comment" rows="3" required style="width:100%;padding:10px;border:1px solid #d1d5db;border-radius:8px;margin-bottom:8px;"></textarea>
                    <button type="submit" class="btn btn-primary">Submit Review</button>
                </form>
            @else
                <p>Please <a href="{{ route('login') }}" style="color:var(--brand);font-weight:700;">login</a> to review this product.</p>
            @endauth
        </div>
        <div class="card" style="padding:14px; margin-bottom: 16px;">
            @forelse($product->reviews as $review)
                <div style="border-bottom:1px solid #e5e7eb;padding:8px 0;">
                    <div style="font-weight:600;">{{ $review->name }} - {{ $review->rating }}/5</div>
                    <div class="mini">{{ $review->created_at->format('M d, Y') }}</div>
                    <div>{{ $review->comment }}</div>
                </div>
            @empty
                <p>No approved reviews yet.</p>
            @endforelse
        </div>

        <h2 style="margin-top: 18px;">Questions & Answers</h2>
        <div class="card" style="padding:14px; margin-bottom: 16px;">
            @auth
                <form method="POST" action="{{ route('shop.questions.store', $product) }}">
                    @csrf
                    <label>Ask a question</label>
                    <textarea name="question" rows="3" required style="width:100%;padding:10px;border:1px solid #d1d5db;border-radius:8px;margin-bottom:8px;"></textarea>
                    <button type="submit" class="btn btn-primary">Submit Question</button>
                </form>
            @else
                <p>Please <a href="{{ route('login') }}" style="color:var(--brand);font-weight:700;">login</a> to ask a question.</p>
            @endauth
        </div>
        <div class="card" style="padding:14px; margin-bottom: 16px;">
            @forelse($product->questions as $q)
                <div style="border-bottom:1px solid #e5e7eb;padding:8px 0;">
                    <div style="font-weight:600;">Q: {{ $q->question }}</div>
                    <div class="mini">By {{ $q->name }} on {{ $q->created_at->format('M d, Y') }}</div>
                    @if($q->answer)
                        <div style="margin-top:6px;"><strong>A:</strong> {{ $q->answer }}</div>
                    @else
                        <div class="mini" style="margin-top:6px;">Awaiting answer.</div>
                    @endif
                </div>
            @empty
                <p>No approved questions yet.</p>
            @endforelse
        </div>

        <h2>Related Products</h2>
        <div class="related-grid">
            @forelse($relatedProducts as $related)
                <article class="card item">
                    <div class="mini">{{ $related->category?->name ?: 'General' }}</div>
                    <div class="mini">Seller: {{ $related->vendor?->name ?: 'In-house' }}</div>
                    <div style="font-weight: 600; margin: 6px 0;">{{ $related->name }}</div>
                    <div class="mini">KES {{ number_format((float) ($related->sale_price ?: $related->price), 2) }}</div>
                    <a href="{{ route('shop.show', $related) }}" style="display:inline-block;margin-top:8px;color:var(--brand);font-weight:700;">View</a>
                </article>
            @empty
                <p>No related products.</p>
            @endforelse
        </div>
    </section>
</div>
@endsection
