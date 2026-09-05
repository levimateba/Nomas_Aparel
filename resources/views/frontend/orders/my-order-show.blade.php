@extends('layouts.storefront')

@section('title', 'Order ' . $order->order_number)

@push('styles')
<style>
    .os-page { margin: 16px 0 28px; }
    .os-card {
        background: #fff;
        border: 1px solid #ececec;
        border-radius: 14px;
        box-shadow: 0 2px 10px rgba(16, 24, 40, 0.04);
        padding: 16px;
    }
    .os-head h1 { margin: 0 0 12px; font-size: 1.35rem; font-weight: 800; word-break: break-all; }
    .os-meta {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 10px;
        margin-bottom: 16px;
    }
    .os-meta-item {
        padding: 10px 12px;
        border-radius: 10px;
        background: #fafafa;
        border: 1px solid #f3f4f6;
        font-size: 13px;
    }
    .os-meta-item span { display: block; font-size: 11px; color: #9ca3af; font-weight: 700; text-transform: uppercase; margin-bottom: 4px; }
    .os-alert {
        margin: 12px 0;
        padding: 12px;
        border-radius: 10px;
        background: #fef2f2;
        color: #991b1b;
        font-size: 13px;
        border: 1px solid #fecaca;
    }
    .os-alert.is-return {
        background: #eff6ff;
        color: #1e40af;
        border-color: #bfdbfe;
    }
    .os-cancel, .os-return {
        padding: 12px;
        margin: 12px 0;
        border-radius: 10px;
    }
    .os-cancel {
        border: 1px solid #fecaca;
        background: #fff7f7;
    }
    .os-return {
        border: 1px solid #bfdbfe;
        background: #f8fbff;
    }
    .os-cancel h4, .os-return h4 { margin: 0 0 8px; font-size: 14px; }
    .os-cancel p, .os-return p { margin: 0 0 10px; font-size: 13px; color: #6b7280; }
    .os-cancel textarea, .os-return textarea {
        width: 100%;
        padding: 10px;
        border: 1px solid #d1d5db;
        border-radius: 8px;
        font: inherit;
    }
    .os-items-title { margin: 18px 0 10px; font-size: 16px; font-weight: 800; }
    .os-table-wrap { overflow-x: auto; -webkit-overflow-scrolling: touch; }
    .os-table { width: 100%; border-collapse: collapse; min-width: 520px; }
    .os-table th {
        font-size: 11px;
        font-weight: 700;
        color: #6b7280;
        text-transform: uppercase;
        padding: 10px 12px;
        border-bottom: 1px solid #f3f4f6;
        text-align: left;
        background: #fafafa;
    }
    .os-table td {
        padding: 12px;
        font-size: 13px;
        border-bottom: 1px solid #f3f4f6;
    }
    .os-table .td-label {
        display: none;
        font-size: 10px;
        font-weight: 700;
        color: #9ca3af;
        text-transform: uppercase;
        margin-bottom: 3px;
    }
    .os-actions { margin-top: 14px; }
    .os-back {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        padding: 10px 16px;
        border-radius: 10px;
        background: linear-gradient(135deg, #d4af37, #b8942d);
        color: #121212;
        font-weight: 800;
        font-size: 13px;
    }
    @media (max-width: 640px) {
        .os-meta { grid-template-columns: 1fr; }
        .os-table-wrap { overflow: visible; }
        .os-table { min-width: 0; }
        .os-table thead { display: none; }
        .os-table tbody tr {
            display: block;
            border: 1px solid #ececec;
            border-radius: 10px;
            padding: 10px 12px;
            margin-bottom: 8px;
        }
        .os-table tbody td {
            display: flex;
            justify-content: space-between;
            gap: 12px;
            padding: 6px 0;
            border: 0;
        }
        .os-table tbody td:first-child {
            flex-direction: column;
            align-items: flex-start;
            padding-bottom: 8px;
            margin-bottom: 4px;
            border-bottom: 1px dashed #ececec;
        }
        .os-table .td-label { display: block; }
        .os-table tbody td:first-child .td-label { display: none; }
        .os-back { width: 100%; }
    }
</style>
@endpush

@section('content')
<div class="container os-page">
    <div class="os-card">
        <div class="os-head">
            <h1>Order {{ $order->order_number }}</h1>
        </div>

        <div class="os-meta">
            <div class="os-meta-item">
                <span>Status</span>
                {{ ucfirst(str_replace('_', ' ', $order->status)) }}
            </div>
            <div class="os-meta-item">
                <span>Payment</span>
                {{ ucwords(str_replace('_', ' ', $order->payment_method)) }}
            </div>
            <div class="os-meta-item">
                <span>Total</span>
                KES {{ number_format((float) $order->total_amount, 2) }}
            </div>
            <div class="os-meta-item">
                <span>Placed</span>
                {{ $order->created_at->format('M d, Y H:i') }}
            </div>
        </div>

        @if($order->cancellation_requested_at)
            <div class="os-alert">
                Cancellation requested on {{ $order->cancellation_requested_at->format('M d, Y H:i') }}.
                <br>Reason: {{ $order->cancellation_reason }}
            </div>
        @endif

        @if($order->return_requested_at)
            <div class="os-alert is-return">
                Return requested on {{ $order->return_requested_at->format('M d, Y H:i') }}.
                <br>Reason: {{ $order->return_reason }}
            </div>
        @endif

        @if(in_array($order->status, ['pending', 'processing'], true) && ! $order->cancellation_requested_at)
            <div class="os-cancel">
                <h4>Request cancellation</h4>
                <p>You can cancel this order before payment is completed.</p>
                <form method="POST" action="{{ route('orders.my.cancel-request', $order) }}">
                    @csrf
                    <textarea name="cancellation_reason" rows="3" required placeholder="Tell us why you want to cancel..."></textarea>
                    <button type="submit" class="btn" style="margin-top:8px;">Submit cancellation request</button>
                </form>
            </div>
        @endif

        @if(in_array($order->status, ['paid', 'shipped', 'delivered'], true) && ! $order->return_requested_at)
            <div class="os-return">
                <h4>Request a return</h4>
                <p>This order is already paid. Cancellation is not available — you can request a return instead.</p>
                <form method="POST" action="{{ route('orders.my.return-request', $order) }}">
                    @csrf
                    <textarea name="return_reason" rows="3" required placeholder="Tell us why you want to return this order..."></textarea>
                    <button type="submit" class="btn" style="margin-top:8px;">Submit return request</button>
                </form>
            </div>
        @endif

        <h3 class="os-items-title">Items</h3>
        <div class="os-table-wrap">
            <table class="os-table">
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
                            <td>
                                <span class="td-label">Unit Price</span>
                                KES {{ number_format((float) $item->unit_price, 2) }}
                            </td>
                            <td>
                                <span class="td-label">Qty</span>
                                {{ $item->quantity }}
                            </td>
                            <td>
                                <span class="td-label">Line Total</span>
                                KES {{ number_format((float) $item->line_total, 2) }}
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="os-actions">
            <a href="{{ route('orders.my') }}" class="os-back">Back to My Orders</a>
        </div>
    </div>
</div>
@endsection
