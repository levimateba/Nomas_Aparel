@extends('layouts.storefront')

@section('title', 'Order Confirmed')

@section('content')
<div class="container" style="margin-top:16px;">
    <div class="card" style="padding:18px;">
        <h2 style="margin-top:0;">Order placed successfully</h2>
        <p>Your order number is <strong>{{ $orderNumber }}</strong>.</p>
        <p>We have received your order and will contact you shortly.</p>
        <a href="{{ route('shop.index') }}" class="btn btn-primary">Continue Shopping</a>
    </div>
</div>
@endsection
