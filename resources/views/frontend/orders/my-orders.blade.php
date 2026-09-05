@extends('layouts.storefront')

@section('title', 'My Orders')

@push('styles')
<style>
    .mo-page { margin: 16px 0 28px; }
    .mo-head { margin-bottom: 16px; }
    .mo-head h1 { margin: 0 0 6px; font-size: 1.55rem; font-weight: 800; color: #121212; }
    .mo-head p { margin: 0; color: #6b7280; font-size: 14px; }

    .mo-card {
        background: #fff;
        border: 1px solid #ececec;
        border-radius: 14px;
        box-shadow: 0 2px 10px rgba(16, 24, 40, 0.04);
        padding: 16px;
        overflow: hidden;
    }

    .mo-table-wrap { overflow-x: auto; -webkit-overflow-scrolling: touch; }
    .mo-table { width: 100%; border-collapse: collapse; min-width: 640px; }
    .mo-table thead th {
        font-size: 11px;
        font-weight: 700;
        color: #6b7280;
        text-transform: uppercase;
        letter-spacing: .05em;
        padding: 10px 12px;
        border-bottom: 1px solid #f3f4f6;
        text-align: left;
        background: #fafafa;
    }
    .mo-table tbody td {
        padding: 12px;
        font-size: 13px;
        color: #374151;
        border-bottom: 1px solid #f3f4f6;
        vertical-align: middle;
    }
    .mo-table tbody tr:last-child td { border-bottom: none; }
    .mo-table .td-label {
        display: none;
        font-size: 10px;
        font-weight: 700;
        color: #9ca3af;
        text-transform: uppercase;
        letter-spacing: .04em;
        margin-bottom: 3px;
    }
    .mo-order-no {
        font-weight: 700;
        color: #121212;
        word-break: break-all;
        font-size: 12px;
        line-height: 1.35;
    }
    .mo-status {
        display: inline-flex;
        border-radius: 999px;
        padding: 3px 9px;
        font-size: 11px;
        font-weight: 700;
        background: #fef3c7;
        color: #92400e;
    }
    .mo-status.is-paid,
    .mo-status.is-delivered,
    .mo-status.is-completed { background: #dcfce7; color: #166534; }
    .mo-status.is-cancelled { background: #fee2e2; color: #991b1b; }
    .mo-status.is-shipped,
    .mo-status.is-processing { background: #dbeafe; color: #1d4ed8; }
    .mo-status.is-return { background: #eff6ff; color: #1e40af; }
    .mo-total { font-weight: 800; color: #121212; white-space: nowrap; }
    .mo-date { color: #6b7280; font-size: 12px; white-space: nowrap; }
    .mo-view {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        padding: 7px 12px;
        border-radius: 8px;
        background: linear-gradient(135deg, #d4af37, #b8942d);
        color: #121212;
        font-size: 12px;
        font-weight: 800;
        white-space: nowrap;
    }
    .mo-empty {
        text-align: center;
        padding: 36px 16px;
        color: #6b7280;
        font-size: 14px;
    }
    .mo-empty a {
        display: inline-flex;
        margin-top: 12px;
        padding: 10px 16px;
        border-radius: 10px;
        background: linear-gradient(135deg, #d4af37, #b8942d);
        color: #121212;
        font-weight: 800;
        font-size: 13px;
    }
    .mo-pager { margin-top: 14px; }

    @media (max-width: 768px) {
        .mo-table-wrap { overflow: visible; }
        .mo-table { min-width: 0; }
        .mo-table thead { display: none; }
        .mo-table tbody tr {
            display: block;
            border: 1px solid #ececec;
            border-radius: 12px;
            padding: 12px;
            margin-bottom: 10px;
            background: #fff;
        }
        .mo-table tbody td {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            padding: 7px 0;
            border: 0;
        }
        .mo-table tbody td:first-child {
            flex-direction: column;
            align-items: flex-start;
            gap: 4px;
            padding-top: 0;
            padding-bottom: 10px;
            border-bottom: 1px dashed #ececec;
            margin-bottom: 4px;
        }
        .mo-table tbody td:last-child {
            padding-top: 10px;
            padding-bottom: 0;
            border-top: 1px dashed #ececec;
            margin-top: 4px;
            justify-content: stretch;
        }
        .mo-table tbody td:last-child .mo-view { width: 100%; }
        .mo-table .td-label { display: block; flex: 0 0 auto; }
        .mo-table tbody td:first-child .td-label { display: none; }
    }
</style>
@endpush

@section('content')
<div class="container mo-page">
    <div class="mo-head">
        <h1>My Orders</h1>
        <p>Orders placed while logged in to your account.</p>
    </div>

    <div class="mo-card">
        <div class="mo-table-wrap">
            <table class="mo-table">
                <thead>
                    <tr>
                        <th>Order #</th>
                        <th>Items</th>
                        <th>Status</th>
                        <th>Total</th>
                        <th>Date</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($orders as $order)
                        @php
                            $statusClass = match($order->status) {
                                'paid', 'delivered', 'completed' => 'is-paid',
                                'cancelled' => 'is-cancelled',
                                'processing', 'shipped' => 'is-processing',
                                'return_requested' => 'is-return',
                                default => '',
                            };
                        @endphp
                        <tr>
                            <td>
                                <span class="mo-order-no">{{ $order->order_number }}</span>
                            </td>
                            <td>
                                <span class="td-label">Items</span>
                                {{ $order->items_count }}
                            </td>
                            <td>
                                <span class="td-label">Status</span>
                                <span class="mo-status {{ $statusClass }}">{{ ucfirst(str_replace('_', ' ', $order->status)) }}</span>
                            </td>
                            <td>
                                <span class="td-label">Total</span>
                                <span class="mo-total">KES {{ number_format((float) $order->total_amount, 2) }}</span>
                            </td>
                            <td>
                                <span class="td-label">Date</span>
                                <span class="mo-date">{{ $order->created_at->format('M d, Y') }}<br>{{ $order->created_at->format('H:i') }}</span>
                            </td>
                            <td>
                                <a href="{{ route('orders.my.show', $order) }}" class="mo-view">View order</a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6">
                                <div class="mo-empty">
                                    You have not placed any orders yet.
                                    <br>
                                    <a href="{{ route('shop.index') }}">Start shopping</a>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($orders->hasPages())
            <div class="mo-pager">{{ $orders->links() }}</div>
        @endif
    </div>
</div>
@endsection
