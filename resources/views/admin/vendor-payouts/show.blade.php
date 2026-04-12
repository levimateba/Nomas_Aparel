
@extends('layouts.admin')
@section('title', 'Vendor Payout')
@section('content')
    <style>
        .payout-show-wrap { display: grid; gap: 18px; }
        .payout-show-head h2 { margin: 0; font-size: 1.6rem; color: #121212; }
        .payout-show-head p { margin: 8px 0 0; color: #5f5f5f; font-size: 0.95rem; }
        .payout-show-grid { display: grid; grid-template-columns: repeat(auto-fit,minmax(180px,1fr)); gap: 16px; margin-top: 10px; }
        .payout-show-card { border: 1px solid rgba(0,0,0,0.08); border-radius: 14px; padding: 16px; background: #fff; }
        .payout-show-label { font-weight: 700; color: #1f1f1f; font-size: 1rem; }
        .payout-show-value { color: #222; font-size: 1.05rem; margin-top: 4px; }
        .payouts-status { border-radius: 999px; padding: 6px 12px; font-size: 0.78rem; font-weight: 700; letter-spacing: 0.04em; text-transform: uppercase; border: 1px solid transparent; display: inline-block; }
        .payouts-status.paid { background: rgba(46,125,50,0.15); color: #1b5e20; border-color: rgba(46,125,50,0.3); }
        .payouts-status.pending { background: rgba(140,140,140,0.15); color: #5f5f5f; border-color: rgba(140,140,140,0.3); }
        .payouts-help { color: #666; font-size: 0.84rem; margin-top: 5px; display: block; }
        .payouts-actions { display: flex; gap: 10px; margin-top: 18px; flex-wrap: wrap; }
        .btn-outline { border: 1px solid #d4af37; border-radius: 999px; color: #1f1f1f; background: #fff; padding: 9px 15px; text-decoration: none; font-weight: 600; }
    </style>

    <div class="payout-show-wrap">
        <div class="payout-show-head">
            <h2>Vendor Payout</h2>
            <p>Review payout details, status, and payment information for this vendor period.</p>
        </div>

        <div class="payout-show-card">
            <h3 style="margin-top:0;">{{ $payout->vendor?->name ?? 'Vendor' }}</h3>
            <div class="payout-show-grid">
                <div>
                    <div class="payout-show-label">Period</div>
                    <div class="payout-show-value">{{ $payout->period_start->format('Y-m-d') }} - {{ $payout->period_end->format('Y-m-d') }}</div>
                </div>
                <div>
                    <div class="payout-show-label">Gross Sales</div>
                    <div class="payout-show-value">KES {{ number_format((float) $payout->gross_sales, 2) }}</div>
                </div>
                <div>
                    <div class="payout-show-label">Commission</div>
                    <div class="payout-show-value">KES {{ number_format((float) $payout->commission_amount, 2) }}</div>
                </div>
                <div>
                    <div class="payout-show-label">Net Amount</div>
                    <div class="payout-show-value">KES {{ number_format((float) $payout->net_amount, 2) }}</div>
                </div>
                <div>
                    <div class="payout-show-label">Status</div>
                    <div class="payout-show-value"><span class="payouts-status {{ $payout->status }}">{{ ucfirst($payout->status) }}</span></div>
                </div>
            </div>
            <div style="margin-top:14px;">
                <strong>Reference:</strong> {{ $payout->reference ?: '—' }}<br>
                <strong>Paid at:</strong> {{ $payout->paid_at ? $payout->paid_at->format('Y-m-d H:i') : '—' }}
            </div>
            @if(!empty($payout->notes))
                <div style="margin-top:14px;">
                    <strong>Notes</strong>
                    <p style="margin:8px 0 0; white-space: pre-wrap;">{{ $payout->notes }}</p>
                </div>
            @endif
        </div>

        @if($payout->status !== 'paid')
            <div class="payout-show-card" id="mark-paid">
                <h3 style="margin-top:0;">Mark as paid</h3>
                <form method="POST" action="{{ route('admin.vendor-payouts.mark-paid', $payout) }}" style="display:flex;gap:8px;flex-wrap:wrap;align-items:end;margin-top:10px;">
                    @csrf
                    <div style="min-width:180px;">
                        <label>Payment reference</label>
                        <input type="text" name="reference" required value="{{ old('reference') }}">
                    </div>
                    <div>
                        <label>Paid at</label>
                        <input type="date" name="paid_at" value="{{ old('paid_at') }}">
                    </div>
                    <div style="min-width:220px;">
                        <label>Notes (optional)</label>
                        <input type="text" name="notes" value="{{ old('notes', $payout->notes) }}">
                    </div>
                    <button class="btn btn-secondary" type="submit">Confirm paid</button>
                </form>
            </div>
        @endif

        <div class="payouts-actions">
            <a class="btn-outline" href="{{ route('admin.vendor-payouts.index') }}">Back to list</a>
        </div>
    </div>
@endsection

