@extends('layouts.admin')
@section('title', 'Payouts')
@section('heading', 'Payouts')
@section('subheading', 'Calculate vendor earnings and mark payouts as paid.')

@push('styles')
<style>
.pay-page { display: flex; flex-direction: column; gap: 18px; }

/* KPI cards */
.pay-kpis { display: grid; grid-template-columns: 1fr 1fr; gap: 14px; }
@media(max-width:600px) { .pay-kpis { grid-template-columns: 1fr; } }
.pay-kpi {
    background: #fff; border: 1px solid #eaecf0; border-radius: 16px;
    padding: 20px 24px; display: flex; align-items: center; gap: 16px;
    box-shadow: 0 1px 4px rgba(0,0,0,.05);
}
.pay-kpi-icon { width: 52px; height: 52px; border-radius: 12px; display: flex; align-items: center; justify-content: center; flex-shrink: 0; }
.pay-kpi-icon svg { width: 26px; height: 26px; }
.pay-kpi-icon.orange { background: #fff7ed; color: #ea580c; }
.pay-kpi-icon.green  { background: #ecfdf5; color: #059669; }
.pay-kpi-label { font-size: 11px; font-weight: 700; color: #6b7280; text-transform: uppercase; letter-spacing: .06em; }
.pay-kpi-value { font-size: 1.7rem; font-weight: 800; color: #111827; line-height: 1.2; margin-top: 2px; }
.pay-kpi-sub   { font-size: 12px; color: #9ca3af; margin-top: 2px; }

/* Filter panel */
.pay-filter-card {
    background: #fff; border: 1px solid #eaecf0; border-radius: 16px;
    overflow: hidden; box-shadow: 0 1px 4px rgba(0,0,0,.04);
}
.pay-filter-head { padding: 14px 20px; border-bottom: 1px solid #f3f4f6; font-size: 14px; font-weight: 700; color: #111827; }
.pay-filter-body { padding: 16px 20px; display: flex; flex-wrap: wrap; gap: 14px; align-items: flex-end; }
.pay-field { display: flex; flex-direction: column; gap: 5px; min-width: 140px; flex: 1; }
.pay-field label { font-size: 11px; font-weight: 700; color: #6b7280; text-transform: uppercase; letter-spacing: .05em; }
.pay-field input,
.pay-field select {
    padding: 9px 12px; border: 1px solid #d1d5db; border-radius: 10px;
    font-size: 13px; color: #111827; background: #fff; outline: none;
    width: 100%; margin: 0 !important;
}
.pay-field input:focus,
.pay-field select:focus { border-color: #d4af37; box-shadow: 0 0 0 3px rgba(212,175,55,.12); }
.pay-input-icon { position: relative; }
.pay-input-icon input { padding-left: 34px; }
.pay-input-icon span { position: absolute; left: 10px; top: 50%; transform: translateY(-50%); color: #9ca3af; pointer-events: none; }

/* Select with icon */
.pay-sel-wrap { position: relative; }
.pay-sel-wrap .psi { position: absolute; left: 10px; top: 50%; transform: translateY(-50%); color: #9ca3af; pointer-events: none; }
.pay-sel-wrap select { padding-left: 34px !important; }

.pay-btn-apply {
    background: linear-gradient(135deg,#d4af37,#b8942d); color: #1a1300;
    border: none; border-radius: 10px; padding: 9px 18px;
    font-size: 13px; font-weight: 800; cursor: pointer;
    white-space: nowrap; display: inline-flex; align-items: center; gap: 6px;
}
.pay-btn-apply:hover { opacity: .92; }
.pay-btn-outline {
    background: #fff; color: #374151;
    border: 1px solid #d1d5db; border-radius: 10px; padding: 9px 14px;
    font-size: 13px; font-weight: 700; cursor: pointer;
    text-decoration: none; white-space: nowrap; display: inline-flex; align-items: center; gap: 6px;
}
.pay-btn-outline:hover { background: #f9fafb; }

/* Create payout card */
.pay-create-card {
    background: #fff; border: 1px solid #eaecf0; border-radius: 16px;
    padding: 18px 20px; box-shadow: 0 1px 4px rgba(0,0,0,.04);
    display: flex; flex-wrap: wrap; gap: 16px; align-items: flex-end;
}
.pay-create-intro { display: flex; gap: 12px; align-items: flex-start; flex: 0 0 auto; max-width: 220px; }
.pay-create-icon { width: 40px; height: 40px; border-radius: 10px; background: #fffbeb; color: #d97706; display: flex; align-items: center; justify-content: center; flex-shrink: 0; }
.pay-create-intro h4 { margin: 0 0 3px; font-size: 14px; font-weight: 700; color: #111827; }
.pay-create-intro p  { margin: 0; font-size: 12px; color: #6b7280; }

/* Table card */
.pay-table-card {
    background: #fff; border: 1px solid #eaecf0; border-radius: 16px;
    overflow: hidden; box-shadow: 0 1px 4px rgba(0,0,0,.04);
}
.pay-table-head {
    display: flex; align-items: center; justify-content: space-between; gap: 12px;
    padding: 16px 20px; border-bottom: 1px solid #f3f4f6; flex-wrap: wrap;
}
.pay-table-head-left { display: flex; align-items: center; gap: 10px; }
.pay-table-head-left h3 { margin: 0; font-size: 15px; font-weight: 700; color: #111827; }
.pay-search { position: relative; }
.pay-search input {
    padding: 8px 14px 8px 34px; border: 1px solid #d1d5db; border-radius: 10px;
    font-size: 13px; min-width: 200px; outline: none; margin: 0 !important;
}
.pay-search input:focus { border-color: #d4af37; box-shadow: 0 0 0 3px rgba(212,175,55,.12); }
.pay-search-icon { position: absolute; left: 10px; top: 50%; transform: translateY(-50%); color: #9ca3af; pointer-events: none; }

.pay-table { width: 100%; border-collapse: collapse; }
.pay-table thead tr { background: #f9fafb; border-bottom: 1px solid #eaecf0; }
.pay-table thead th { font-size: 11px; font-weight: 700; color: #6b7280; text-transform: uppercase; letter-spacing: .06em; padding: 11px 16px; white-space: nowrap; }
.pay-table tbody tr { border-bottom: 1px solid #f3f4f6; transition: background .12s; }
.pay-table tbody tr:last-child { border-bottom: none; }
.pay-table tbody tr:hover { background: rgba(212,175,55,.04); }
.pay-table td { padding: 14px 16px; font-size: 13px; color: #374151; vertical-align: middle; }

/* Empty state */
.pay-empty {
    display: flex; flex-direction: column; align-items: center; justify-content: center;
    padding: 52px 24px; text-align: center;
}
.pay-empty-icon { margin-bottom: 12px; }
.pay-empty h4   { margin: 0 0 6px; font-size: 15px; font-weight: 700; color: #374151; }
.pay-empty p    { margin: 0; font-size: 13px; color: #9ca3af; }

.pay-pill { display: inline-flex; border-radius: 999px; padding: 4px 10px; font-size: 11.5px; font-weight: 700; }
.pay-pill.paid    { background: #dcfce7; color: #166534; }
.pay-pill.pending { background: #fef3c7; color: #92400e; }

.pay-table-footer {
    padding: 12px 20px; border-top: 1px solid #f3f4f6;
    display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 10px;
}
.pay-showing { font-size: 12px; color: #6b7280; }

.sort-ico { opacity: .5; font-size: 10px; margin-left: 3px; }
</style>
@endpush

@section('content')
<div class="pay-page">

    {{-- KPIs --}}
    <div class="pay-kpis">
        <div class="pay-kpi">
            <div class="pay-kpi-icon orange">
                <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
            </div>
            <div>
                <div class="pay-kpi-label">Pending Payouts</div>
                <div class="pay-kpi-value">{{ $stats['pending'] ?? 0 }}</div>
                <div class="pay-kpi-sub">Awaiting payment</div>
            </div>
        </div>
        <div class="pay-kpi">
            <div class="pay-kpi-icon green">
                <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            </div>
            <div>
                <div class="pay-kpi-label">Paid Payouts</div>
                <div class="pay-kpi-value">{{ $stats['paid'] ?? 0 }}</div>
                <div class="pay-kpi-sub">Completed</div>
            </div>
        </div>
    </div>

    {{-- Filter panel --}}
    <div class="pay-filter-card">
        <div class="pay-filter-head">Filter Payouts</div>
        <form method="GET" action="{{ route('admin.vendor-payouts.index') }}">
            <div class="pay-filter-body">
                <div class="pay-field">
                    <label>Vendor</label>
                    <div class="pay-sel-wrap">
                        <span class="psi"><svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5"/></svg></span>
                        <select name="vendor_id">
                            <option value="">All vendors</option>
                            @foreach($vendors as $vendor)
                                <option value="{{ $vendor->id }}" @selected((string)($filters['vendor_id'] ?? '') === (string)$vendor->id)>{{ $vendor->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="pay-field" style="max-width:160px;">
                    <label>Status</label>
                    <select name="status">
                        <option value="">All</option>
                        <option value="pending" @selected(($filters['status'] ?? '') === 'pending')>Pending</option>
                        <option value="paid"    @selected(($filters['status'] ?? '') === 'paid')>Paid</option>
                    </select>
                </div>
                <div class="pay-field" style="max-width:180px;">
                    <label>From Date</label>
                    <div class="pay-input-icon">
                        <span><svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><rect x="3" y="4" width="18" height="18" rx="2"/><path stroke-linecap="round" d="M16 2v4M8 2v4M3 10h18"/></svg></span>
                        <input type="date" name="from_date" value="{{ $filters['from_date'] ?? now()->toDateString() }}">
                    </div>
                </div>
                <div class="pay-field" style="max-width:180px;">
                    <label>To Date</label>
                    <div class="pay-input-icon">
                        <span><svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><rect x="3" y="4" width="18" height="18" rx="2"/><path stroke-linecap="round" d="M16 2v4M8 2v4M3 10h18"/></svg></span>
                        <input type="date" name="to_date" value="{{ $filters['to_date'] ?? now()->toDateString() }}">
                    </div>
                </div>
                <div style="display:flex;gap:8px;align-items:flex-end;">
                    <button type="submit" class="pay-btn-apply">
                        <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2a1 1 0 01-.293.707L13 13.414V19a1 1 0 01-.553.894l-4 2A1 1 0 017 21v-7.586L3.293 6.707A1 1 0 013 6V4z"/></svg>
                        Apply Filters
                    </button>
                    <a href="{{ route('admin.vendor-payouts.export.csv', request()->query()) }}" class="pay-btn-outline">
                        <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                        Export CSV
                    </a>
                </div>
            </div>
        </form>
    </div>

    {{-- Create payout --}}
    <div class="pay-create-card">
        <div class="pay-create-intro">
            <div class="pay-create-icon">
                <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            </div>
            <div>
                <h4>Create Payout</h4>
                <p>Calculate vendor earnings for a specific period.</p>
            </div>
        </div>
        <form method="POST" action="{{ route('admin.vendor-payouts.store') }}" style="display:contents;">
            @csrf
            <div class="pay-field">
                <label>Vendor</label>
                <div class="pay-sel-wrap">
                    <span class="psi"><svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5"/></svg></span>
                    <select name="vendor_id" required>
                        @foreach($vendors as $vendor)
                            <option value="{{ $vendor->id }}">{{ $vendor->name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="pay-field" style="max-width:180px;">
                <label>From Date</label>
                <div class="pay-input-icon">
                    <span><svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><rect x="3" y="4" width="18" height="18" rx="2"/><path stroke-linecap="round" d="M16 2v4M8 2v4M3 10h18"/></svg></span>
                    <input type="date" name="from_date" required value="{{ old('from_date', now()->toDateString()) }}">
                </div>
            </div>
            <div class="pay-field" style="max-width:180px;">
                <label>To Date</label>
                <div class="pay-input-icon">
                    <span><svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><rect x="3" y="4" width="18" height="18" rx="2"/><path stroke-linecap="round" d="M16 2v4M8 2v4M3 10h18"/></svg></span>
                    <input type="date" name="to_date" required value="{{ old('to_date', now()->toDateString()) }}">
                </div>
            </div>
            <div style="align-self:flex-end;">
                <button type="submit" class="pay-btn-apply">
                    <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
                    Calculate &amp; Create
                </button>
            </div>
        </form>
    </div>

    {{-- Payouts list --}}
    <div class="pay-table-card">
        <div class="pay-table-head">
            <div class="pay-table-head-left">
                <svg width="18" height="18" fill="none" stroke="#6b7280" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2"/></svg>
                <h3>Payouts List</h3>
            </div>
            <div class="pay-search">
                <span class="pay-search-icon"><svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="11" cy="11" r="8"/><path stroke-linecap="round" d="M21 21l-4.35-4.35"/></svg></span>
                <input type="text" placeholder="Search payouts...">
            </div>
        </div>

        <div style="overflow-x:auto;">
            <table class="pay-table">
                <thead>
                    <tr>
                        <th>Vendor <span class="sort-ico">↕</span></th>
                        <th>Period <span class="sort-ico">↕</span></th>
                        <th>Gross <span class="sort-ico">↕</span></th>
                        <th>Commission <span class="sort-ico">↕</span></th>
                        <th>Net <span class="sort-ico">↕</span></th>
                        <th>Status <span class="sort-ico">↕</span></th>
                        <th>Reference <span class="sort-ico">↕</span></th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                @forelse($payouts as $payout)
                    <tr>
                        <td><strong>{{ $payout->vendor?->name ?? '' }}</strong></td>
                        <td>{{ $payout->period_start->format('d M Y') }} – {{ $payout->period_end->format('d M Y') }}</td>
                        <td>KES {{ number_format((float)$payout->gross_sales, 2) }}</td>
                        <td>KES {{ number_format((float)$payout->commission_amount, 2) }}</td>
                        <td><strong>KES {{ number_format((float)$payout->net_amount, 2) }}</strong></td>
                        <td><span class="pay-pill {{ $payout->status === 'paid' ? 'paid' : 'pending' }}">{{ ucfirst($payout->status) }}</span></td>
                        <td>{{ $payout->reference ?: '—' }}</td>
                        <td>
                            <div style="display:flex;gap:6px;">
                                <a href="{{ route('admin.vendor-payouts.show', $payout) }}" class="pay-btn-outline" style="padding:6px 12px;font-size:12px;">View</a>
                                @if($payout->status !== 'paid')
                                    <a href="{{ route('admin.vendor-payouts.show', $payout) }}#mark-paid" class="pay-btn-outline" style="padding:6px 12px;font-size:12px;">Mark paid</a>
                                @endif
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8">
                            <div class="pay-empty">
                                <div class="pay-empty-icon">
                                    <svg width="52" height="52" fill="none" stroke="#d1d5db" stroke-width="1.2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                </div>
                                <h4>No payouts found</h4>
                                <p>Try adjusting your filters or create a new payout.</p>
                            </div>
                        </td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>

        <div class="pay-table-footer">
            <span class="pay-showing">Showing {{ $payouts->firstItem() ?? 0 }} to {{ $payouts->lastItem() ?? 0 }} of {{ $payouts->total() }} payouts</span>
            {{ $payouts->links() }}
        </div>
    </div>

</div>
@endsection
