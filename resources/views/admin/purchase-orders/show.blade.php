@extends('layouts.admin')
@section('title', $order->po_number)
@section('heading', $order->po_number)
@section('subheading', 'Status: '.str_replace('_', ' ', $order->status))

@section('content')
<div class="card" style="padding:16px;margin-bottom:14px;">
    <div class="admin-form-grid">
        <div><div class="muted">Supplier</div><strong>{{ $order->supplier?->name ?: '—' }}</strong></div>
        <div><div class="muted">Order date</div><strong>{{ $order->order_date->format('d M Y') }}</strong></div>
        <div><div class="muted">Expected</div><strong>{{ $order->expected_date?->format('d M Y') ?: '—' }}</strong></div>
        <div><div class="muted">Total</div><strong>KES {{ number_format((float)$order->total, 2) }}</strong></div>
    </div>
    <div class="row-actions" style="margin-top:14px;">
        @if($order->status === 'draft')
            <form method="POST" action="{{ route('admin.purchase-orders.submit', $order) }}">@csrf<button class="btn" type="submit">Submit</button></form>
        @endif
        @if(in_array($order->status, ['draft','submitted'], true) && auth()->user()?->hasPermission('approve_purchase_orders'))
            <form method="POST" action="{{ route('admin.purchase-orders.approve', $order) }}">@csrf<button class="btn" type="submit">Approve</button></form>
        @endif
        @if(!in_array($order->status, ['received','cancelled','closed'], true) && (int)$order->items->sum('received_qty') === 0)
            <form method="POST" action="{{ route('admin.purchase-orders.cancel', $order) }}" onsubmit="return confirm('Cancel this PO?')">@csrf<button class="btn btn-danger" type="submit">Cancel</button></form>
        @endif
    </div>
</div>

<div class="card" style="padding:16px;margin-bottom:14px;">
    <h3 style="margin-top:0;">Lines</h3>
    <div class="table-wrap">
        <table class="admin-table" style="width:100%;border-collapse:collapse;">
            <thead>
                <tr>
                    <th style="text-align:left;padding:8px;">Product</th>
                    <th style="text-align:right;padding:8px;">Ordered</th>
                    <th style="text-align:right;padding:8px;">Received</th>
                    <th style="text-align:right;padding:8px;">Outstanding</th>
                    <th style="text-align:right;padding:8px;">Cost</th>
                </tr>
            </thead>
            <tbody>
                @foreach($order->items as $item)
                    <tr>
                        <td style="padding:8px;border-bottom:1px solid #f3f4f6;">{{ $item->product?->name }}</td>
                        <td style="padding:8px;border-bottom:1px solid #f3f4f6;text-align:right;">{{ $item->ordered_qty }}</td>
                        <td style="padding:8px;border-bottom:1px solid #f3f4f6;text-align:right;">{{ $item->received_qty }}</td>
                        <td style="padding:8px;border-bottom:1px solid #f3f4f6;text-align:right;">{{ $item->outstandingQty() }}</td>
                        <td style="padding:8px;border-bottom:1px solid #f3f4f6;text-align:right;">{{ number_format((float)$item->buying_price, 2) }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>

@if(in_array($order->status, ['approved','partially_received'], true) && auth()->user()?->hasPermission('manage_purchases'))
<div class="card" style="padding:16px;">
    <h3 style="margin-top:0;">Receive stock</h3>
    <form method="POST" action="{{ route('admin.purchase-orders.receive', $order) }}">
        @csrf
        <div class="admin-form-grid">
            <div>
                <label>Receive date</label>
                <input type="date" name="purchase_date" value="{{ now()->toDateString() }}">
            </div>
            <div>
                <label>Invoice reference</label>
                <input type="text" name="invoice_reference">
            </div>
            <div>
                <label>Payment status</label>
                <select name="payment_status">
                    <option value="unpaid">Unpaid</option>
                    <option value="paid">Paid</option>
                    <option value="partial">Partial</option>
                </select>
            </div>
        </div>
        <div class="table-wrap" style="margin-top:12px;">
            <table class="admin-table" style="width:100%;border-collapse:collapse;">
                <thead>
                    <tr>
                        <th style="text-align:left;padding:8px;">Product</th>
                        <th style="text-align:left;padding:8px;">Qty to receive</th>
                        <th style="text-align:left;padding:8px;">Buying price</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($order->items as $i => $item)
                        @if($item->outstandingQty() > 0)
                            <tr>
                                <td style="padding:8px;border-bottom:1px solid #f3f4f6;">
                                    <input type="hidden" name="items[{{ $i }}][purchase_order_item_id]" value="{{ $item->id }}">
                                    {{ $item->product?->name }}
                                    <div class="muted">Outstanding {{ $item->outstandingQty() }}</div>
                                </td>
                                <td style="padding:8px;border-bottom:1px solid #f3f4f6;">
                                    <input type="number" min="0" max="{{ $item->outstandingQty() }}" name="items[{{ $i }}][quantity]" value="{{ $item->outstandingQty() }}">
                                </td>
                                <td style="padding:8px;border-bottom:1px solid #f3f4f6;">
                                    <input type="number" step="0.01" min="0" name="items[{{ $i }}][buying_price]" value="{{ $item->buying_price }}">
                                </td>
                            </tr>
                        @endif
                    @endforeach
                </tbody>
            </table>
        </div>
        <button type="submit" class="btn" style="margin-top:12px;">Receive selected quantities</button>
    </form>
</div>
@endif

@if($order->receipts->isNotEmpty())
<div class="card" style="padding:16px;margin-top:14px;">
    <h3 style="margin-top:0;">Linked purchases</h3>
    @foreach($order->receipts as $receipt)
        <div style="padding:8px 0;border-bottom:1px solid #f3f4f6;">
            <a href="{{ route('admin.purchases.show', $receipt) }}">{{ $receipt->purchase_number }}</a>
            · KES {{ number_format((float)$receipt->total, 2) }}
        </div>
    @endforeach
</div>
@endif
@endsection
