@extends('layouts.storefront')

@section('title', 'My Orders')

@section('content')
<div class="container" style="margin-top:16px;">
    <div class="card" style="padding:16px;">
        <h2 style="margin-top:0;">My Orders</h2>
        <p style="color:#6b7280;">Orders placed while logged in to your account.</p>

        <table style="width:100%;border-collapse:collapse;">
            <thead>
                <tr style="background:#f9fafb;">
                    <th style="padding:8px;border:1px solid #e5e7eb;text-align:left;">Order #</th>
                    <th style="padding:8px;border:1px solid #e5e7eb;text-align:left;">Items</th>
                    <th style="padding:8px;border:1px solid #e5e7eb;text-align:left;">Status</th>
                    <th style="padding:8px;border:1px solid #e5e7eb;text-align:left;">Total</th>
                    <th style="padding:8px;border:1px solid #e5e7eb;text-align:left;">Date</th>
                    <th style="padding:8px;border:1px solid #e5e7eb;text-align:left;">Action</th>
                </tr>
            </thead>
            <tbody>
                @forelse($orders as $order)
                    <tr>
                        <td style="padding:8px;border:1px solid #e5e7eb;">{{ $order->order_number }}</td>
                        <td style="padding:8px;border:1px solid #e5e7eb;">{{ $order->items_count }}</td>
                        <td style="padding:8px;border:1px solid #e5e7eb;">{{ ucfirst($order->status) }}</td>
                        <td style="padding:8px;border:1px solid #e5e7eb;">KES {{ number_format((float) $order->total_amount, 2) }}</td>
                        <td style="padding:8px;border:1px solid #e5e7eb;">{{ $order->created_at->format('M d, Y H:i') }}</td>
                        <td style="padding:8px;border:1px solid #e5e7eb;">
                            <a href="{{ route('orders.my.show', $order) }}" class="btn btn-primary" style="padding:6px 10px;display:inline-block;">View</a>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6" style="padding:10px;border:1px solid #e5e7eb;">No orders yet.</td></tr>
                @endforelse
            </tbody>
        </table>
        <div style="margin-top:10px;">{{ $orders->links() }}</div>
    </div>
</div>
@endsection
