<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Invoice {{ $order->order_number }}</title>
    <style>
        body { font-family: DejaVu Sans, Arial, sans-serif; color: #1f2937; margin: 0; font-size: 12px; }
        .page { padding: 30px; }
        .top { width: 100%; border-bottom: 2px solid #16456e; padding-bottom: 12px; margin-bottom: 16px; }
        .brand { font-size: 22px; font-weight: 700; color: #16456e; }
        .muted { color: #6b7280; font-size: 11px; }
        .title { font-size: 14px; text-transform: uppercase; letter-spacing: 1px; color: #165752; font-weight: 700; margin-top: 8px; }
        .block { border: 1px solid #d1d5db; border-radius: 6px; margin-top: 12px; }
        .block-head { background: #f4f6f8; padding: 8px 10px; font-size: 11px; font-weight: 700; text-transform: uppercase; }
        .block-body { padding: 10px; }
        .items { width: 100%; border-collapse: collapse; margin-top: 8px; }
        .items th, .items td { border: 1px solid #d1d5db; padding: 7px; vertical-align: top; }
        .items th { background: #f8fafc; text-align: left; }
        .right { text-align: right; }
        .totals { margin-top: 12px; width: 42%; margin-left: auto; border-collapse: collapse; }
        .totals td { border: 1px solid #d1d5db; padding: 7px; }
        .totals .label { background: #f8fafc; font-weight: 700; }
        .totals .final { font-size: 13px; font-weight: 700; }
    </style>
</head>
<body>
<div class="page">
    <table class="top" cellpadding="0" cellspacing="0">
        <tr>
            <td>
                <div class="brand">{{ $settings->site_name ?? 'Store' }}</div>
                <div class="muted">{{ $settings->site_tagline ?? 'Marketplace' }}</div>
            </td>
            <td class="right">
                <div class="title">Order Invoice</div>
                <div class="muted">{{ $order->order_number }}</div>
                <div class="muted">Generated: {{ $generatedAt->format('M d, Y H:i') }}</div>
            </td>
        </tr>
    </table>

    <div class="block">
        <div class="block-head">Customer Details</div>
        <div class="block-body">
            <strong>{{ $order->customer_name }}</strong><br>
            {{ $order->customer_email }}<br>
            {{ $order->customer_phone ?: 'N/A' }}<br>
            {{ $order->shipping_address }}
        </div>
    </div>

    <div class="block">
        <div class="block-head">Order Details</div>
        <div class="block-body">
            <strong>Payment Method:</strong> {{ ucwords(str_replace('_', ' ', $order->payment_method)) }}<br>
            <strong>Status:</strong> {{ ucfirst($order->status) }}<br>
            @if($order->notes)
                <strong>Notes:</strong> {{ $order->notes }}
            @endif
        </div>
    </div>

    <table class="items">
        <thead>
            <tr>
                <th>Product</th>
                <th style="width:15%;">Unit Price</th>
                <th style="width:10%;">Qty</th>
                <th style="width:15%;">Total</th>
            </tr>
        </thead>
        <tbody>
        @foreach($order->items as $item)
            <tr>
                <td>{{ $item->product_name }}</td>
                <td class="right">KES {{ number_format((float) $item->unit_price, 2) }}</td>
                <td class="right">{{ $item->quantity }}</td>
                <td class="right">KES {{ number_format((float) $item->line_total, 2) }}</td>
            </tr>
        @endforeach
        </tbody>
    </table>

    <table class="totals">
        <tr>
            <td class="label final">Total Amount</td>
            <td class="right final">KES {{ number_format((float) $order->total_amount, 2) }}</td>
        </tr>
    </table>
</div>
</body>
</html>
