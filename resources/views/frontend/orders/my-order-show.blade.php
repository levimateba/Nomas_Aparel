@extends('layouts.storefront')

@section('title', 'Order ' . $order->order_number)

@section('content')
<div class="container" style="margin-top:16px;">
    <div class="card" style="padding:16px;">
        <h2 style="margin-top:0;">Order {{ $order->order_number }}</h2>
        <p><strong>Status:</strong> {{ ucfirst(str_replace('_', ' ', $order->status)) }}</p>
        <p><strong>Payment:</strong> {{ ucwords(str_replace('_', ' ', $order->payment_method)) }}</p>
        <p><strong>Total:</strong> KES {{ number_format((float) $order->total_amount, 2) }}</p>
        <p><strong>Placed:</strong> {{ $order->created_at->format('M d, Y H:i') }}</p>

        @if($order->cancellation_requested_at)
            <div style="margin:10px 0;padding:10px;border-radius:8px;background:#fef2f2;color:#991b1b;">
                Cancellation requested on {{ $order->cancellation_requested_at->format('M d, Y H:i') }}.
                <br>Reason: {{ $order->cancellation_reason }}
            </div>
        @endif

        @if(in_array($order->status, ['pending','processing','paid'], true) && !$order->cancellation_requested_at)
            <div class="card" style="padding:12px;margin:12px 0;border:1px solid #fecaca;background:#fff7f7;">
                <h4 style="margin-top:0;">Request cancellation</h4>
                <form method="POST" action="{{ route('orders.my.cancel-request', $order) }}">
                    @csrf
                    <textarea name="cancellation_reason" rows="3" required placeholder="Tell us why you want to cancel..." style="width:100%;padding:10px;border:1px solid #d1d5db;border-radius:8px;"></textarea>
                    <button type="submit" class="btn" style="margin-top:8px;">Submit cancellation request</button>
                </form>
            </div>
        @endif

        <h3>Items</h3>
        <table style="width:100%;border-collapse:collapse;">
            <thead>
                <tr style="background:#f9fafb;">
                    <th style="padding:8px;border:1px solid #e5e7eb;text-align:left;">Product</th>
                    <th style="padding:8px;border:1px solid #e5e7eb;text-align:left;">Unit Price</th>
                    <th style="padding:8px;border:1px solid #e5e7eb;text-align:left;">Qty</th>
                    <th style="padding:8px;border:1px solid #e5e7eb;text-align:left;">Line Total</th>
                </tr>
            </thead>
            <tbody>
                @foreach($order->items as $item)
                    <tr>
                        <td style="padding:8px;border:1px solid #e5e7eb;">{{ $item->product_name }}</td>
                        <td style="padding:8px;border:1px solid #e5e7eb;">KES {{ number_format((float) $item->unit_price, 2) }}</td>
                        <td style="padding:8px;border:1px solid #e5e7eb;">{{ $item->quantity }}</td>
                        <td style="padding:8px;border:1px solid #e5e7eb;">KES {{ number_format((float) $item->line_total, 2) }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <div style="margin-top:12px;">
            <a href="{{ route('orders.my') }}" class="btn btn-primary">Back to My Orders</a>
        </div>
    </div>
</div>
@endsection
