<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Receipt {{ $order->order_number }}</title>
    @php
        $brandName = $settings->displayName();
        $receiptHeader = trim((string) ($settings->receipt_header ?: $brandName));
        $receiptFooter = trim((string) ($settings->receipt_footer ?: 'Thank you for shopping with us.'));
        $logoSrc = $settings->showsLogoOnReceipts() ? $settings->logoDataUri() : null;
        $paymentLabel = ucwords(str_replace('_', ' ', (string) $order->payment_method));
        $cashReceived = null;
        $cashChange = null;
        $displayNotes = trim((string) ($order->notes ?? ''));
        if (preg_match('/Cash received:\s*KES\s*([0-9,]+\.\d{2}).*Change:\s*KES\s*([0-9,]+\.\d{2})/is', $displayNotes, $cashMatch)) {
            $cashReceived = $cashMatch[1];
            $cashChange = $cashMatch[2];
        }
    @endphp
    <style>
        body { font-family: DejaVu Sans, Arial, sans-serif; color: #111; margin: 0; font-size: 11px; }
        .page { padding: 18px 16px; max-width: 320px; margin: 0 auto; }
        .brand { text-align: center; margin-bottom: 8px; }
        .brand img { max-height: 42px; max-width: 120px; margin-bottom: 4px; }
        .brand strong { display: block; font-size: 13px; text-transform: uppercase; letter-spacing: .04em; }
        .muted { color: #555; font-size: 10px; text-align: center; }
        .rule { border: 0; border-top: 1px dashed #bbb; margin: 8px 0; }
        .title { text-align: center; font-weight: 700; letter-spacing: .1em; font-size: 11px; margin: 6px 0; }
        .kv { width: 100%; margin: 2px 0; }
        .kv td { font-size: 10px; vertical-align: top; }
        .kv td:last-child { text-align: right; font-weight: 700; }
        table.items { width: 100%; border-collapse: collapse; margin-top: 4px; }
        table.items th { text-align: left; font-size: 9px; border-bottom: 1px solid #111; padding: 3px 0; }
        table.items th:nth-child(2), table.items td:nth-child(2) { text-align: center; width: 28px; }
        table.items th:last-child, table.items td:last-child { text-align: right; width: 70px; }
        table.items td { padding: 4px 0; vertical-align: top; font-size: 10px; }
        .tot td { font-weight: 700; border-top: 1px dashed #111; padding-top: 6px; font-size: 12px; }
        .thanks { text-align: center; margin-top: 10px; font-size: 10px; }
        .cash { border: 1px dashed #111; padding: 6px; margin-top: 8px; }
    </style>
</head>
<body>
<div class="page">
    <div class="brand">
        @if($logoSrc)
            <img src="{{ $logoSrc }}" alt="{{ $brandName }}">
        @endif
        <strong>{{ $receiptHeader }}</strong>
        @if($settings->phone)<div class="muted">{{ $settings->phone }}</div>@endif
        @if($settings->address)<div class="muted">{{ collect([$settings->address, $settings->city])->filter()->implode(', ') }}</div>@endif
    </div>

    <div class="title">IN-STORE RECEIPT</div>
    <table class="kv">
        <tr><td>Receipt</td><td>{{ $order->order_number }}</td></tr>
        <tr><td>Date</td><td>{{ $order->created_at->format('d M Y H:i') }}</td></tr>
    </table>
    <hr class="rule">

    <table class="items">
        <thead>
            <tr>
                <th>Item</th>
                <th>Qty</th>
                <th>Total</th>
            </tr>
        </thead>
        <tbody>
            @foreach($order->items as $item)
                <tr>
                    <td>{{ $item->product_name }}</td>
                    <td>{{ $item->quantity }}</td>
                    <td>{{ number_format((float) $item->line_total, 2) }}</td>
                </tr>
            @endforeach
            @if((float) ($order->discount_amount ?? 0) > 0)
                <tr>
                    <td colspan="2">Discount</td>
                    <td>-{{ number_format((float) $order->discount_amount, 2) }}</td>
                </tr>
            @endif
            @if((float) ($order->loyalty_discount_amount ?? 0) > 0)
                <tr>
                    <td colspan="2">Loyalty discount</td>
                    <td>-{{ number_format((float) $order->loyalty_discount_amount, 2) }}</td>
                </tr>
            @endif
            @if(($settings->tax_enabled ?? false) || (float) ($order->tax_amount ?? 0) > 0)
                <tr>
                    <td colspan="2">{{ $settings->taxReceiptLabel() }}</td>
                    <td>{{ number_format((float) ($order->tax_amount ?? 0), 2) }}</td>
                </tr>
            @endif
            <tr class="tot">
                <td colspan="2">TOTAL</td>
                <td>{{ number_format((float) $order->total_amount, 2) }}</td>
            </tr>
        </tbody>
    </table>

    <table class="kv" style="margin-top:8px;">
        <tr><td>Customer</td><td>{{ $order->customer_name ?: 'Walk-in' }}</td></tr>
        <tr><td>Payment</td><td>{{ $paymentLabel }}</td></tr>
    </table>

    @if($cashReceived !== null)
        <div class="cash">
            <table class="kv">
                <tr><td>Cash received</td><td>KES {{ $cashReceived }}</td></tr>
                <tr><td>Change</td><td>KES {{ $cashChange }}</td></tr>
            </table>
        </div>
    @endif

    <div class="thanks">{{ $receiptFooter }}</div>
</div>
</body>
</html>
