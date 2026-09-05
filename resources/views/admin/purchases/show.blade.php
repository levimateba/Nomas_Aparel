@extends('layouts.admin')
@section('title', $purchase->purchase_number)
@section('heading', $purchase->purchase_number)
@section('subheading', 'Purchase receipt')

@section('content')
<div class="card" style="padding:16px;margin-bottom:14px;">
    <div class="admin-form-grid">
        <div><div class="muted">Supplier</div><strong>{{ $purchase->supplier?->name ?: '—' }}</strong></div>
        <div><div class="muted">Date</div><strong>{{ $purchase->purchase_date->format('d M Y') }}</strong></div>
        <div><div class="muted">Invoice</div><strong>{{ $purchase->invoice_reference ?: '—' }}</strong></div>
        <div><div class="muted">Payment</div><span class="status-pill {{ $purchase->payment_status === 'paid' ? 'on' : 'warn' }}">{{ ucfirst($purchase->payment_status) }}</span></div>
        <div><div class="muted">Total</div><strong>KES {{ number_format((float)$purchase->total, 2) }}</strong></div>
        <div><div class="muted">Balance due</div><strong>KES {{ number_format((float)$purchase->balance_due, 2) }}</strong></div>
    </div>
</div>

<div class="card" style="padding:16px;margin-bottom:14px;">
    <h3 style="margin-top:0;">Items</h3>
    <div class="table-wrap">
        <table class="admin-table" style="width:100%;border-collapse:collapse;">
            <thead>
                <tr>
                    <th style="text-align:left;padding:8px;">Product</th>
                    <th style="text-align:right;padding:8px;">Qty</th>
                    <th style="text-align:right;padding:8px;">Cost</th>
                    <th style="text-align:right;padding:8px;">Line</th>
                </tr>
            </thead>
            <tbody>
                @foreach($purchase->items as $item)
                    <tr>
                        <td style="padding:8px;border-bottom:1px solid #f3f4f6;">{{ $item->product?->name }}</td>
                        <td style="padding:8px;border-bottom:1px solid #f3f4f6;text-align:right;">{{ $item->quantity }}</td>
                        <td style="padding:8px;border-bottom:1px solid #f3f4f6;text-align:right;">{{ number_format((float)$item->buying_price, 2) }}</td>
                        <td style="padding:8px;border-bottom:1px solid #f3f4f6;text-align:right;">{{ number_format((float)$item->total, 2) }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>

@if((float)$purchase->balance_due > 0)
<div class="card" style="padding:16px;margin-bottom:14px;">
    <h3 style="margin-top:0;">Record payment</h3>
    <form method="POST" action="{{ route('admin.purchases.payment', $purchase) }}" class="admin-form-grid">
        @csrf
        <div>
            <label>Amount *</label>
            <input type="number" step="0.01" min="0.01" max="{{ $purchase->balance_due }}" name="amount" required>
        </div>
        <div>
            <label>Method *</label>
            <select name="payment_method">
                @foreach(['Cash','M-Pesa','Bank','Card','Credit'] as $method)
                    <option value="{{ $method }}">{{ $method }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label>Reference</label>
            <input type="text" name="reference">
        </div>
        <div>
            <label>Paid at</label>
            <input type="date" name="paid_at" value="{{ now()->toDateString() }}">
        </div>
        <div style="grid-column:1/-1;">
            <button type="submit" class="btn">Save payment</button>
        </div>
    </form>
</div>
@endif

@if($purchase->payments->isNotEmpty())
<div class="card" style="padding:16px;">
    <h3 style="margin-top:0;">Payments</h3>
    @foreach($purchase->payments as $payment)
        <div style="padding:8px 0;border-bottom:1px solid #f3f4f6;display:flex;justify-content:space-between;gap:12px;">
            <span>{{ $payment->paid_at?->format('d M Y') }} · {{ $payment->payment_method }} · {{ $payment->user?->name }}</span>
            <strong>KES {{ number_format((float)$payment->amount, 2) }}</strong>
        </div>
    @endforeach
</div>
@endif
@endsection
