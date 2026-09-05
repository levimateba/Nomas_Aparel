@extends('layouts.pos')
@section('title', 'Receipt ' . $order->order_number)

@php
    $loyaltySettings = $loyaltySettings ?? \App\Models\LoyaltySetting::current();
    $brandName = $settings->displayName();
    $receiptHeader = trim((string) ($settings->receipt_header ?: $brandName));
    $receiptFooter = trim((string) ($settings->receipt_footer ?: 'Thank you for shopping with us. Goods once sold are not returnable without receipt.'));
    $phone = $settings->phone;
    $address = collect([
        $settings->address,
        $settings->city,
    ])->filter()->implode(', ');
    $taxPin = $settings->tax_pin;
    $regNo = $settings->business_registration_number;
    $showLogo = $settings->showsLogoOnReceipts();
    $brandLogo = $showLogo ? $settings->logo : null;
    $escpos = (bool) ($settings->escpos_enabled ?? true);
    $copies = \App\Support\ReceiptPrintMode::copiesFor($settings->receipt_print_mode);
    $autoPrint = (bool) session('pos_auto_print');
    $openDrawer = (bool) session('pos_open_drawer');

    $cashReceived = null;
    $cashChange = null;
    $displayNotes = trim((string) ($order->notes ?? ''));
    if (preg_match('/Cash received:\s*KES\s*([0-9,]+\.\d{2}).*Change:\s*KES\s*([0-9,]+\.\d{2})/is', $displayNotes, $cashMatch)) {
        $cashReceived = $cashMatch[1];
        $cashChange = $cashMatch[2];
        $displayNotes = trim(preg_replace('/Cash received:\s*KES\s*[0-9,]+\.\d{2}\.\s*Change:\s*KES\s*[0-9,]+\.\d{2}\.?/is', '', $displayNotes));
    }
    $paymentLabel = ucwords(str_replace('_', ' ', (string) $order->payment_method));
    $receiptPdfUrl = route('admin.pos.receipt.pdf', $order);
@endphp

@push('styles')
<style>
    @page { size: {{ $escpos ? '80mm auto' : 'A4' }}; margin: {{ $escpos ? '0' : '12mm' }}; }
    body { background: #ececec; }
    .receipt-page { padding: 20px 12px 36px; display: grid; justify-items: center; gap: 14px; }
    .receipt {
        width: {{ $escpos ? '80mm' : 'min(720px, 100%)' }}; max-width: 100%; background: #fff; color: #111;
        box-shadow: 0 10px 24px rgba(18,18,18,.12);
        font-size: {{ $escpos ? '11px' : '14px' }}; line-height: 1.35; overflow: hidden;
        page-break-after: always;
    }
    .receipt:last-of-type { page-break-after: auto; }
    .receipt-pad { padding: 8px 6px 6px; }
    .brand { text-align: center; }
    .brand img { width: 28px; height: 28px; object-fit: cover; border-radius: 4px; margin-bottom: 4px; }
    .brand strong { display: block; font-size: 12px; letter-spacing: .04em; text-transform: uppercase; white-space: pre-line; }
    .brand .contact { color: #444; font-size: 10px; }
    .copy-badge { text-align:center; font-size:10px; font-weight:800; letter-spacing:.08em; margin:4px 0; color:#555; }
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
    .thanks { text-align: center; margin-top: 8px; font-size: 10px; white-space: pre-line; }
    .print-hint { max-width: 420px; color: #4b5563; font-size: 12px; text-align: center; }
    .actions {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 8px;
        width: min(420px, 100%);
        max-width: 100%;
        padding: 0 4px;
        box-sizing: border-box;
    }
    .actions a, .actions button {
        display: inline-flex; align-items: center; justify-content: center; gap: 6px;
        border: 0; border-radius: 10px; padding: 12px 10px; font-weight: 800; font-size: 12px;
        cursor: pointer; text-transform: uppercase; text-decoration: none; width: 100%;
        font-family: inherit; box-sizing: border-box;
    }
    .actions .gold { background: #d4af37; color: #121212; }
    .actions .dark { background: #121212; color: #fff; }
    .actions .ghost { background: #fff; color: #121212; border: 1px solid #d1d5db; }
    .actions .share { background: #1877f2; color: #fff; }
    .share-note { max-width: 420px; width: 100%; color: #6b7280; font-size: 12px; text-align: center; }
    @media print {
        html, body { width: {{ $escpos ? '80mm' : 'auto' }}; background: #fff !important; margin: 0; }
        .receipt-page { padding: 0; display: block; }
        .receipt { width: {{ $escpos ? '80mm' : '100%' }}; max-width: {{ $escpos ? '80mm' : '100%' }}; box-shadow: none; }
        .print-hint, .actions, .share-note, .pos-top, .alert { display: none !important; }
    }
</style>
@endpush

@section('content')
<div class="receipt-page">
    @foreach($copies as $copy)
        <article class="receipt">
            <div class="receipt-pad">
                <header class="brand">
                    @if($showLogo && !empty($brandLogo))
                        <img src="{{ $brandLogo }}" alt="{{ $brandName }}">
                    @endif
                    <strong>{{ $receiptHeader }}</strong>
                    @if($phone)<div class="contact">{{ $phone }}</div>@endif
                    @if($address)<div class="contact">{{ $address }}</div>@endif
                    @if($taxPin)<div class="contact">PIN: {{ $taxPin }}</div>@endif
                    @if($regNo)<div class="contact">Reg: {{ $regNo }}</div>@endif
                </header>

                <div class="copy-badge">{{ strtoupper($copy) }} COPY</div>
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

                <div class="meta" style="margin-top:8px;">
                    <div><span>Customer</span><b>{{ $order->customer_name ?: 'Walk-in' }}</b></div>
                    <div><span>Payment</span><b>{{ $paymentLabel }}</b></div>
                </div>

                @php
                    $showLoyaltyReceipt = ($loyaltySettings->enabled ?? false)
                        && ($loyaltySettings->show_on_receipt ?? false)
                        && ($order->shop_customer_id || (int) ($order->loyalty_points_earned ?? 0) > 0 || (int) ($order->loyalty_points_redeemed ?? 0) > 0);
                    $receiptCard = $order->shopCustomer?->loyaltyCard;
                    $balanceAfter = $receiptCard?->points_balance;
                @endphp
                @if($showLoyaltyReceipt)
                    <hr class="rule">
                    <div class="meta">
                        @if($receiptCard)
                            <div><span>Loyalty Card</span><b>{{ $receiptCard->card_number }}</b></div>
                        @endif
                        @if((int) ($order->loyalty_points_earned ?? 0) > 0)
                            <div><span>Points Earned</span><b>+{{ (int) $order->loyalty_points_earned }}</b></div>
                        @endif
                        @if((int) ($order->loyalty_points_redeemed ?? 0) > 0)
                            <div><span>Points Redeemed</span><b>{{ (int) $order->loyalty_points_redeemed }}</b></div>
                            <div><span>Loyalty Discount</span><b>KES {{ number_format((float) ($order->loyalty_discount_amount ?? 0), 2) }}</b></div>
                        @endif
                        @if($balanceAfter !== null)
                            <div><span>Available Points</span><b>{{ number_format($balanceAfter) }}</b></div>
                        @endif
                    </div>
                @endif

                @if($cashReceived !== null)
                    <div class="cash">
                        <div class="kv"><span>Cash received</span><b>KES {{ $cashReceived }}</b></div>
                        <div class="kv"><span>Change</span><b>KES {{ $cashChange }}</b></div>
                    </div>
                @endif

                <div class="thanks">{{ $receiptFooter }}</div>
            </div>
        </article>
    @endforeach

    <p class="print-hint no-print">{{ $escpos ? 'Fits 80mm thermal paper. In the print dialog choose the receipt printer, paper size 80mm, and turn off headers and footers.' : 'A4 / PDF receipt layout is enabled in settings.' }}</p>
    <p class="share-note no-print">Share the receipt PDF via WhatsApp, email, or any app on your device.</p>
    <div class="actions no-print">
        <button class="gold" type="button" onclick="window.print()">Print Receipt</button>
        <button class="share" type="button" id="receipt-share-btn">Share Receipt</button>
        <a class="dark" href="{{ route('admin.pos.index') }}">New Sale</a>
        <a class="ghost" href="{{ route('admin.orders.show', $order) }}">View Order</a>
    </div>
</div>
<iframe id="drawer-frame" title="Cash drawer" style="position:absolute;width:0;height:0;border:0;visibility:hidden;"></iframe>
<script>
(function () {
    var pdfUrl = @json($receiptPdfUrl);
    var title = @json($brandName . ' Receipt ' . $order->order_number);
    var fileName = @json('receipt-' . $order->order_number . '.pdf');
    var btn = document.getElementById('receipt-share-btn');

    function setBusy(busy) {
        if (!btn) return;
        btn.disabled = !!busy;
        btn.textContent = busy ? 'Preparing…' : 'Share Receipt';
    }

    async function getReceiptFile() {
        var res = await fetch(pdfUrl, { credentials: 'same-origin', headers: { 'Accept': 'application/pdf' } });
        if (!res.ok) {
            throw new Error('Could not load receipt PDF');
        }
        var blob = await res.blob();
        return new File([blob], fileName, { type: 'application/pdf' });
    }

    if (btn) {
        btn.addEventListener('click', async function () {
            setBusy(true);
            try {
                var file = await getReceiptFile();
                if (navigator.canShare && navigator.canShare({ files: [file] })) {
                    await navigator.share({ files: [file], title: title, text: title });
                    return;
                }
                if (navigator.share) {
                    await navigator.share({ title: title, text: title, url: pdfUrl });
                    return;
                }
                var link = document.createElement('a');
                link.href = pdfUrl + (pdfUrl.indexOf('?') >= 0 ? '&' : '?') + 'download=1';
                link.download = fileName;
                document.body.appendChild(link);
                link.click();
                link.remove();
                btn.textContent = 'Downloaded';
                setTimeout(function () { btn.textContent = 'Share Receipt'; }, 1600);
            } catch (err) {
                if (err && err.name === 'AbortError') {
                    return;
                }
                window.open(pdfUrl, '_blank');
            } finally {
                setBusy(false);
            }
        });
    }

    function openDrawer() {
        var frame = document.getElementById('drawer-frame');
        if (!frame) return;
        var doc = frame.contentWindow.document;
        doc.open();
        doc.write('<html><body><pre style="font-size:1px;color:#fff;">\x1B\x70\x00\x19\xFA</pre><script>window.onload=function(){window.print();}<\/script></body></html>');
        doc.close();
    }

    @if($openDrawer)
    openDrawer();
    @endif
    @if($autoPrint)
    setTimeout(function () { window.print(); }, 450);
    @endif
})();
</script>
@endsection
