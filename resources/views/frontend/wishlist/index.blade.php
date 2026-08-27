@extends('layouts.storefront')

@section('title', 'My Wishlist')

@section('content')
<div class="container" style="margin-top:16px;">
    <div class="card" style="padding:16px;">
        <h2 style="margin-top:0;">My Wishlist</h2>
        <p style="color:#6b7280;">Saved products for later.</p>

        <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(220px,1fr));gap:12px;">
            @forelse($items as $item)
                @if($item->product)
                    @include('frontend.partials.product-card', ['product' => $item->product, 'showRating' => false, 'showActions' => true])
                @endif
            @empty
                <p>No items in wishlist yet.</p>
            @endforelse
        </div>
        <div style="margin-top:10px;">{{ $items->links() }}</div>
    </div>
</div>
@endsection
