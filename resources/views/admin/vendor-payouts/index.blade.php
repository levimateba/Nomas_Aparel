@extends('layouts.admin')
@section('title', 'Vendor Payouts')
@section('content')
    <h2>Vendor Payouts</h2>
        <style>
            .payouts-form-wrap { display: grid; gap: 18px; }
            .payouts-form-head h2 { margin: 0; font-size: 1.6rem; color: #121212; }
            .payouts-form-head p { margin: 8px 0 0; color: #5f5f5f; font-size: 0.95rem; }
            .payouts-form-grid { display: grid; grid-template-columns: 2fr 1fr; gap: 20px; }
            .payouts-section-card { border: 1px solid rgba(0,0,0,0.08); border-radius: 14px; padding: 16px; background: #fff; }
            .payouts-section-title { margin: 0 0 14px; font-size: 1rem; font-weight: 700; color: #1f1f1f; }
            .payouts-field { margin-bottom: 14px; }
            .payouts-label-row { display: flex; align-items: center; justify-content: space-between; margin-bottom: 6px; }
            .payouts-label-row label { margin: 0; font-weight: 600; color: #1f1f1f; }
            .payouts-help { color: #666; font-size: 0.84rem; margin-top: 5px; display: block; }
            .payouts-status { border-radius: 999px; padding: 6px 12px; font-size: 0.78rem; font-weight: 700; letter-spacing: 0.04em; text-transform: uppercase; border: 1px solid transparent; display: inline-block; }
            .payouts-status.paid { background: rgba(46,125,50,0.15); color: #1b5e20; border-color: rgba(46,125,50,0.3); }
            .payouts-status.pending { background: rgba(140,140,140,0.15); color: #5f5f5f; border-color: rgba(140,140,140,0.3); }
            .payouts-actions { display: flex; gap: 10px; margin-top: 18px; flex-wrap: wrap; }
            .btn-outline { border: 1px solid #d4af37; border-radius: 999px; color: #1f1f1f; background: #fff; padding: 9px 15px; text-decoration: none; font-weight: 600; }
            @media (max-width: 960px) { .payouts-form-grid { grid-template-columns: 1fr; } }
            .payouts-table th, .payouts-table td { padding: 8px 10px; text-align: left; }
            .payouts-table th { background: #f7f7f7; font-weight: 700; color: #222; }
            .payouts-table tr { border-bottom: 1px solid #ececec; }
            .payouts-table { width: 100%; border-collapse: collapse; margin-top: 12px; }
        </style>

        <div class="payouts-form-wrap">
            <div class="payouts-form-head">
                <h2>Vendor Payouts</h2>
                <p>Manage and review vendor payouts. Create new payouts, filter by vendor or status, and export records for accounting.</p>
            </div>

            <div class="payouts-form-grid">
                <div class="payouts-section-card">
                    <h3 class="payouts-section-title">Create payout</h3>
                    <form method="POST" action="{{ route('admin.vendor-payouts.store') }}" style="display:flex;gap:8px;flex-wrap:wrap;align-items:end;margin-top:10px;">
                        @csrf
                        <div>
                            <label>Vendor</label>
                            <select name="vendor_id" required style="min-width:180px;">
                                @foreach($vendors as $vendor)
                                    <option value="{{ $vendor->id }}" {{ (string)($filters['vendor_id'] ?? '') === (string)$vendor->id ? 'selected' : '' }}>
                                        {{ $vendor->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label>From</label>
                            <input type="date" name="from_date" required value="{{ old('from_date', $filters['from_date'] ?? '') }}">
                        </div>
                        <div>
                            <label>To</label>
                            <input type="date" name="to_date" required value="{{ old('to_date', $filters['to_date'] ?? '') }}">
                        </div>
                        <button class="btn btn-secondary" type="submit">Calculate &amp; Create</button>
                    </form>
                    <small class="payouts-help">Calculation uses summed <code>order_items.line_total</code> for the vendor between the selected dates.</small>
                </div>
                <div class="payouts-section-card">
                    <h3 class="payouts-section-title">Filter payouts</h3>
                    <form method="GET" action="{{ route('admin.vendor-payouts.index') }}" style="display:flex;gap:8px;flex-wrap:wrap;align-items:end;margin-top:10px;">
                        <select name="vendor_id" style="min-width:120px;">
                            <option value="">All vendors</option>
                            @foreach($vendors as $vendor)
                                <option value="{{ $vendor->id }}" {{ (string)($filters['vendor_id'] ?? '') === (string)$vendor->id ? 'selected' : '' }}>
                                    {{ $vendor->name }}
                                </option>
                            @endforeach
                        </select>
                        <select name="status" style="min-width:100px;">
                            <option value="">All status</option>
                            <option value="pending" {{ ($filters['status'] ?? '') === 'pending' ? 'selected' : '' }}>Pending</option>
                            <option value="paid" {{ ($filters['status'] ?? '') === 'paid' ? 'selected' : '' }}>Paid</option>
                        </select>
                        <input type="date" name="from_date" value="{{ $filters['from_date'] ?? '' }}">
                        <input type="date" name="to_date" value="{{ $filters['to_date'] ?? '' }}">
                        <button class="btn btn-secondary" type="submit">Apply</button>
                        @if(!empty($filters['vendor_id']) || !empty($filters['status']) || !empty($filters['from_date']) || !empty($filters['to_date']))
                            <a class="btn btn-secondary" href="{{ route('admin.vendor-payouts.index') }}">Clear</a>
                        @endif
                    </form>
                    <div style="margin-top:10px; display:flex; justify-content: flex-end;">
                        <a class="btn btn-secondary" href="{{ route('admin.vendor-payouts.export.csv', request()->query()) }}">Export CSV</a>
                    </div>
                </div>
            </div>

            <div class="payouts-section-card">
                <h3 class="payouts-section-title">Payouts</h3>
                <table class="payouts-table">
                    <thead>
                    <tr>
                        <th>Vendor</th>
                        <th>Period</th>
                        <th>Gross</th>
                        <th>Commission</th>
                        <th>Net</th>
                        <th>Status</th>
                        <th>Reference</th>
                        <th>Actions</th>
                    </tr>
                    </thead>
                    <tbody>
                    @forelse($payouts as $payout)
                        <tr>
                            <td>{{ $payout->vendor?->name ?? '' }}</td>
                            <td>{{ $payout->period_start->format('Y-m-d') }} - {{ $payout->period_end->format('Y-m-d') }}</td>
                            <td>KES {{ number_format((float) $payout->gross_sales, 2) }}</td>
                            <td>KES {{ number_format((float) $payout->commission_amount, 2) }}</td>
                            <td>KES {{ number_format((float) $payout->net_amount, 2) }}</td>
                            <td><span class="payouts-status {{ $payout->status }}">{{ ucfirst($payout->status) }}</span></td>
                            <td>{{ $payout->reference ?: '—' }}</td>
                            <td>
                                <a class="btn btn-secondary" href="{{ route('admin.vendor-payouts.show', $payout) }}">View</a>
                                @if($payout->status !== 'paid')
                                    <a class="btn btn-secondary" href="{{ route('admin.vendor-payouts.show', $payout) }}#mark-paid">Mark paid</a>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="8">No payouts found.</td></tr>
                    @endforelse
                    </tbody>
                </table>
                <div style="margin-top:10px;">{{ $payouts->links() }}</div>
            </div>
        </div>
    @endsection

