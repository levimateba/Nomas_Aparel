<!doctype html>
<html>
<head>
    <meta charset="utf-8">
    <title>Stock Valuation</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 11px; color:#111; }
        h1 { font-size: 18px; margin: 0 0 4px; color: #a58112; }
        .muted { color: #555; margin: 0 0 12px; }
        .header { width: 100%; border-bottom: 2px solid #a58112; padding-bottom: 10px; margin-bottom: 12px; }
        .brand-logo { max-height: 52px; max-width: 140px; }
        .kpis td { padding: 8px; border: 1px solid #ddd; }
        table.data { width:100%; border-collapse: collapse; margin-top: 12px; }
        table.data th, table.data td { border:1px solid #ddd; padding:5px; text-align:left; }
        table.data th { background:#f3f4f6; font-size:10px; text-transform:uppercase; }
        .right { text-align:right; }
    </style>
</head>
<body>
    <table class="header" cellpadding="0" cellspacing="0">
        <tr>
            <td style="vertical-align:middle; width:60%;">
                @if(!empty($logoSrc))
                    <img class="brand-logo" src="{{ $logoSrc }}" alt=""><br>
                @endif
                <h1>{{ $settings->displayName() }} — Stock Valuation</h1>
                <p class="muted">Generated {{ $generatedAt->format('d M Y H:i') }}</p>
            </td>
            <td style="vertical-align:top; text-align:right; width:40%; font-size:10px; color:#555;">
                @if($settings->phone){{ $settings->phone }}<br>@endif
                @if($settings->email){{ $settings->email }}@endif
            </td>
        </tr>
    </table>
    <table class="kpis" width="100%">
        <tr>
            <td><strong>Total Stock Cost</strong><br>KES {{ number_format($summary['total_stock_cost'], 2) }}</td>
            <td><strong>Expected Gross Sales</strong><br>KES {{ number_format($summary['expected_gross_sales'], 2) }}</td>
            <td><strong>Expected Gross Profit</strong><br>KES {{ number_format($summary['expected_gross_profit'], 2) }}</td>
            <td><strong>Margin</strong><br>{{ number_format($summary['expected_profit_margin'], 2) }}%</td>
        </tr>
    </table>
    <table class="data">
        <thead>
            <tr>
                <th>Product</th><th>Qty</th><th>Buying</th><th>Selling</th>
                <th>Stock Cost</th><th>Expected Sales</th><th>Expected Profit</th><th>Status</th>
            </tr>
        </thead>
        <tbody>
            @foreach($products as $product)
                @php $m = $valuation->lineMetrics($product); @endphp
                <tr>
                    <td>{{ $product->name }}</td>
                    <td class="right">{{ $product->stock }}</td>
                    <td class="right">{{ number_format((float)($product->buying_price ?? 0), 2) }}</td>
                    <td class="right">{{ number_format((float)$product->price, 2) }}</td>
                    <td class="right">{{ number_format($m['stock_cost'], 2) }}</td>
                    <td class="right">{{ number_format($m['expected_sales'], 2) }}</td>
                    <td class="right">{{ number_format($m['expected_profit'], 2) }}</td>
                    <td>{{ $valuation->stockStatus($product) }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>
