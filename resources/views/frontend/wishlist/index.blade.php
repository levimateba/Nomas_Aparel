@extends('layouts.storefront')

@section('title', 'My Wishlist')

@section('content')
<div class="container" style="margin-top:16px;">
    <div class="card" style="padding:16px;">
        <h2 style="margin-top:0;">My Wishlist</h2>
        <p style="color:#6b7280;">Saved products for later.</p>

        <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(220px,1fr));gap:12px;">
            @forelse($items as $item)
                @php $product = $item->product; @endphp
                @if($product)
                    <article class="card" style="padding:12px;">
                        <div style="height:140px;border-radius:8px;background:#f3f4f6 url('{{ $product->image_url ?: 'https://via.placeholder.com/300x220?text=Product' }}') center/cover no-repeat;"></div>
                        <div style="margin-top:8px;font-weight:600;">{{ $product->name }}</div>
                        <div style="color:#6b7280;font-size:13px;">KES {{ number_format((float) ($product->sale_price ?: $product->price), 2) }}</div>
                        <div style="margin-top:8px;display:flex;gap:6px;flex-wrap:wrap;">
                            <a href="{{ route('shop.show', $product) }}" class="btn btn-primary" style="padding:7px 10px;">View</a>
                            <form method="POST" action="{{ route('cart.add', $product) }}">
                                @csrf
                                <button type="submit" class="btn" style="padding:7px 10px;">Add to Cart</button>
                            </form>
                            <form method="POST" action="{{ route('wishlist.destroy', $product) }}">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn" style="padding:7px 10px;">Remove</button>
                            </form>
                        </div>
                    </article>
                @endif
            @empty
                <p>No items in wishlist yet.</p>
            @endforelse
        </div>
        <div style="margin-top:10px;">{{ $items->links() }}</div>
    </div>
</div>
@endsection
