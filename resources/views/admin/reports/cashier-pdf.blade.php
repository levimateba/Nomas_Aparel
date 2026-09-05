<!doctype html>
<html>
<head>
    <meta charset="utf-8">
    <title>Cashier Performance</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 11px; color:#111; }
        h1 { font-size: 18px; margin: 0 0 4px; color: #a58112; }
        .muted { color: #555; margin: 0 0 12px; }
        .header { width: 100%; border-bottom: 2px solid #a58112; padding-bottom: 10px; margin-bottom: 12px; }
        .brand-logo { max-height: 52px; max-width: 140px; }
        table.data { width:100%; border-collapse: collapse; margin-top: 8px; }
        table.data th, table.data td { border:1px solid #ddd; padding:6px; text-align:left; }
        table.data th { background:#f3f4f6; font-size:10px; text-transform:uppercase; }
        .right { text-align:right; }
        .neg { color: #b91c1c; }
        .pos { color: #15803d; }
    </style>
</head>
<body>
    <table class="header" cellpadding="0" cellspacing="0">
        <tr>
            <td style="vertical-align:middle; width:60%;">
                @if(!empty($logoSrc))
                    <img class="brand-logo" src="{{ $logoSrc }}" alt=""><br>
                @endif
                <h1>{{ $settings->displayName() }} — Cashier Performance</h1>
                <p class="muted">
                    {{ \Illuminate\Support\Carbon::parse($from)->format('d M Y') }}
                    –
                    {{ \Illuminate\Support\Carbon::parse($to)->format('d M Y') }}
                    · {{ $cashierName }}
                    · Generated {{ $generatedAt->format('d M Y H:i') }}
                </p>
            </td>
            <td style="vertical-align:top; text-align:right; width:40%; font-size:10px; color:#555;">
                @if($settings->phone){{ $settings->phone }}<br>@endif
                @if($settings->email){{ $settings->email }}@endif
            </td>
        </tr>
    </table>

    <table class="data">
        <thead>
            <tr>
                <th>Cashier</th>
                <th class="right">Tickets</th>
                <th class="right">Net sales</th>
                <th class="right">Discounts</th>
                <th class="right">Returns</th>
                <th class="right">Cash</th>
                <th class="right">M-Pesa</th>
                <th class="right">Card</th>
                <th class="right">Bank</th>
                <th class="right">Shift variance</th>
            </tr>
        </thead>
        <tbody>
        @forelse($rows as $row)
            <tr>
                <td><strong>{{ $row->cashier }}</strong></td>
                <td class="right">{{ number_format($row->transactions) }}</td>
                <td class="right"><strong>{{ number_format($row->net, 2) }}</strong></td>
                <td class="right">{{ number_format($row->discounts, 2) }}</td>
                <td class="right">{{ number_format($row->returns, 2) }}</td>
                <td class="right">{{ number_format($row->cash, 2) }}</td>
                <td class="right">{{ number_format($row->mobile_money, 2) }}</td>
                <td class="right">{{ number_format($row->card, 2) }}</td>
                <td class="right">{{ number_format($row->bank, 2) }}</td>
                <td class="right {{ $row->variance < 0 ? 'neg' : ($row->variance > 0 ? 'pos' : '') }}">
                    {{ number_format($row->variance, 2) }}
                </td>
            </tr>
        @empty
            <tr><td colspan="10">No cashier activity in this period.</td></tr>
        @endforelse
        </tbody>
    </table>
</body>
</html>
