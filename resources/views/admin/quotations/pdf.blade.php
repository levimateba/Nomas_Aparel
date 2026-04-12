<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>{{ $quotation->quotation_number }}</title>
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
        .footer { margin-top: 16px; border-top: 1px solid #e5e7eb; padding-top: 8px; font-size: 10px; color: #6b7280; }
    </style>
</head>
<body>
@php
    $logoDataUri = null;

    if (!empty($settings->logo)) {
        $logo = (string) $settings->logo;

        if (str_starts_with($logo, 'data:image')) {
            $logoDataUri = $logo;
        } else {
            $logoPath = null;

            if (str_starts_with($logo, 'http://') || str_starts_with($logo, 'https://')) {
                $parsedPath = parse_url($logo, PHP_URL_PATH);
                if (!empty($parsedPath)) {
                    $logoPath = public_path(ltrim((string) $parsedPath, '/'));
                }
            } elseif (str_starts_with($logo, '/storage/')) {
                $logoPath = public_path(ltrim($logo, '/'));
            } elseif (str_starts_with($logo, 'storage/')) {
                $logoPath = public_path($logo);
            } else {
                $logoPath = public_path(ltrim($logo, '/'));
            }

            if (!empty($logoPath) && is_file($logoPath) && is_readable($logoPath)) {
                $mime = mime_content_type($logoPath) ?: 'image/png';
                $logoDataUri = 'data:' . $mime . ';base64,' . base64_encode(file_get_contents($logoPath));
            }
        }
    }
@endphp
<div class="page">
    <table class="top" cellpadding="0" cellspacing="0">
        <tr>
            <td>
                @if(!empty($logoDataUri))
                    <img src="{{ $logoDataUri }}" alt="Logo" style="height: 42px; margin-bottom: 6px;">
                @endif
                <div class="brand">{{ $settings->site_name ?? 'Elgon Tech' }}</div>
                <div class="muted">{{ $settings->site_tagline ?? 'ICT Consultancy' }}</div>
            </td>
            <td class="right">
                <div class="title">ICT Consultancy Quotation</div>
                <div class="muted">{{ $quotation->quotation_number }}</div>
                <div class="muted">Generated: {{ $generatedAt->format('M d, Y H:i') }}</div>
            </td>
        </tr>
    </table>

    <div class="block">
        <div class="block-head">Client Details</div>
        <div class="block-body">
            <strong>{{ $quotation->client_name }}</strong><br>
            {{ $quotation->client_email }}<br>
            {{ $quotation->client_phone ?: 'N/A' }}<br>
            {{ $quotation->client_address ?: 'N/A' }}
        </div>
    </div>

    <div class="block">
        <div class="block-head">Project Overview</div>
        <div class="block-body">
            <strong>{{ $quotation->project_title }}</strong><br><br>
            {{ $quotation->description ?: 'N/A' }}<br><br>
            <strong>Scope of Work:</strong><br>
            {{ $quotation->scope_of_work ?: 'N/A' }}<br><br>
            <strong>Deliverables:</strong><br>
            {{ $quotation->deliverables ?: 'N/A' }}<br><br>
            <strong>Timeline:</strong> {{ $quotation->timeline ?: 'N/A' }}
        </div>
    </div>

    <table class="items">
        <thead>
            <tr>
                <th style="width:35%;">Item</th>
                <th>Description</th>
                <th style="width:10%;">Qty</th>
                <th style="width:15%;">Unit Price</th>
                <th style="width:15%;">Total</th>
            </tr>
        </thead>
        <tbody>
        @foreach($quotation->items as $item)
            <tr>
                <td>{{ $item->item_name }}</td>
                <td>{{ $item->description ?: 'N/A' }}</td>
                <td class="right">{{ number_format((float) $item->quantity, 2) }}</td>
                <td class="right">${{ number_format((float) $item->unit_price, 2) }}</td>
                <td class="right">${{ number_format((float) $item->total_price, 2) }}</td>
            </tr>
        @endforeach
        </tbody>
    </table>

    <table class="totals">
        <tr>
            <td class="label">Subtotal</td>
            <td class="right">${{ number_format((float) $quotation->subtotal, 2) }}</td>
        </tr>
        <tr>
            <td class="label">Tax</td>
            <td class="right">${{ number_format((float) $quotation->tax, 2) }}</td>
        </tr>
        <tr>
            <td class="label final">Total Amount</td>
            <td class="right final">${{ number_format((float) $quotation->total_amount, 2) }}</td>
        </tr>
    </table>

    <div class="footer">
        {{ $settings->footer_text ?: 'Thank you for choosing us.' }}
    </div>
</div>
</body>
</html>
