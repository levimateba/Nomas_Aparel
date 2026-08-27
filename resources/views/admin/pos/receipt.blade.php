@extends('layouts.pos')
@section('title', 'Receipt ' . $order->order_number)

@php
    $brandName = $settings->site_name ?? 'Store';
    $nameParts = preg_split('/\s+/', trim($brandName)) ?: [$brandName];
    $brandTop = $nameParts[0] ?? $brandName;
    $brandGold = trim(implode(' ', array_slice($nameParts, 1)));
    $rawLogoPath = (is_object($settings) && method_exists($settings, 'getRawOriginal'))
        ? $settings->getRawOriginal('logo')
        : ($settings->logo ?? null);
    $brandLogo = \App\Support\PublicStorageUrl::fromPath($rawLogoPath) ?? ($settings->logo ?? null);
    $contacts = collect($siteContacts ?? []);
    $phone = optional($contacts->firstWhere('type', 'phone'))->value;
    $address = optional($contacts->firstWhere('type', 'address'))->value;
    $cashReceived = null;
    $cashChange = null;
    $displayNotes = trim((string) ($order->notes ?? ''));
    if (preg_match('/Cash received:\s*KES\s*([0-9,]+\.\d{2}).*Change:\s*KES\s*([0-9,]+\.\d{2})/is', $displayNotes, $cashMatch)) {
        $cashReceived = $cashMatch[1];
        $cashChange = $cashMatch[2];
        $displayNotes = trim(preg_replace('/Cash received:\s*KES\s*[0-9,]+\.\d{2}\.\s*Change:\s*KES\s*[0-9,]+\.\d{2}\.?/is', '', $displayNotes));
    }
    $paymentLabel = ucwords(str_replace('_', ' ', (string) $order->payment_method));
@endphp

@push('styles')
<style>
    @page { size: 80mm auto; margin: 0; }
    body { background: #ececec; }
    .receipt-page { padding: 20px 12px 36px; display: grid; justify-items: center; gap: 14px; }
    .receipt {
        width: 80mm; max-width: 80mm; background: #fff; color: #111;
        box-shadow: 0 10px 24px rgba(18,18,18,.12);
        font-size: 11px; line-height: 1.35; overflow: hidden;
    }
    .receipt-pad { padding: 8px 6px 6px; }
    .brand { text-align: center; }
    .brand img { width: 28px; height: 28px; object-fit: cover; border-radius: 4px; margin-bottom: 4px; }
    .brand .mark {
        width: 28px; height: 28px; margin: 0 auto 4px; background: #121212; color: #d4af37;
        display: grid; place-items: center; font-weight: 800; font-size: 14px; border-radius: 4px;
    }
    .brand strong { display: block; font-size: 12px; letter-spacing: .04em; text-transform: uppercase; }
    .brand .gold { color: #d4af37; }
    .brand .tag, .brand .contact { color: #444; font-size: 10px; }
    .rule { border: 0; border-top: 1px dashed #bbb; margin: 6px 0; }
    .title { text-align: center; font-weight: 800; letter-spacing: .12em; font-size: 11px; margin: 6px 0 4px; }
    .kv { display: flex; justify-content: space-between; gap: 8px; font-size: 10px; }
    .kv span { color: #555; }
    .kv b { text-align: right; word-break: break-all; }
    table { width: 100%; border-collapse: collapse; margin-top: 4px; table-layout: fixed; }
    th { text-align: left; font-size: 9px; letter-spacing: .06em; border-bottom: 1px solid #111; padding: 3px 0; }
    th:nth-child(1), td:nth-child(1) { width: auto; word-break: break-word; }
    th:nth-child(2), td:nth-child(2) { text-align: center; width: 22px; white-space: nowrap; }
    th:last-child, td:last-child { text-align: right; width: 62px; white-space: nowrap; font-variant-numeric: tabular-nums; }
    td { padding: 4px 0; vertical-align: top; }
    .tot { font-weight: 800; border-top: 1px dashed #111; }
    .tot td { padding-top: 6px; font-size: 12px; white-space: nowrap; }
    .meta { font-size: 10px; }
    .meta div { display: flex; justify-content: space-between; gap: 8px; margin: 2px 0; }
    .cash { border: 1px dashed #111; padding: 5px; margin-top: 6px; }
    .thanks { text-align: center; margin-top: 8px; font-size: 10px; }
    .thanks b { display: block; letter-spacing: .04em; }
    .print-hint { max-width: 80mm; color: #4b5563; font-size: 12px; text-align: center; }
    .actions { display: flex; gap: 8px; justify-content: center; flex-wrap: wrap; max-width: 420px; }
    .actions a, .actions button {
        display: inline-flex; align-items: center; gap: 6px; border: 0; border-radius: 8px;
        padding: 10px 12px; font-weight: 800; font-size: 12px; cursor: pointer; text-transform: uppercase;
    }
    .actions .gold { background: #d4af37; color: #121212; }
    .actions .dark { background: #121212; color: #fff; }
    .actions .ghost { background: #fff; color: #121212; border: 1px solid #d1d5db; }
    @media print {
        html, body { width: 80mm; background: #fff !important; margin: 0; }
        .receipt-page { padding: 0; display: block; }
        .receipt { width: 80mm; max-width: 80mm; box-shadow: none; }
        .brand .gold { color: #111; }
        .print-hint, .actions { display: none !important; }
    }
</style>
@endpush

@section('content')
<div class="receipt-page">
    <article class="receipt">
        <div class="receipt-pad">
            <header class="brand">
                @if(!empty($brandLogo))
                    <img src="{{ $brandLogo }}" alt="{{ $brandName }}">
                @else
                    <div class="mark">{{ strtoupper(substr($brandTop, 0, 1)) }}</div>
                @endif
                <strong>{{ strtoupper($brandTop) }}</strong>
                @if($brandGold !== '')
                    <strong class="gold">{{ strtoupper($brandGold) }}</strong>
                @endif
                <div class="tag">{{ $settings->site_tagline ?: 'Best Quality For You' }}</div>
                @if($phone)<div class="contact">{{ $phone }}</div>@endif
                @if($address)<div class="contact">{{ $address }}</div>@endif
            </header>

            <hr class="rule">
            <div class="title">IN-STORE RECEIPT</div>
            <div class="kv"><span>Receipt</span><b>{{ $order->order_number }}</b></div>
            <div class="kv"><span>Date</span><b>{{ $order->created_at->format('d M Y H:i') }}</b></div>
            <hr class="rule">

            <table>
                <thead>
                    <tr>
                        <th>Item</th>
                        <th>Qty</th>
                        <th>KES</th>
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
                            <td colspan="2">Discount {{ $order->coupon_code ? '(' . $order->coupon_code . ')' : '' }}</td>
                            <td>-{{ number_format((float) $order->discount_amount, 2) }}</td>
                        </tr>
                    @endif
                    <tr class="tot">
                        <td colspan="2">TOTAL</td>
                        <td>{{ number_format((float) $order->total_amount, 2) }}</td>
                    </tr>
                </tbody>
            </table>

            <hr class="rule">
            <div class="meta">
                <div><span>Customer</span><b>{{ $order->customer_name }}</b></div>
                <div><span>Payment</span><b>{{ $paymentLabel }} · Paid</b></div>
                <div><span>Cashier</span><b>{{ $order->user?->name ?? auth()->user()?->name }}</b></div>
            </div>

            @if($cashReceived !== null)
                <div class="cash">
                    <div class="kv"><span>Cash received</span><b>{{ $cashReceived }}</b></div>
                    <div class="kv"><span>Change</span><b>{{ $cashChange }}</b></div>
                </div>
            @endif

            @if($displayNotes !== '')
                <p class="thanks">{{ $displayNotes }}</p>
            @endif

            <div class="thanks">
                <b>THANK YOU FOR SHOPPING WITH US</b>
                Goods once sold are not returnable or exchangeable.
            </div>
        </div>
    </article>

    <p class="print-hint no-print">Fits 80mm thermal paper. In the print dialog choose the receipt printer, paper size 80mm, and turn off headers and footers.</p>
    <div class="actions no-print">
        <button class="gold" type="button" onclick="window.print()">Print Receipt</button>
        <a class="dark" href="{{ route('admin.pos.index') }}">New Sale</a>
        <a class="ghost" href="{{ route('admin.orders.show', $order) }}">View Order</a>
    </div>
</div>
@endsection
