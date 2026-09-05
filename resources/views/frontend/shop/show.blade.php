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
    .share { margin-top: 16px; padding-top: 14px; border-top: 1px solid var(--line); }
    .share h3 { margin: 0 0 10px; font-size: 15px; }
    .share-row { display: flex; flex-wrap: wrap; gap: 8px; }
    .share-btn {
        display: inline-flex; align-items: center; justify-content: center;
        min-width: 42px; height: 38px; padding: 0 12px; border-radius: 8px;
        font-size: 13px; font-weight: 700; border: 1px solid var(--line); background: #fff;
    }
    .share-btn.whatsapp { background: #25d366; color: #fff; border-color: #25d366; }
    .share-btn.facebook { background: #1877f2; color: #fff; border-color: #1877f2; }
    .share-btn.x { background: #111; color: #fff; border-color: #111; }
    .share-btn.copy { cursor: pointer; }
    .star-rating {
        display: flex;
        align-items: center;
        gap: 6px;
        margin: 8px 0 14px;
        flex-wrap: wrap;
    }
    .star-rating-stars { display: inline-flex; gap: 4px; }
    .star-rating .star-btn {
        width: 40px;
        height: 40px;
        border: 0;
        border-radius: 10px;
        background: #f3f4f6;
        color: #d1d5db;
        font-size: 22px;
        line-height: 1;
        cursor: pointer;
        padding: 0;
        display: inline-flex;
        align-items: center;
        justify-content: center;
    }
    .star-rating .star-btn.is-on,
    .star-rating .star-btn.is-hover { color: #d4af37; background: #fffbeb; }
    .star-rating-label { font-size: 13px; font-weight: 700; color: #6b7280; min-width: 48px; }
    @media (max-width: 1000px) { .related-grid { grid-template-columns: repeat(2, minmax(0, 1fr)); } }
    @media (max-width: 900px) { .product-layout { grid-template-columns: 1fr; } }
</style>
@endpush

@section('content')
<div class="container">
    <div class="product-layout">
        <article class="card main">
            @php $inWishlist = in_array((int) $product->id, array_map('intval', $wishlistIds ?? []), true); @endphp
            <div class="p-card-media" style="margin-bottom:12px;">
                <div class="p-card-img" style="height:320px;border-radius:10px;background-image:url('{{ $product->displayImageUrl() }}');"></div>
                @if($product->hasSale())
                    <span class="p-disc">-{{ $product->discountPercent() }}%</span>
                @endif
                @auth
                    <form class="p-heart {{ $inWishlist ? 'is-on' : '' }}" method="POST" action="{{ $inWishlist ? route('wishlist.destroy', $product) : route('wishlist.store', $product) }}">
                        @csrf
                        @if($inWishlist)
                            @method('DELETE')
                        @endif
                        <button type="submit" aria-label="Wishlist">{{ $inWishlist ? '♥' : '♡' }}</button>
                    </form>
                @else
                    <a class="p-heart" href="{{ route('login') }}" aria-label="Login to add wishlist">♡</a>
                @endauth
            </div>
            <span class="badge">{{ $product->category?->name ?: 'General' }}</span>
            <h1 class="title">{{ $product->name }}</h1>
            <div class="mini">Sold by: <strong>{{ $product->vendor?->name ?: 'In-house' }}</strong></div>
            <div class="mini" style="margin-bottom:8px;">
                Rating: {{ $averageRating > 0 ? $averageRating . '/5' : 'No ratings yet' }} ({{ $product->reviews->count() }} reviews)
            </div>
            <div class="price" id="product-display-price">KES {{ number_format($product->currentPrice(), 2) }}</div>
            @if($product->hasSale())
                <div style="color:#6b7280;">Was KES {{ number_format((float) $product->price, 2) }}</div>
            @endif
            @if($product->style)<div class="mini">Style: <strong>{{ $product->style->name }}</strong></div>@endif
            @if($product->target_audience)<div class="mini">For: <strong>{{ \App\Models\Product::AUDIENCES[$product->target_audience] ?? $product->target_audience }}</strong></div>@endif
            <p class="desc">{{ $product->description }}</p>
            @if($product->care_instructions)
                <p class="mini" style="white-space:pre-line;"><strong>Care:</strong> {{ $product->care_instructions }}</p>
            @endif
            @if($product->specifications->count())
                <div style="margin-top:10px;">
                    @foreach($product->specifications as $spec)
                        <div class="mini"><strong>{{ $spec->name }}:</strong> {{ $spec->value }}</div>
                    @endforeach
                </div>
            @endif
        </article>

        <aside class="card side">
            <h3 style="margin-top: 0;">Delivery & Returns</h3>
            <p class="mini">Eligible for next-day delivery in selected areas.</p>
            <p class="mini">7-day return policy for unopened items.</p>
            <form method="POST" action="{{ route('cart.add', $product) }}">
                @csrf
                @if($product->usesVariants())
                    <label class="mini" style="display:block;margin-bottom:6px;">Select variant</label>
                    <select name="variant_id" id="variant-select" required style="width:100%;margin-bottom:10px;padding:10px;border-radius:8px;border:1px solid #d1d5db;">
                        <option value="">Choose size / colour…</option>
                        @foreach($product->activeVariants as $variant)
                            @php $onlineQty = $onlineStockByVariant[$variant->id] ?? (int) $variant->stock; @endphp
                            <option
                                value="{{ $variant->id }}"
                                data-price="{{ $variant->currentPrice() }}"
                                data-stock="{{ $onlineQty }}"
                                @disabled($onlineQty < 1 && !($product->allow_backorders ?? false))
                            >
                                {{ $variant->name }} — KES {{ number_format($variant->currentPrice(), 2) }}
                                @if($product->display_stock) ({{ $onlineQty }} left) @endif
                                @if($onlineQty < 1) — Out of stock @endif
                            </option>
                        @endforeach
                    </select>
                @elseif($product->display_stock)
                    <p class="mini">Stock: {{ $onlineStockSimple ?? $product->stock }}</p>
                @endif
                <input type="number" name="qty" min="1" value="1" style="width:100%;margin-bottom:10px;padding:10px;border-radius:8px;border:1px solid #d1d5db;">
                <button class="btn btn-primary" style="width: 100%;" type="submit" @disabled(!($product->allow_online_purchase ?? true))>Add to Cart</button>
            </form>
            <script>
            document.getElementById('variant-select')?.addEventListener('change', function () {
                const opt = this.options[this.selectedIndex];
                const price = opt?.dataset?.price;
                const el = document.getElementById('product-display-price');
                if (el && price) el.textContent = 'KES ' + Number(price).toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2});
            });
            </script>
            @auth
                <form method="POST" action="{{ $inWishlist ? route('wishlist.destroy', $product) : route('wishlist.store', $product) }}" style="margin-top:8px;">
                    @csrf
                    @if($inWishlist)
                        @method('DELETE')
                    @endif
                    <button class="btn btn-cart" style="width: 100%;" type="submit">{{ $inWishlist ? 'Remove from Wishlist' : 'Add to Wishlist' }}</button>
                </form>
            @else
                <a class="btn btn-cart" href="{{ route('login') }}" style="display:block;margin-top:8px;text-align:center;">Login to Wishlist</a>
            @endauth

            @php
                $shareUrl = url()->current();
                $shareText = $product->name . ' — KES ' . number_format($product->currentPrice(), 2);
                $shareMessage = $shareText . ' ' . $shareUrl;
            @endphp
            <div class="share">
                <h3>Share this product</h3>
                <div class="share-row">
                    <a class="share-btn whatsapp" target="_blank" rel="noopener" href="https://api.whatsapp.com/send?text={{ urlencode($shareMessage) }}">WhatsApp</a>
                    <a class="share-btn facebook" target="_blank" rel="noopener" href="https://www.facebook.com/sharer/sharer.php?u={{ urlencode($shareUrl) }}">Facebook</a>
                    <a class="share-btn x" target="_blank" rel="noopener" href="https://twitter.com/intent/tweet?url={{ urlencode($shareUrl) }}&text={{ urlencode($shareText) }}">X</a>
                    <button type="button" class="share-btn copy" data-share-url="{{ $shareUrl }}">Copy link</button>
                </div>
            </div>
            <a href="{{ route('shop.index') }}" style="display:block;margin-top:10px;text-align:center;">Back to shop</a>
        </aside>
    </div>

    <section class="related">
        <h2 style="margin-top: 18px;">Customer Reviews</h2>
        <div class="card" style="padding: 14px; margin-bottom: 16px;">
            @auth
                <form method="POST" action="{{ route('shop.reviews.store', $product) }}" id="review-form">
                    @csrf
                    <label>Rating</label>
                    <input type="hidden" name="rating" id="review-rating" value="{{ old('rating') }}" required>
                    <div class="star-rating" id="star-rating" role="radiogroup" aria-label="Select rating">
                        <div class="star-rating-stars">
                            @for($i = 1; $i <= 5; $i++)
                                <button type="button" class="star-btn" data-value="{{ $i }}" aria-label="{{ $i }} star{{ $i > 1 ? 's' : '' }}">★</button>
                            @endfor
                        </div>
                        <span class="star-rating-label" id="star-rating-label">Tap a star</span>
                    </div>
                    <label>Comment</label>
                    <textarea name="comment" rows="3" required style="width:100%;padding:10px;border:1px solid #d1d5db;border-radius:8px;margin-bottom:8px;">{{ old('comment') }}</textarea>
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
                @include('frontend.partials.product-card', ['product' => $related, 'showRating' => true, 'showActions' => true])
            @empty
                <p>No related products.</p>
            @endforelse
        </div>
    </section>
</div>
<script>
    document.querySelectorAll('.share-btn.copy').forEach(function (button) {
        button.addEventListener('click', function () {
            const url = button.getAttribute('data-share-url') || window.location.href;
            const done = function () {
                const original = button.textContent;
                button.textContent = 'Copied';
                setTimeout(function () { button.textContent = original; }, 1600);
            };
            if (navigator.clipboard && navigator.clipboard.writeText) {
                navigator.clipboard.writeText(url).then(done).catch(function () {
                    window.prompt('Copy this link', url);
                });
            } else {
                window.prompt('Copy this link', url);
            }
        });
    });

    (function () {
        var wrap = document.getElementById('star-rating');
        var input = document.getElementById('review-rating');
        var label = document.getElementById('star-rating-label');
        var form = document.getElementById('review-form');
        if (!wrap || !input) return;

        var buttons = Array.prototype.slice.call(wrap.querySelectorAll('.star-btn'));
        var selected = parseInt(input.value, 10) || 0;

        function paint(value, hover) {
            buttons.forEach(function (btn) {
                var n = parseInt(btn.getAttribute('data-value'), 10);
                btn.classList.toggle('is-on', n <= value);
                btn.classList.toggle('is-hover', hover ? n <= hover : false);
            });
            if (label) {
                label.textContent = value > 0 ? (value + ' / 5') : 'Tap a star';
            }
        }

        buttons.forEach(function (btn) {
            btn.addEventListener('mouseenter', function () {
                paint(selected, parseInt(btn.getAttribute('data-value'), 10));
            });
            btn.addEventListener('mouseleave', function () {
                paint(selected, 0);
            });
            btn.addEventListener('click', function () {
                selected = parseInt(btn.getAttribute('data-value'), 10);
                input.value = String(selected);
                paint(selected, 0);
            });
        });

        if (form) {
            form.addEventListener('submit', function (e) {
                if (!input.value) {
                    e.preventDefault();
                    if (label) label.textContent = 'Please select a rating';
                    label && (label.style.color = '#dc2626');
                }
            });
        }

        paint(selected, 0);
    })();
</script>
@endsection
