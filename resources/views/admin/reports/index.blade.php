@extends('layouts.admin')
@section('title', 'Reports')
@section('heading', 'Reports')
@section('subheading', 'Monitor your business performance and sales insights.')

@push('styles')
<style>
.rpt-page { display: flex; flex-direction: column; gap: 18px; }

/* ── Toolbar ──────────────────────────────────────────── */
.rpt-toolbar {
    background: #fff; border: 1px solid #eaecf0; border-radius: 16px;
    padding: 14px 18px; display: flex; flex-wrap: wrap; gap: 10px; align-items: center;
    box-shadow: 0 1px 4px rgba(0,0,0,.04);
}
.rpt-preset { display: flex; gap: 6px; flex-wrap: wrap; }
.rpt-chip {
    background: #fff; border: 1px solid #e5e7eb; border-radius: 8px;
    padding: 7px 14px; font-size: 13px; font-weight: 700; color: #374151;
    text-decoration: none; display: inline-flex; align-items: center; gap: 6px; cursor: pointer;
    transition: background .12s;
}
.rpt-chip:hover { background: #f9fafb; }
.rpt-chip.active { background: linear-gradient(135deg,#d4af37,#b8942d); border-color: #d4af37; color: #1a1300; }

.rpt-dates { display: flex; gap: 10px; align-items: flex-end; flex-wrap: wrap; }
.rpt-date-field { display: flex; flex-direction: column; gap: 4px; }
.rpt-date-field label { font-size: 10px; font-weight: 700; color: #9ca3af; text-transform: uppercase; letter-spacing: .05em; }
.rpt-date-wrap { position: relative; }
.rpt-date-wrap input {
    padding: 8px 12px 8px 32px; border: 1px solid #d1d5db; border-radius: 10px;
    font-size: 13px; outline: none; min-width: 130px; margin: 0 !important;
}
.rpt-date-wrap input:focus { border-color: #d4af37; box-shadow: 0 0 0 3px rgba(212,175,55,.12); }
.rpt-date-icon { position: absolute; left: 9px; top: 50%; transform: translateY(-50%); color: #9ca3af; pointer-events: none; }
.rpt-filter-btn {
    background: linear-gradient(135deg,#d4af37,#b8942d); color: #1a1300;
    border: none; border-radius: 10px; padding: 8px 18px;
    font-size: 13px; font-weight: 800; cursor: pointer;
    display: inline-flex; align-items: center; gap: 6px;
}
.rpt-filter-btn:hover { opacity: .92; }

.rpt-export { display: flex; gap: 8px; flex-wrap: wrap; margin-left: auto; align-items: center; }
.rpt-export-btn {
    background: #fff; border: 1px solid #e5e7eb; border-radius: 8px;
    padding: 7px 12px; font-size: 12.5px; font-weight: 700; color: #374151;
    text-decoration: none; display: inline-flex; align-items: center; gap: 6px; cursor: pointer;
}
.rpt-export-btn:hover { background: #f9fafb; }
.rpt-cashier-btn {
    background: linear-gradient(135deg,#d4af37,#b8942d); color: #1a1300;
    border: none; border-radius: 8px; padding: 7px 14px;
    font-size: 12.5px; font-weight: 800; cursor: pointer;
    text-decoration: none; display: inline-flex; align-items: center; gap: 6px;
}
.rpt-cashier-btn:hover { opacity: .92; }

/* ── KPIs ─────────────────────────────────────────────── */
.rpt-kpis { display: grid; grid-template-columns: repeat(auto-fit, minmax(140px,1fr)); gap: 10px; }
.rpt-kpi {
    background: #fff; border: 1px solid #eaecf0; border-radius: 14px;
    padding: 14px 16px; display: flex; align-items: flex-start; gap: 12px;
    box-shadow: 0 1px 4px rgba(0,0,0,.04);
}
.rpt-kpi-ico { width: 38px; height: 38px; border-radius: 10px; display: flex; align-items: center; justify-content: center; flex-shrink: 0; }
.rpt-kpi-ico svg { width: 20px; height: 20px; }
.rpt-kpi-ico.blue   { background: #eff6ff; color: #3b82f6; }
.rpt-kpi-ico.green  { background: #ecfdf5; color: #059669; }
.rpt-kpi-ico.purple { background: #f3f0ff; color: #7c3aed; }
.rpt-kpi-ico.red    { background: #fee2e2; color: #ef4444; }
.rpt-kpi-ico.orange { background: #fff7ed; color: #ea580c; }
.rpt-kpi-ico.gold   { background: #fffbeb; color: #d97706; }
.rpt-kpi-ico.pink   { background: #fdf2f8; color: #db2777; }
.rpt-kpi-ico.teal   { background: #f0fdfa; color: #0d9488; }
.rpt-kpi-label { font-size: 10.5px; font-weight: 700; color: #6b7280; text-transform: uppercase; letter-spacing: .05em; }
.rpt-kpi-value { font-size: 1.25rem; font-weight: 800; color: #111827; line-height: 1.2; margin-top: 3px; }
.rpt-kpi-sub   { font-size: 11px; color: #9ca3af; margin-top: 2px; }

/* ── 2-col split ─────────────────────────────────────── */
.rpt-split { display: grid; grid-template-columns: 1fr 1fr; gap: 16px; }
@media(max-width:900px) { .rpt-split { grid-template-columns: 1fr; } }

/* ── Cards ─────────────────────────────────────────────── */
.rpt-card {
    background: #fff; border: 1px solid #eaecf0; border-radius: 16px;
    overflow: hidden; box-shadow: 0 1px 4px rgba(0,0,0,.04);
}
.rpt-card-head {
    display: flex; align-items: center; justify-content: space-between; gap: 10px;
    padding: 14px 18px; border-bottom: 1px solid #f3f4f6; flex-wrap: wrap;
}
.rpt-card-head h3 { margin: 0; font-size: 14px; font-weight: 700; color: #111827; }
.rpt-view-all { font-size: 12.5px; color: #d4af37; font-weight: 700; text-decoration: none; }
.rpt-view-all:hover { text-decoration: underline; }
.rpt-card-body { padding: 16px 18px; }

/* View by selector */
.rpt-viewby { display: flex; align-items: center; gap: 8px; }
.rpt-viewby label { font-size: 12px; color: #6b7280; }
.rpt-viewby select { padding: 5px 8px; border: 1px solid #d1d5db; border-radius: 8px; font-size: 12px; margin: 0 !important; }

/* Table inside card */
.rpt-table { width: 100%; border-collapse: collapse; }
.rpt-table thead th { font-size: 11px; font-weight: 700; color: #6b7280; text-transform: uppercase; letter-spacing: .05em; padding: 8px 10px; border-bottom: 1px solid #f3f4f6; text-align: left; }
.rpt-table tbody td { padding: 10px 10px; font-size: 13px; border-bottom: 1px solid #f9fafb; color: #374151; }
.rpt-table tbody tr:last-child td { border-bottom: none; }
.rpt-table tbody tr:hover td { background: rgba(212,175,55,.03); }
.rpt-empty-cell { text-align: center; padding: 32px 16px !important; }

/* Empty state */
.rpt-empty {
    display: flex; flex-direction: column; align-items: center; justify-content: center;
    padding: 32px 16px; text-align: center; gap: 8px;
}
.rpt-empty h5 { margin: 0; font-size: 14px; font-weight: 700; color: #374151; }
.rpt-empty p  { margin: 0; font-size: 12px; color: #9ca3af; }

/* Payment method row */
.rpt-pay-row { display: flex; justify-content: space-between; align-items: center; padding: 10px 0; border-bottom: 1px solid #f3f4f6; }
.rpt-pay-row:last-child { border-bottom: none; }

/* Low stock row */
.rpt-stock-row { display: flex; align-items: center; gap: 10px; padding: 10px 0; border-bottom: 1px solid #f3f4f6; }
.rpt-stock-row:last-child { border-bottom: none; }
.rpt-stock-img { width: 40px; height: 40px; border-radius: 8px; object-fit: cover; border: 1px solid #e5e7eb; background: #f9fafb; flex-shrink: 0; }
.rpt-stock-count { margin-left: auto; font-weight: 700; font-size: 14px; }
.rpt-stock-count.red { color: #dc2626; }
.rpt-stock-count.orange { color: #ea580c; }
.rpt-stock-in { font-size: 11px; color: #9ca3af; }

/* Sales chart placeholder */
.rpt-chart-area { background: #f9fafb; border-radius: 12px; padding: 20px 16px; min-height: 200px; display: flex; align-items: center; justify-content: center; }
</style>
@endpush

@section('content')
<div class="rpt-page">

    {{-- ── Toolbar ──────────────────────────────────────── --}}
    <div class="rpt-toolbar">
        <div class="rpt-preset">
            <a class="rpt-chip {{ $preset === 'today'     ? 'active' : '' }}" href="{{ route('admin.reports.index', ['preset' => 'today']) }}">
                <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><rect x="3" y="4" width="18" height="18" rx="2"/><path stroke-linecap="round" d="M16 2v4M8 2v4M3 10h18"/></svg>
                Today
            </a>
            <a class="rpt-chip {{ $preset === 'yesterday' ? 'active' : '' }}" href="{{ route('admin.reports.index', ['preset' => 'yesterday']) }}">
                <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><rect x="3" y="4" width="18" height="18" rx="2"/><path stroke-linecap="round" d="M16 2v4M8 2v4M3 10h18"/></svg>
                Yesterday
            </a>
            <a class="rpt-chip {{ $preset === 'week'      ? 'active' : '' }}" href="{{ route('admin.reports.index', ['preset' => 'week']) }}">
                <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><rect x="3" y="4" width="18" height="18" rx="2"/><path stroke-linecap="round" d="M16 2v4M8 2v4M3 10h18"/></svg>
                This week
            </a>
            <a class="rpt-chip {{ $preset === 'month'     ? 'active' : '' }}" href="{{ route('admin.reports.index', ['preset' => 'month']) }}">
                <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><rect x="3" y="4" width="18" height="18" rx="2"/><path stroke-linecap="round" d="M16 2v4M8 2v4M3 10h18"/></svg>
                This month
            </a>
            <a class="rpt-chip {{ $preset === 'custom'    ? 'active' : '' }}" href="#">
                <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                Custom
            </a>
        </div>

        <form method="GET" action="{{ route('admin.reports.index') }}" style="display:contents;">
            <div class="rpt-dates">
                <div class="rpt-date-field">
                    <label>From date</label>
                    <div class="rpt-date-wrap">
                        <span class="rpt-date-icon"><svg width="12" height="12" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><rect x="3" y="4" width="18" height="18" rx="2"/><path stroke-linecap="round" d="M16 2v4M8 2v4M3 10h18"/></svg></span>
                        <input type="date" name="from" value="{{ $from }}">
                    </div>
                </div>
                <div class="rpt-date-field">
                    <label>To date</label>
                    <div class="rpt-date-wrap">
                        <span class="rpt-date-icon"><svg width="12" height="12" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><rect x="3" y="4" width="18" height="18" rx="2"/><path stroke-linecap="round" d="M16 2v4M8 2v4M3 10h18"/></svg></span>
                        <input type="date" name="to" value="{{ $to }}">
                    </div>
                </div>
                <button type="submit" class="rpt-filter-btn">
                    <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2a1 1 0 01-.293.707L13 13.414V19a1 1 0 01-.553.894l-4 2A1 1 0 017 21v-7.586L3.293 6.707A1 1 0 013 6V4z"/></svg>
                    Filter
                </button>
            </div>
        </form>

        <div class="rpt-export">
            <a class="rpt-export-btn" href="{{ route('admin.reports.export', array_merge(request()->query(), ['format'=>'pdf','from'=>$from,'to'=>$to])) }}">
                <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
                Export PDF
            </a>
            <a class="rpt-export-btn" href="{{ route('admin.reports.export', array_merge(request()->query(), ['format'=>'excel','from'=>$from,'to'=>$to])) }}">
                <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                Export Excel
            </a>
            @if(auth()->user()?->hasPermission('view_cashier_performance'))
                <a class="rpt-cashier-btn" href="{{ route('admin.reports.cashier', ['preset'=>$preset,'from'=>$from,'to'=>$to]) }}">
                    <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
                    Cashier Performance
                </a>
            @endif
        </div>
    </div>

    {{-- ── KPIs ──────────────────────────────────────────── --}}
    <div class="rpt-kpis">
        <div class="rpt-kpi">
            <div class="rpt-kpi-ico blue">
                <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
            </div>
            <div>
                <div class="rpt-kpi-label">Sales</div>
                <div class="rpt-kpi-value">{{ number_format($summary['count']) }}</div>
                <div class="rpt-kpi-sub">Paid tickets</div>
            </div>
        </div>
        <div class="rpt-kpi">
            <div class="rpt-kpi-ico green">
                <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/></svg>
            </div>
            <div>
                <div class="rpt-kpi-label">Gross Sales</div>
                <div class="rpt-kpi-value" style="font-size:1.1rem;">KES {{ number_format($summary['gross'], 2) }}</div>
            </div>
        </div>
        <div class="rpt-kpi">
            <div class="rpt-kpi-ico purple">
                <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 14l6-6m-5.5.5h.01m4.99 5h.01M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16l3.5-2 3.5 2 3.5-2 3.5 2z"/></svg>
            </div>
            <div>
                <div class="rpt-kpi-label">Net Sales</div>
                <div class="rpt-kpi-value" style="font-size:1.1rem;">KES {{ number_format($summary['net'], 2) }}</div>
                <div class="rpt-kpi-sub">After returns</div>
            </div>
        </div>
        <div class="rpt-kpi">
            <div class="rpt-kpi-ico orange">
                <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/></svg>
            </div>
            <div>
                <div class="rpt-kpi-label">Discounts</div>
                <div class="rpt-kpi-value" style="font-size:1.1rem;">KES {{ number_format($summary['discount'], 2) }}</div>
            </div>
        </div>
        <div class="rpt-kpi">
            <div class="rpt-kpi-ico red">
                <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M16 15v-1a4 4 0 00-4-4H8m0 0l3 3m-3-3l3-3m9 14V5a2 2 0 00-2-2H6a2 2 0 00-2 2v16l4-2 4 2 4-2 4 2z"/></svg>
            </div>
            <div>
                <div class="rpt-kpi-label">Returns</div>
                <div class="rpt-kpi-value" style="font-size:1.1rem;">KES {{ number_format($summary['returns'], 2) }}</div>
            </div>
        </div>
        <div class="rpt-kpi">
            <div class="rpt-kpi-ico gold">
                <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
            </div>
            <div>
                <div class="rpt-kpi-label">Average Ticket</div>
                <div class="rpt-kpi-value" style="font-size:1.1rem;">KES {{ number_format($summary['average'], 2) }}</div>
            </div>
        </div>
        <div class="rpt-kpi">
            <div class="rpt-kpi-ico pink">
                <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><rect x="2" y="3" width="20" height="14" rx="2"/><path stroke-linecap="round" d="M8 21h8m-4-4v4"/></svg>
            </div>
            <div>
                <div class="rpt-kpi-label">POS Sales</div>
                <div class="rpt-kpi-value" style="font-size:1.1rem;">KES {{ number_format($summary['pos_revenue'], 2) }}</div>
                <div class="rpt-kpi-sub">{{ $summary['pos_count'] }} sales</div>
            </div>
        </div>
        <div class="rpt-kpi">
            <div class="rpt-kpi-ico teal">
                <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><path stroke-linecap="round" stroke-linejoin="round" d="M2 12h20M12 2a15.3 15.3 0 010 20M12 2a15.3 15.3 0 000 20"/></svg>
            </div>
            <div>
                <div class="rpt-kpi-label">Online Orders</div>
                <div class="rpt-kpi-value" style="font-size:1.1rem;">KES {{ number_format($summary['online_revenue'], 2) }}</div>
                <div class="rpt-kpi-sub">{{ $summary['online_count'] }} orders</div>
            </div>
        </div>
    </div>

    {{-- ── Daily sales + Sales overview ────────────────── --}}
    <div class="rpt-split">
        <div class="rpt-card">
            <div class="rpt-card-head">
                <h3>Daily sales</h3>
                <div class="rpt-viewby">
                    <label>View by:</label>
                    <select><option>Day</option><option>Week</option><option>Month</option></select>
                </div>
            </div>
            <div style="overflow-x:auto;">
                <table class="rpt-table">
                    <thead><tr><th>Date</th><th>Tickets</th><th>Net</th><th>POS</th><th>Online</th><th>Returns</th></tr></thead>
                    <tbody>
                    @forelse($daily as $row)
                        <tr>
                            <td>{{ $row['label'] }}</td>
                            <td>{{ $row['count'] }}</td>
                            <td><strong>KES {{ number_format($row['sales'], 2) }}</strong></td>
                            <td>KES {{ number_format($row['pos'], 2) }}</td>
                            <td>KES {{ number_format($row['online'], 2) }}</td>
                            <td>KES {{ number_format($row['returns'], 2) }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="rpt-empty-cell" style="color:#9ca3af;font-size:13px;">No activity in this period.</td></tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        <div class="rpt-card">
            <div class="rpt-card-head">
                <h3>Sales overview</h3>
                <select style="padding:5px 8px;border:1px solid #d1d5db;border-radius:8px;font-size:12px;margin:0;"><option>Last 7 days</option><option>Last 30 days</option></select>
            </div>
            <div class="rpt-card-body">
                <div class="rpt-chart-area">
                    <div style="text-align:center;color:#9ca3af;">
                        <svg width="40" height="40" fill="none" stroke="#d1d5db" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
                        <div style="font-size:12px;margin-top:8px;">Chart visualization</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- ── Payment methods + Low stock ─────────────────── --}}
    <div class="rpt-split">
        <div class="rpt-card">
            <div class="rpt-card-head"><h3>Payment methods</h3></div>
            <div class="rpt-card-body">
                @forelse($paymentBreakdown as $row)
                    <div class="rpt-pay-row">
                        <span style="font-weight:600;color:#374151;">{{ ucwords(str_replace('_',' ', $row->payment_method ?: 'Other')) }} <span style="color:#9ca3af;font-size:11px;">{{ $row->count }}</span></span>
                        <strong>KES {{ number_format((float)$row->total, 2) }}</strong>
                    </div>
                @empty
                    <div class="rpt-empty">
                        <div style="color:#d1d5db;"><svg width="40" height="40" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><path stroke-linecap="round" d="M12 8v4m0 4h.01"/></svg></div>
                        <p>No payments recorded.</p>
                    </div>
                @endforelse
            </div>
        </div>
        <div class="rpt-card">
            <div class="rpt-card-head">
                <h3>Low stock</h3>
                <a href="{{ route('admin.products.index', ['stock'=>'low']) }}" class="rpt-view-all">View all</a>
            </div>
            <div class="rpt-card-body">
                @forelse($lowStock as $product)
                    <div class="rpt-stock-row">
                        <img src="{{ $product->image_url ?: 'https://via.placeholder.com/40?text=?' }}" alt="{{ $product->name }}" class="rpt-stock-img">
                        <div>
                            <div style="font-weight:700;font-size:13px;color:#111827;">{{ $product->name }}</div>
                            <div style="font-size:11px;color:#9ca3af;">{{ $product->category?->name }}</div>
                        </div>
                        <div style="margin-left:auto;text-align:right;">
                            <div class="rpt-stock-count {{ $product->stock <= 0 ? 'red' : 'orange' }}">{{ $product->stock }}</div>
                            <div class="rpt-stock-in">In stock</div>
                        </div>
                    </div>
                @empty
                    <div class="rpt-empty"><p>Stock levels look healthy.</p></div>
                @endforelse
            </div>
        </div>
    </div>

    {{-- ── Top products + Category sales ───────────────── --}}
    <div class="rpt-split">
        <div class="rpt-card">
            <div class="rpt-card-head">
                <h3>Top products</h3>
                <a href="#" class="rpt-view-all">View all</a>
            </div>
            <div style="overflow-x:auto;">
                <table class="rpt-table">
                    <thead><tr><th>Product</th><th>Qty Sold</th><th>Revenue</th></tr></thead>
                    <tbody>
                    @forelse($topProducts as $item)
                        <tr>
                            <td>{{ $item->product_name }}</td>
                            <td>{{ (int)$item->quantity }}</td>
                            <td>KES {{ number_format((float)$item->revenue, 2) }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="3">
                                <div class="rpt-empty">
                                    <svg width="32" height="32" fill="none" stroke="#d1d5db" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                                    <h5>No product sales.</h5>
                                    <p>There is no product sales data for the selected period.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        <div class="rpt-card">
            <div class="rpt-card-head">
                <h3>Sales by category</h3>
                <a href="#" class="rpt-view-all">View all</a>
            </div>
            <div style="overflow-x:auto;">
                <table class="rpt-table">
                    <thead><tr><th>Category</th><th>Qty Sold</th><th>Revenue</th></tr></thead>
                    <tbody>
                    @forelse($categorySales as $row)
                        <tr>
                            <td>{{ $row->category_name }}</td>
                            <td>{{ (int)$row->quantity }}</td>
                            <td>KES {{ number_format((float)$row->revenue, 2) }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="3">
                                <div class="rpt-empty">
                                    <svg width="32" height="32" fill="none" stroke="#d1d5db" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z"/></svg>
                                    <h5>No category sales.</h5>
                                    <p>There is no category sales data for the selected period.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- ── Recent sales ─────────────────────────────────── --}}
    <div class="rpt-card">
        <div class="rpt-card-head">
            <h3>Recent sales</h3>
            <a href="{{ route('admin.orders.index') }}" class="rpt-view-all">Sales history</a>
        </div>
        <div style="overflow-x:auto;">
            <table class="rpt-table">
                <thead><tr><th>Ticket</th><th>Customer</th><th>Source</th><th>Total</th><th>Status</th></tr></thead>
                <tbody>
                @forelse($recentOrders as $order)
                    <tr>
                        <td><a href="{{ route('admin.orders.show', $order) }}" style="font-weight:700;color:#111827;text-decoration:none;">{{ $order->order_number }}</a></td>
                        <td>{{ $order->customer_name }}</td>
                        <td>{{ $order->isPos() ? 'POS' : 'Online' }}</td>
                        <td><strong>KES {{ number_format((float)$order->total_amount, 2) }}</strong></td>
                        <td>{{ ucwords(str_replace('_',' ',$order->status)) }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5">
                            <div class="rpt-empty">
                                <svg width="36" height="36" fill="none" stroke="#d1d5db" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2"/></svg>
                                <h5>No recent sales.</h5>
                                <p>There is no sales data for the selected period.</p>
                            </div>
                        </td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- ── Vendor payouts + Commissions ─────────────────── --}}
    <div class="rpt-split">
        <div class="rpt-card">
            <div class="rpt-card-head"><h3>Vendor payouts</h3></div>
            <div class="rpt-card-body">
                <div style="display:flex;justify-content:space-between;padding:8px 0;border-bottom:1px solid #f3f4f6;">
                    <span style="color:#6b7280;font-size:13px;">Pending</span>
                    <strong>KES {{ number_format((float)$payoutTotals['pending']['net_amount'], 2) }} <span style="color:#9ca3af;font-weight:400;">({{ $payoutTotals['pending']['count'] }})</span></strong>
                </div>
                <div style="display:flex;justify-content:space-between;padding:8px 0;">
                    <span style="color:#6b7280;font-size:13px;">Paid</span>
                    <strong>KES {{ number_format((float)$payoutTotals['paid']['net_amount'], 2) }} <span style="color:#9ca3af;font-weight:400;">({{ $payoutTotals['paid']['count'] }})</span></strong>
                </div>
            </div>
        </div>
        <div class="rpt-card">
            <div class="rpt-card-head">
                <h3>Vendor commissions</h3>
                <a href="{{ route('admin.reports.vendor-commissions.csv', ['from'=>$from,'to'=>$to,'preset'=>$preset]) }}" class="rpt-view-all">Export CSV</a>
            </div>
            <div style="overflow-x:auto;">
                <table class="rpt-table">
                    <thead><tr><th>Vendor</th><th>Gross</th><th>Commission</th></tr></thead>
                    <tbody>
                    @forelse($vendorCommissions as $vendor)
                        <tr>
                            <td>{{ $vendor->name }}</td>
                            <td>KES {{ number_format((float)$vendor->gross_sales, 2) }}</td>
                            <td>KES {{ number_format((float)$vendor->commission_amount, 2) }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="3" style="text-align:center;padding:24px;color:#9ca3af;font-size:13px;">No vendor sales.</td></tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

</div>
@endsection
