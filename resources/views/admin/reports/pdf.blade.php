<!doctype html>
<html>
<head>
    <meta charset="utf-8">
    <title>Store report</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 12px; color: #111; }
        h1 { font-size: 18px; margin: 0 0 4px; }
        .muted { color: #555; margin-bottom: 16px; }
        table { width: 100%; border-collapse: collapse; margin-top: 12px; }
        th, td { border: 1px solid #ddd; padding: 6px 8px; text-align: left; }
        th { background: #f3f4f6; }
        .kpis { width: 100%; }
        .kpis td { border: 0; padding: 4px 8px 4px 0; }
    </style>
</head>
<body>
    <h1>{{ $settings->site_name ?? 'Store' }} — Sales report</h1>
    <p class="muted">{{ \Illuminate\Support\Carbon::parse($from)->format('d M Y') }} – {{ \Illuminate\Support\Carbon::parse($to)->format('d M Y') }} · Generated {{ $generatedAt->format('d M Y H:i') }}</p>
    <table class="kpis">
        <tr>
            <td>Sales: <strong>{{ number_format($summary['count']) }}</strong></td>
            <td>Gross: <strong>KES {{ number_format($summary['gross'], 2) }}</strong></td>
            <td>Net: <strong>KES {{ number_format($summary['net'], 2) }}</strong></td>
        </tr>
        <tr>
            <td>POS: <strong>KES {{ number_format($summary['pos_revenue'], 2) }}</strong></td>
            <td>Online: <strong>KES {{ number_format($summary['online_revenue'], 2) }}</strong></td>
            <td>Returns: <strong>KES {{ number_format($summary['returns'], 2) }}</strong></td>
        </tr>
    </table>
    <h2>Daily</h2>
    <table>
        <tr><th>Date</th><th>Tickets</th><th>Net</th><th>POS</th><th>Online</th></tr>
        @foreach($daily as $row)
            <tr>
                <td>{{ $row['label'] }}</td>
                <td>{{ $row['count'] }}</td>
                <td>KES {{ number_format($row['sales'], 2) }}</td>
                <td>KES {{ number_format($row['pos'], 2) }}</td>
                <td>KES {{ number_format($row['online'], 2) }}</td>
            </tr>
        @endforeach
    </table>
    <h2>Top products</h2>
    <table>
        <tr><th>Product</th><th>Qty</th><th>Revenue</th></tr>
        @foreach($topProducts as $item)
            <tr>
                <td>{{ $item->product_name }}</td>
                <td>{{ (int) $item->quantity }}</td>
                <td>KES {{ number_format((float) $item->revenue, 2) }}</td>
            </tr>
        @endforeach
    </table>
</body>
</html>
