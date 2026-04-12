@extends('layouts.admin')
@section('title', 'Order Details')
@section('content')
    <h2>Order {{ $order->order_number }}</h2>
    <div style="margin-bottom:10px; display:flex; gap:8px; flex-wrap:wrap;">
        <a class="btn btn-secondary" href="{{ route('admin.orders.print', $order) }}" target="_blank" rel="noopener">Printable Invoice</a>
        <a class="btn btn-secondary" href="{{ route('admin.orders.pdf', $order) }}">Download PDF</a>
        <form method="POST" action="{{ route('admin.orders.send-confirmation', $order) }}" style="display:inline;">
            @csrf
            <button type="submit" class="btn btn-secondary">Resend Confirmation Email</button>
        </form>
    </div>

    <div class="card">
        <p><strong>Customer:</strong> {{ $order->customer_name }} ({{ $order->customer_email }})</p>
        <p><strong>Phone:</strong> {{ $order->customer_phone ?: 'N/A' }}</p>
        <p><strong>Shipping Address:</strong><br>{{ $order->shipping_address }}</p>
        <p><strong>Payment Method:</strong> {{ ucwords(str_replace('_', ' ', $order->payment_method)) }}</p>
        <p><strong>Payment Status:</strong> {{ ucfirst($order->payment_status ?? 'pending') }}</p>
        @if($order->payment_reference)
            <p><strong>Payment Reference:</strong> {{ $order->payment_reference }}</p>
        @endif
        @if($order->notes)
            <p><strong>Notes:</strong> {{ $order->notes }}</p>
        @endif
        @if($order->cancellation_requested_at)
            <p><strong>Cancellation Requested At:</strong> {{ $order->cancellation_requested_at->format('M d, Y H:i') }}</p>
            <p><strong>Cancellation Reason:</strong> {{ $order->cancellation_reason }}</p>
        @endif
        <p><strong>Total:</strong> KES {{ number_format((float) $order->total_amount, 2) }}</p>
    </div>

    <div class="card">
        <form method="POST" action="{{ route('admin.orders.update', $order) }}" style="display:flex;gap:8px;align-items:center;flex-wrap:wrap;">
            @csrf
            @method('PUT')
            <label style="margin:0;">Status</label>
            <select name="status" style="max-width:260px;">
            @foreach(['pending','processing','paid','shipped','delivered','cancellation_requested','cancelled'] as $status)
                    <option value="{{ $status }}" @selected($order->status === $status)>{{ ucfirst($status) }}</option>
                @endforeach
            </select>
            <button type="submit" class="btn">Update Status</button>
        </form>
    </div>

    <div class="card">
        <h3>Items</h3>
        <table>
            <thead>
            <tr>
                <th>Product</th>
                <th>Unit Price</th>
                <th>Qty</th>
                <th>Line Total</th>
            </tr>
            </thead>
            <tbody>
            @foreach($order->items as $item)
                <tr>
                    <td>{{ $item->product_name }}</td>
                    <td>KES {{ number_format((float) $item->unit_price, 2) }}</td>
                    <td>{{ $item->quantity }}</td>
                    <td>KES {{ number_format((float) $item->line_total, 2) }}</td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>
@endsection
