@php
    $inWishlist = in_array((int) $product->id, array_map('intval', $wishlistIds ?? []), true);
    $rating = round((float) ($product->reviews_avg_rating ?? 0), 1);
    $ratingCount = (int) ($product->reviews_count ?? 0);
    $showRating = $showRating ?? false;
    $showActions = $showActions ?? true;
@endphp
<article class="p-card">
    <div class="p-card-media">
        <a href="{{ route('shop.show', $product) }}" class="p-card-img" style="background-image:url('{{ $product->displayImageUrl() }}');"></a>
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
    <div class="p-card-body">
        <div class="p-cat">{{ $product->category?->name ?: 'General' }}</div>
        <a class="p-name" href="{{ route('shop.show', $product) }}">{{ $product->name }}</a>
        @if($showRating)
            <div class="p-rating">
                @for($i = 1; $i <= 5; $i++)
                    <span class="{{ $rating >= $i ? 'is-on' : '' }}">★</span>
                @endfor
                <small>({{ $ratingCount }})</small>
            </div>
        @endif
        <div class="p-price">
            KES {{ number_format($product->currentPrice(), 2) }}
            @if($product->hasSale())
                <span class="p-was">KES {{ number_format((float) $product->price, 2) }}</span>
            @endif
        </div>
        @if($showActions)
            <div class="p-actions">
                <form method="POST" action="{{ route('cart.add', $product) }}">
                    @csrf
                    <button type="submit" class="btn btn-cart">Add to Cart</button>
                </form>
                <a href="{{ route('shop.show', $product) }}" class="btn btn-view">View</a>
            </div>
        @endif
    </div>
</article>
