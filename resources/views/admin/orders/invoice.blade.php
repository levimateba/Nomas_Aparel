<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Invoice {{ $order->order_number }}</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; color: #111827; }
        .top { display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 18px; }
        .brand { font-size: 24px; font-weight: 700; color: #16456e; }
        .muted { color: #6b7280; font-size: 13px; }
        .block { border: 1px solid #e5e7eb; border-radius: 8px; padding: 12px; margin-bottom: 12px; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #e5e7eb; padding: 8px; text-align: left; }
        th { background: #f9fafb; }
        .right { text-align: right; }
        .actions { margin: 12px 0 18px; display: flex; gap: 8px; }
        .btn { display: inline-block; padding: 8px 12px; border-radius: 6px; text-decoration: none; color: #fff; background: #16456e; }
        @media print { .actions { display: none; } }
    </style>
</head>
<body>
    <div class="actions">
        <a class="btn" href="{{ route('admin.orders.pdf', $order) }}">Download PDF</a>
        <a class="btn" href="#" onclick="window.print(); return false;">Print</a>
    </div>

    <div class="top">
        <div>
            <div class="brand">{{ $settings->site_name ?? 'Store' }}</div>
            <div class="muted">{{ $settings->site_tagline ?? 'Marketplace' }}</div>
        </div>
        <div class="right">
            <div><strong>Invoice</strong></div>
            <div class="muted">{{ $order->order_number }}</div>
            <div class="muted">Generated {{ $generatedAt->format('M d, Y H:i') }}</div>
        </div>
    </div>

    <div class="block">
        <strong>Customer</strong><br>
        {{ $order->customer_name }}<br>
        {{ $order->customer_email }}<br>
        {{ $order->customer_phone ?: 'N/A' }}
    </div>

    <div class="block">
        <strong>Shipping Address</strong><br>
        {{ $order->shipping_address }}
        <br><br>
        <strong>Payment Method:</strong> {{ ucwords(str_replace('_', ' ', $order->payment_method)) }}<br>
        <strong>Status:</strong> {{ ucfirst($order->status) }}
    </div>

    <table>
        <thead>
        <tr>
            <th>Product</th>
            <th class="right">Unit Price</th>
            <th class="right">Qty</th>
            <th class="right">Line Total</th>
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
        <tr>
            <td colspan="3" class="right"><strong>Total</strong></td>
            <td class="right"><strong>KES {{ number_format((float) $order->total_amount, 2) }}</strong></td>
        </tr>
        </tbody>
    </table>
</body>
</html>
