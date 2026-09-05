@extends('layouts.admin')
@section('title', 'Order Details')
@section('heading', 'Order ' . $order->order_number)
@section('subheading', 'Created ' . $order->created_at->format('d M Y, H:i'))

@push('styles')
<style>
.os-page { display: flex; flex-direction: column; gap: 18px; }

/* Header bar */
.os-header {
    background: #fff; border: 1px solid #eaecf0; border-radius: 16px;
    padding: 18px 22px; display: flex; align-items: center; justify-content: space-between;
    flex-wrap: wrap; gap: 14px; box-shadow: 0 1px 4px rgba(0,0,0,.05);
}
.os-header-left h2 { margin: 0; font-size: 1.4rem; font-weight: 800; color: #111827; }
.os-header-left p  { margin: 4px 0 0; font-size: 12.5px; color: #9ca3af; }
.os-header-right   { display: flex; align-items: center; gap: 10px; flex-wrap: wrap; }

.os-badge { display: inline-flex; border-radius: 999px; padding: 5px 14px; font-size: 12px; font-weight: 700; letter-spacing: .04em; }
.os-badge.paid, .os-badge.delivered, .os-badge.completed { background: #dcfce7; color: #166534; }
.os-badge.processing, .os-badge.shipped { background: #dbeafe; color: #1d4ed8; }
.os-badge.cancelled { background: #fee2e2; color: #991b1b; }
.os-badge.pending, .os-badge.cancellation_requested, .os-badge.return_requested { background: #fef3c7; color: #92400e; }

.os-action-btn {
    background: #fff; border: 1px solid #d1d5db; border-radius: 10px;
    padding: 8px 14px; font-size: 13px; font-weight: 700; color: #374151;
    text-decoration: none; display: inline-flex; align-items: center; gap: 6px; cursor: pointer;
}
.os-action-btn:hover { background: #f9fafb; }
.os-action-btn.primary {
    background: linear-gradient(135deg,#d4af37,#b8942d); color: #1a1300;
    border: none; box-shadow: 0 3px 10px rgba(212,175,55,.25);
}
.os-action-btn.primary:hover { opacity: .9; }

/* Body grid */
.os-body { display: grid; grid-template-columns: 1.5fr 1fr; gap: 16px; }
@media(max-width:900px) { .os-body { grid-template-columns: 1fr; } }

/* Panels */
.os-panel {
    background: #fff; border: 1px solid #eaecf0; border-radius: 16px;
    overflow: hidden; box-shadow: 0 1px 4px rgba(0,0,0,.04);
}
.os-panel-head { padding: 14px 18px; border-bottom: 1px solid #f3f4f6; background: #fafafa; }
.os-panel-head h3 { margin: 0; font-size: 14px; font-weight: 700; color: #111827; }
.os-panel-body { padding: 18px; }

/* Detail grid */
.os-detail-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 14px; }
@media(max-width:600px) { .os-detail-grid { grid-template-columns: 1fr; } }
.os-detail-item { display: flex; flex-direction: column; gap: 3px; }
.os-detail-label { font-size: 11px; font-weight: 700; color: #9ca3af; text-transform: uppercase; letter-spacing: .05em; }
.os-detail-value { font-size: 13.5px; color: #111827; font-weight: 500; }

/* Status update */
.os-status-form { display: flex; gap: 10px; align-items: flex-end; flex-wrap: wrap; }
.os-status-field { display: flex; flex-direction: column; gap: 5px; }
.os-status-field label { font-size: 11px; font-weight: 700; color: #6b7280; text-transform: uppercase; letter-spacing: .05em; }
.os-input {
    padding: 9px 12px; border: 1px solid #d1d5db; border-radius: 10px;
    font-size: 13px; color: #111827; outline: none; min-width: 200px; margin: 0 !important;
}
.os-input:focus { border-color: #d4af37; box-shadow: 0 0 0 3px rgba(212,175,55,.12); }

/* Items table */
.os-items-table { width: 100%; border-collapse: collapse; }
.os-items-table thead th { font-size: 11px; font-weight: 700; color: #6b7280; text-transform: uppercase; letter-spacing: .05em; padding: 10px 14px; background: #f9fafb; border-bottom: 1px solid #eaecf0; text-align: left; }
.os-items-table tbody td { padding: 12px 14px; font-size: 13px; color: #374151; border-bottom: 1px solid #f3f4f6; }
.os-items-table tbody tr:last-child td { border-bottom: none; }
.os-items-table tbody tr:hover td { background: rgba(212,175,55,.03); }

/* Totals */
.os-totals { display: flex; flex-direction: column; gap: 8px; padding: 14px 18px; border-top: 1px solid #f3f4f6; }
.os-total-row { display: flex; justify-content: space-between; font-size: 13px; color: #374151; }
.os-total-row.grand { font-weight: 800; font-size: 15px; color: #111827; padding-top: 8px; border-top: 1px solid #eaecf0; margin-top: 4px; }

/* Notes */
.os-note {
    background: #fffbeb; border: 1px solid #fde68a; border-radius: 10px;
    padding: 12px 14px; font-size: 13px; color: #92400e; margin-top: 14px;
    display: flex; gap: 8px;
}
.os-note-warn { background: #fee2e2; border-color: #fca5a5; color: #991b1b; }
</style>
@endpush

@section('content')
@php
    $statusClass = match($order->status) {
        'paid','delivered','completed' => 'paid',
        'processing','shipped' => 'processing',
        'cancelled' => 'cancelled',
        default => 'pending',
    };
    $paymentClass = match(strtolower((string)($order->payment_status ?? 'pending'))) {
        'paid','completed' => 'paid',
        'processing','pending' => 'pending',
        default => 'pending',
    };
    $isPos = ($order->source ?? null) === 'pos' || str_starts_with((string)$order->order_number, 'POS-');
@endphp

<div class="os-page">

    {{-- Header --}}
    <div class="os-header">
        <div class="os-header-left">
            <h2>Order {{ $order->order_number }}</h2>
            <p>Created on {{ $order->created_at->format('d M Y, H:i') }} &bull; {{ $isPos ? 'In-store POS' : 'Online' }}</p>
        </div>
        <div class="os-header-right">
            <span class="os-badge {{ $statusClass }}">{{ ucwords(str_replace('_',' ',$order->status)) }}</span>
            @if($isPos)
                <a href="{{ route('admin.pos.receipt', $order) }}" class="os-action-btn primary">
                    <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/></svg>
                    POS Receipt
                </a>
                @if(auth()->user()?->hasPermission('process_return') && $order->isReturnable())
                    <a href="{{ route('admin.returns.create', ['order_id' => $order->id]) }}" class="os-action-btn">Process Return</a>
                @endif
            @endif
            <a href="{{ route('admin.orders.print', $order) }}" target="_blank" class="os-action-btn">
                <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                Print Invoice
            </a>
            <a href="{{ route('admin.orders.pdf', $order) }}" class="os-action-btn">
                <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                Download PDF
            </a>
            <form method="POST" action="{{ route('admin.orders.send-confirmation', $order) }}" style="display:inline;">
                @csrf
                <button type="submit" class="os-action-btn">Resend Email</button>
            </form>
        </div>
    </div>

    <div class="os-body">
        {{-- Left column --}}
        <div style="display:flex;flex-direction:column;gap:16px;">

            {{-- Order Items --}}
            <div class="os-panel">
                <div class="os-panel-head"><h3>Order Items</h3></div>
                <div style="overflow-x:auto;">
                    <table class="os-items-table">
                        <thead>
                            <tr>
                                <th>Product</th>
                                <th>Unit Price</th>
                                <th>Qty</th>
                                <th style="text-align:right;">Line Total</th>
                            </tr>
                        </thead>
                        <tbody>
                        @foreach($order->items as $item)
                            <tr>
                                <td><strong>{{ $item->product_name }}</strong></td>
                                <td>KES {{ number_format((float)$item->unit_price, 2) }}</td>
                                <td>{{ $item->quantity }}</td>
                                <td style="text-align:right;font-weight:700;">KES {{ number_format((float)$item->line_total, 2) }}</td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="os-totals">
                    @if((float)($order->discount_amount ?? 0) > 0)
                        <div class="os-total-row">
                            <span>Subtotal</span>
                            <span>KES {{ number_format((float)$order->total_amount + (float)$order->discount_amount + (float)($order->loyalty_discount_amount ?? 0) - (($settings->tax_inclusive ?? true) ? 0 : (float)($order->tax_amount ?? 0)), 2) }}</span>
                        </div>
                        <div class="os-total-row" style="color:#059669;">
                            <span>Discount</span>
                            <span>− KES {{ number_format((float)$order->discount_amount, 2) }}</span>
                        </div>
                    @endif
                    @if(($settings->tax_enabled ?? false) || (float)($order->tax_amount ?? 0) > 0)
                        <div class="os-total-row">
                            <span>{{ method_exists($settings, 'taxReceiptLabel') ? $settings->taxReceiptLabel() : 'Tax' }}</span>
                            <span>KES {{ number_format((float)($order->tax_amount ?? 0), 2) }}</span>
                        </div>
                    @endif
                    <div class="os-total-row grand">
                        <span>Order Total</span>
                        <span>KES {{ number_format((float)$order->total_amount, 2) }}</span>
                    </div>
                </div>
            </div>

            {{-- Update Status --}}
            <div class="os-panel">
                <div class="os-panel-head"><h3>Update Status</h3></div>
                <div class="os-panel-body">
                    <form method="POST" action="{{ route('admin.orders.update', $order) }}" class="os-status-form">
                        @csrf @method('PUT')
                        <div class="os-status-field">
                            <label>Order Status</label>
                            <select name="status" class="os-input">
                                @foreach(['pending','processing','paid','shipped','delivered','cancellation_requested','return_requested','cancelled'] as $s)
                                    <option value="{{ $s }}" @selected($order->status === $s)>{{ ucwords(str_replace('_',' ',$s)) }}</option>
                                @endforeach
                            </select>
                        </div>
                        <button type="submit" class="os-action-btn primary" style="margin-top:0;">
                            <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                            Update Status
                        </button>
                    </form>
                </div>
            </div>

        </div>

        {{-- Right column --}}
        <div style="display:flex;flex-direction:column;gap:16px;">

            {{-- Customer Info --}}
            <div class="os-panel">
                <div class="os-panel-head"><h3>Customer</h3></div>
                <div class="os-panel-body">
                    <div class="os-detail-grid">
                        <div class="os-detail-item" style="grid-column:1/-1;">
                            <span class="os-detail-label">Name</span>
                            <span class="os-detail-value" style="font-weight:700;">{{ $order->customer_name }}</span>
                        </div>
                        <div class="os-detail-item">
                            <span class="os-detail-label">Email</span>
                            <span class="os-detail-value">{{ $order->customer_email ?: '—' }}</span>
                        </div>
                        <div class="os-detail-item">
                            <span class="os-detail-label">Phone</span>
                            <span class="os-detail-value">{{ $order->customer_phone ?: '—' }}</span>
                        </div>
                        @if($order->shipping_address)
                            <div class="os-detail-item" style="grid-column:1/-1;">
                                <span class="os-detail-label">Shipping Address</span>
                                <span class="os-detail-value">{{ $order->shipping_address }}</span>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            {{-- Payment Info --}}
            <div class="os-panel">
                <div class="os-panel-head"><h3>Payment</h3></div>
                <div class="os-panel-body">
                    <div class="os-detail-grid">
                        <div class="os-detail-item">
                            <span class="os-detail-label">Method</span>
                            <span class="os-detail-value">{{ ucwords(str_replace('_',' ',$order->payment_method)) }}</span>
                        </div>
                        <div class="os-detail-item">
                            <span class="os-detail-label">Status</span>
                            <span class="os-detail-value">
                                <span class="os-badge {{ $paymentClass }}" style="font-size:11.5px;padding:3px 10px;">
                                    {{ ucfirst($order->payment_status ?? 'pending') }}
                                </span>
                            </span>
                        </div>
                        <div class="os-detail-item">
                            <span class="os-detail-label">Source</span>
                            <span class="os-detail-value">{{ $isPos ? 'In-store POS' : 'Online' }}</span>
                        </div>
                        @if($order->payment_reference)
                            <div class="os-detail-item" style="grid-column:1/-1;">
                                <span class="os-detail-label">Reference</span>
                                <span class="os-detail-value" style="font-family:monospace;font-size:12px;">{{ $order->payment_reference }}</span>
                            </div>
                        @endif
                        @if($order->user)
                            <div class="os-detail-item" style="grid-column:1/-1;">
                                <span class="os-detail-label">Cashier</span>
                                <span class="os-detail-value">{{ $order->user->name }}</span>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            {{-- Notes --}}
            @if($order->notes)
                <div class="os-note">
                    <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                    <div><strong>Note:</strong> {{ $order->notes }}</div>
                </div>
            @endif

            @if($order->cancellation_requested_at)
                <div class="os-note os-note-warn">
                    <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/></svg>
                    <div>
                        <strong>Cancellation Requested:</strong> {{ $order->cancellation_requested_at->format('M d, Y H:i') }}<br>
                        <strong>Reason:</strong> {{ $order->cancellation_reason ?: 'N/A' }}
                    </div>
                </div>
            @endif

            @if($order->return_requested_at)
                <div class="os-note os-note-warn">
                    <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                    <div>
                        <strong>Return Requested:</strong> {{ $order->return_requested_at->format('M d, Y H:i') }}<br>
                        <strong>Reason:</strong> {{ $order->return_reason ?: 'N/A' }}
                    </div>
                </div>
            @endif

        </div>
    </div>
</div>
@endsection
