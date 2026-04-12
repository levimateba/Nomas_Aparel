@extends('layouts.storefront')

@section('title', 'Track Order')

@section('content')
<div class="container" style="margin-top:16px;">
    <style>
        .timeline { display:flex; gap:8px; flex-wrap:wrap; margin: 10px 0 14px; }
        .timeline-step {
            padding: 8px 12px;
            border-radius: 999px;
            border: 1px solid #d1d5db;
            color: #6b7280;
            font-size: 13px;
            font-weight: 600;
            background: #fff;
        }
        .timeline-step.active {
            border-color: #c9a227;
            background: #f8f2df;
            color: #7b6116;
        }
        .timeline-step.done {
            border-color: #16a34a;
            background: #ecfdf5;
            color: #166534;
        }
    </style>
    <div class="card" style="padding:16px;">
        <h2 style="margin-top:0;">Track Your Order</h2>
        <p style="color:#6b7280;">Enter your order number and the email used at checkout.</p>

        <form method="POST" action="{{ route('orders.track.search') }}" style="display:grid;grid-template-columns:1fr 1fr auto;gap:8px;align-items:end;">
            @csrf
            <div>
                <label>Order Number</label>
                <input type="text" name="order_number" value="{{ old('order_number') }}" required style="width:100%;padding:10px;border:1px solid #d1d5db;border-radius:8px;">
            </div>
            <div>
                <label>Email</label>
                <input type="email" name="customer_email" value="{{ old('customer_email') }}" required style="width:100%;padding:10px;border:1px solid #d1d5db;border-radius:8px;">
            </div>
            <button class="btn btn-primary" type="submit">Track</button>
        </form>

        @if($errors->any())
            <div style="margin-top:10px;padding:10px;border-radius:8px;background:#fef2f2;color:#991b1b;">
                {{ $errors->first() }}
            </div>
        @endif
    </div>

    @isset($order)
        @php
            $statusFlow = ['pending', 'processing', 'paid', 'shipped', 'delivered'];
            $currentStatus = strtolower((string) $order->status);
            $currentIndex = array_search($currentStatus, $statusFlow, true);
            if ($currentIndex === false) {
                $currentIndex = -1;
            }
        @endphp
        <div class="card" style="padding:16px;margin-top:12px;">
            <h3 style="margin-top:0;">Order {{ $order->order_number }}</h3>
            <p><strong>Status:</strong> {{ ucfirst($order->status) }}</p>
            <div class="timeline">
                @foreach($statusFlow as $index => $status)
                    @php
                        $class = 'timeline-step';
                        if ($currentStatus === 'cancelled') {
                            $class .= $status === 'pending' ? ' done' : '';
                        } elseif ($index < $currentIndex) {
                            $class .= ' done';
                        } elseif ($index === $currentIndex) {
                            $class .= ' active';
                        }
                    @endphp
                    <span class="{{ $class }}">{{ ucfirst($status) }}</span>
                @endforeach
                @if($currentStatus === 'cancelled')
                    <span class="timeline-step active" style="border-color:#dc2626;background:#fee2e2;color:#b91c1c;">Cancelled</span>
                @endif
            </div>
            <p><strong>Payment:</strong> {{ ucwords(str_replace('_', ' ', $order->payment_method)) }}</p>
            <p><strong>Total:</strong> KES {{ number_format((float) $order->total_amount, 2) }}</p>
            <p><strong>Placed:</strong> {{ $order->created_at->format('M d, Y H:i') }}</p>

            <h4>Items</h4>
            <div style="display:grid;gap:8px;">
                @foreach($order->items as $item)
                    <div style="display:flex;justify-content:space-between;border-bottom:1px solid #e5e7eb;padding-bottom:6px;">
                        <span>{{ $item->product_name }} x{{ $item->quantity }}</span>
                        <span>KES {{ number_format((float) $item->line_total, 2) }}</span>
                    </div>
                @endforeach
            </div>
        </div>
    @endisset
</div>
@endsection
