@extends('layouts.storefront')

@section('title', 'Cart')

@section('content')
<div class="container" style="margin-top:16px;">
    <div class="card" style="padding:16px;">
        <h2 style="margin-top:0;">Your Cart</h2>

        @if(session('success'))
            <div style="margin:10px 0;padding:10px;border-radius:8px;background:#ecfdf5;color:#065f46;">{{ session('success') }}</div>
        @endif

        @forelse($cart as $item)
            <div style="display:grid;grid-template-columns:80px 1fr auto;gap:12px;align-items:center;padding:10px 0;border-bottom:1px solid #e5e7eb;">
                <div style="width:80px;height:80px;border-radius:8px;background:#f3f4f6 url('{{ $item['image_url'] ?: 'https://via.placeholder.com/160x160?text=Item' }}') center/cover no-repeat;"></div>
                <div>
                    <div style="font-weight:600;">{{ $item['name'] }}</div>
                    <div style="color:#6b7280;font-size:13px;">KES {{ number_format((float) $item['price'], 2) }}</div>
                </div>
                <div style="display:flex;align-items:center;gap:8px;">
                    <form method="POST" action="{{ route('cart.update', $item['product_id']) }}">
                        @csrf
                        <input type="number" min="1" name="qty" value="{{ $item['qty'] }}" style="width:68px;padding:8px;border:1px solid #d1d5db;border-radius:8px;">
                        <button class="btn btn-primary" type="submit" style="padding:8px 10px;">Update</button>
                    </form>
                    <form method="POST" action="{{ route('cart.remove', $item['product_id']) }}">
                        @csrf
                        <button class="btn" type="submit" style="padding:8px 10px;">Remove</button>
                    </form>
                </div>
            </div>
        @empty
            <p>Your cart is empty.</p>
        @endforelse

        <div class="card" style="padding:12px;margin-top:12px;">
            <form method="POST" action="{{ route('cart.coupon.apply') }}" style="display:flex;gap:8px;align-items:center;flex-wrap:wrap;">
                @csrf
                <input type="text" name="coupon_code" placeholder="Coupon code" style="padding:8px;border:1px solid #d1d5db;border-radius:8px;">
                <button class="btn btn-primary" type="submit">Apply Coupon</button>
                @if(!empty($totals['coupon_code']))
                    <span style="color:#6b7280;">Applied: <strong>{{ $totals['coupon_code'] }}</strong></span>
                @endif
            </form>
            @if(!empty($totals['coupon_code']))
                <form method="POST" action="{{ route('cart.coupon.remove') }}" style="margin-top:8px;">
                    @csrf
                    <button class="btn" type="submit">Remove Coupon</button>
                </form>
            @endif
        </div>

        <div style="margin-top:14px;display:flex;justify-content:space-between;align-items:center;gap:12px;flex-wrap:wrap;">
            <strong>
                Subtotal: KES {{ number_format((float) $totals['subtotal'], 2) }}<br>
                Discount: KES {{ number_format((float) $totals['discount'], 2) }}<br>
                Total: KES {{ number_format((float) $totals['total'], 2) }}
            </strong>
            <div style="display:flex;gap:8px;flex-wrap:wrap;justify-content:flex-end;">
                @guest
                    <a href="{{ route('checkout.index') }}" class="btn btn-primary">Login to checkout</a>
                    <a href="{{ route('register') }}?redirect=checkout" class="btn">Register</a>
                @else
                    <a href="{{ route('checkout.index') }}" class="btn btn-primary">Checkout</a>
                @endguest
                <form method="POST" action="{{ route('cart.clear') }}">
                    @csrf
                    <button class="btn" type="submit">Clear Cart</button>
                </form>
                <a href="{{ route('shop.index') }}" class="btn">Continue Shopping</a>
            </div>
        </div>
        @guest
            <p style="margin:12px 0 0;color:#6b7280;">You need to login or register before completing checkout.</p>
        @endguest
    </div>
</div>
@endsection
