@extends('layouts.storefront')

@section('title', 'Payment Status')

@section('content')
<div class="container" style="margin-top:16px;">
    <div class="card" style="padding:16px;">
        <h2 style="margin-top:0;">Payment Status</h2>
        @if($order)
            <p><strong>Order:</strong> {{ $order->order_number }}</p>
            <p><strong>Payment Status:</strong> {{ ucfirst($order->payment_status ?? 'pending') }}</p>
            @if($order->payment_reference)
                <p><strong>Reference:</strong> {{ $order->payment_reference }}</p>
            @endif
            <a href="{{ route('orders.track') }}" class="btn btn-primary">Track Order</a>
        @else
            <p>Order not found for the provided payment reference.</p>
            <a href="{{ route('home') }}" class="btn btn-primary">Back Home</a>
        @endif
    </div>
</div>
@endsection
