@extends('layouts.admin')
@section('title', 'Stocktakes')
@section('heading', 'Stocktakes')
@section('subheading', 'Physical count sessions — inventory adjustments only after approval.')

@push('styles')
<style>
.stk-page { display: flex; flex-direction: column; gap: 18px; }

/* KPIs */
.stk-kpis { display: grid; grid-template-columns: repeat(4,1fr); gap: 14px; }
@media(max-width:900px) { .stk-kpis { grid-template-columns: repeat(2,1fr); } }
@media(max-width:500px) { .stk-kpis { grid-template-columns: 1fr; } }
.stk-kpi {
    background: #fff; border: 1px solid #eaecf0; border-radius: 16px;
    padding: 18px 20px; display: flex; align-items: center; gap: 16px;
    box-shadow: 0 1px 4px rgba(0,0,0,.05);
}
.stk-kpi-icon { width: 52px; height: 52px; border-radius: 12px; display: flex; align-items: center; justify-content: center; flex-shrink: 0; }
.stk-kpi-icon svg { width: 26px; height: 26px; }
.stk-kpi-icon.blue   { background: #eff6ff; color: #3b82f6; }
.stk-kpi-icon.orange { background: #fff7ed; color: #ea580c; }
.stk-kpi-icon.green  { background: #ecfdf5; color: #059669; }
.stk-kpi-icon.purple { background: #f3f0ff; color: #7c3aed; }
.stk-kpi-label { font-size: 11px; font-weight: 700; color: #6b7280; text-transform: uppercase; letter-spacing: .06em; }
.stk-kpi-value { font-size: 1.65rem; font-weight: 800; color: #111827; line-height: 1.15; margin-top: 2px; }
.stk-kpi-sub   { font-size: 12px; margin-top: 2px; }
.stk-kpi-sub.blue   { color: #3b82f6; }
.stk-kpi-sub.orange { color: #ea580c; }
.stk-kpi-sub.green  { color: #059669; }

/* New button banner */
.stk-new-bar {
    background: #fff; border: 1px solid #eaecf0; border-radius: 16px;
    padding: 16px 20px; display: flex; align-items: center; justify-content: space-between; gap: 12px;
    box-shadow: 0 1px 4px rgba(0,0,0,.04);
}
.stk-new-bar p { margin: 0; font-size: 13.5px; color: #374151; }
.stk-btn-new {
    background: linear-gradient(135deg,#d4af37,#b8942d); color: #1a1300;
    border: none; border-radius: 10px; padding: 10px 20px;
    font-size: 14px; font-weight: 800; cursor: pointer;
    text-decoration: none; display: inline-flex; align-items: center; gap: 8px;
    box-shadow: 0 4px 12px rgba(212,175,55,.3);
}
.stk-btn-new:hover { opacity: .9; }
.stk-link { font-size: 13px; color: #d4af37; font-weight: 700; text-decoration: none; display: inline-flex; align-items: center; gap: 4px; }
.stk-link:hover { text-decoration: underline; }

/* Table card */
.stk-table-card {
    background: #fff; border: 1px solid #eaecf0; border-radius: 16px;
    overflow: hidden; box-shadow: 0 1px 4px rgba(0,0,0,.04);
}
.stk-table-head {
    display: flex; align-items: center; justify-content: space-between; gap: 12px; flex-wrap: wrap;
    padding: 16px 20px; border-bottom: 1px solid #f3f4f6;
}
.stk-table-head-left { display: flex; align-items: center; gap: 10px; }
.stk-table-head-left h3 { margin: 0; font-size: 15px; font-weight: 700; color: #111827; }
.stk-search-wrap { position: relative; }
.stk-search-wrap input {
    padding: 8px 14px 8px 36px;
    border: 1px solid #d1d5db; border-radius: 10px;
    font-size: 13px; min-width: 200px; outline: none; margin: 0 !important;
}
.stk-search-wrap input:focus { border-color: #d4af37; box-shadow: 0 0 0 3px rgba(212,175,55,.12); }
.stk-search-icon { position: absolute; left: 10px; top: 50%; transform: translateY(-50%); color: #9ca3af; pointer-events: none; }
.stk-filters-btn {
    background: #fff; border: 1px solid #d1d5db; border-radius: 10px;
    padding: 8px 14px; font-size: 13px; font-weight: 700; color: #374151;
    cursor: pointer; display: inline-flex; align-items: center; gap: 6px;
}
.stk-filters-btn:hover { background: #f9fafb; }

.stk-table { width: 100%; border-collapse: collapse; }
.stk-table thead tr { background: #f9fafb; border-bottom: 1px solid #eaecf0; }
.stk-table thead th { font-size: 11px; font-weight: 700; color: #6b7280; text-transform: uppercase; letter-spacing: .06em; padding: 11px 16px; white-space: nowrap; }
.stk-table tbody tr { border-bottom: 1px solid #f3f4f6; transition: background .12s; }
.stk-table tbody tr:last-child { border-bottom: none; }
.stk-table tbody tr:hover { background: rgba(212,175,55,.04); }
.stk-table td { padding: 14px 16px; font-size: 13px; color: #374151; vertical-align: middle; }

.stk-ref { font-weight: 700; color: #111827; font-size: 13.5px; }
.stk-loc { font-size: 11px; color: #9ca3af; margin-top: 2px; display: flex; align-items: center; gap: 4px; }
.stk-date-main { font-weight: 600; color: #111827; }
.stk-date-time  { font-size: 11px; color: #9ca3af; margin-top: 2px; }

.stk-avatar { width: 32px; height: 32px; border-radius: 50%; background: #1a1300; color: #d4af37; font-size: 13px; font-weight: 800; display: inline-flex; align-items: center; justify-content: center; }
.stk-user-name { font-weight: 600; color: #111827; }
.stk-user-role { font-size: 11px; color: #9ca3af; }

.stk-variance { font-size: 12.5px; }
.stk-var-pos { color: #059669; font-weight: 700; }
.stk-var-neg { color: #dc2626; font-weight: 700; }
.stk-var-kes  { font-size: 11px; color: #9ca3af; margin-top: 2px; }

.stk-items-count { font-weight: 800; color: #111827; font-size: 15px; }
.stk-items-of    { font-size: 11px; color: #9ca3af; margin-top: 2px; }

.stk-pill { display: inline-flex; align-items: center; gap: 5px; border-radius: 999px; padding: 4px 10px; font-size: 11.5px; font-weight: 700; }
.stk-pill::before { content:''; width:7px; height:7px; border-radius:50%; background:currentColor; opacity:.6; }
.stk-pill.counting  { background: #dbeafe; color: #1d4ed8; }
.stk-pill.review    { background: #fef3c7; color: #92400e; }
.stk-pill.approved  { background: #dcfce7; color: #166534; }
.stk-pill.cancelled { background: #f3f4f6; color: #4b5563; }

.stk-btn-view {
    background: #fff; border: 1px solid #e5e7eb; border-radius: 8px;
    padding: 7px 16px; font-size: 12.5px; font-weight: 700; color: #374151;
    text-decoration: none; display: inline-flex; align-items: center; gap: 6px;
}
.stk-btn-view:hover { background: #f3f4f6; }
.stk-more {
    width: 30px; height: 30px; border-radius: 8px; border: 1px solid #e5e7eb; background: #fff;
    display: inline-flex; align-items: center; justify-content: center; cursor: pointer; color: #6b7280;
}
.stk-more:hover { background: #f3f4f6; }

/* Footer */
.stk-footer {
    padding: 12px 20px; border-top: 1px solid #f3f4f6;
    display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 10px;
}
.stk-showing { font-size: 12px; color: #6b7280; }

/* How it works */
.stk-howto {
    background: #fff; border: 1px solid #eaecf0; border-radius: 16px;
    padding: 18px 20px; display: flex; align-items: flex-start; justify-content: space-between; gap: 16px; flex-wrap: wrap;
    box-shadow: 0 1px 4px rgba(0,0,0,.04);
}
.stk-howto-left { display: flex; gap: 12px; align-items: flex-start; }
.stk-howto-icon { width: 36px; height: 36px; border-radius: 50%; background: #eff6ff; color: #3b82f6; display: flex; align-items: center; justify-content: center; flex-shrink: 0; }
.stk-howto h4 { margin: 0 0 4px; font-size: 14px; font-weight: 700; color: #111827; }
.stk-howto p  { margin: 0; font-size: 13px; color: #6b7280; max-width: 60ch; }
.stk-guide-btn {
    background: #fff; border: 1px solid #d1d5db; border-radius: 10px;
    padding: 9px 16px; font-size: 13px; font-weight: 700; color: #374151;
    text-decoration: none; display: inline-flex; align-items: center; gap: 6px; white-space: nowrap;
}
.stk-guide-btn:hover { background: #f9fafb; }
</style>
@endpush

@section('content')
<div class="stk-page">

    {{-- KPIs --}}
    <div class="stk-kpis">
        <div class="stk-kpi">
            <div class="stk-kpi-icon blue">
                <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
            </div>
            <div>
                <div class="stk-kpi-label">Open / Counting</div>
                <div class="stk-kpi-value">{{ $stats['open'] }}</div>
                <div class="stk-kpi-sub blue">Active sessions</div>
            </div>
        </div>
        <div class="stk-kpi">
            <div class="stk-kpi-icon orange">
                <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
            </div>
            <div>
                <div class="stk-kpi-label">In Review</div>
                <div class="stk-kpi-value">{{ $stats['review'] }}</div>
                <div class="stk-kpi-sub orange">Awaiting review</div>
            </div>
        </div>
        <div class="stk-kpi">
            <div class="stk-kpi-icon green">
                <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            </div>
            <div>
                <div class="stk-kpi-label">Approved</div>
                <div class="stk-kpi-value">{{ $stats['approved'] }}</div>
                <div class="stk-kpi-sub green">Completed</div>
            </div>
        </div>
        <div class="stk-kpi">
            <div class="stk-kpi-icon purple">
                <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/></svg>
            </div>
            <div>
                <div class="stk-kpi-label">This Month Net</div>
                <div class="stk-kpi-value" style="font-size:1.3rem;">KES {{ number_format($stats['month_net'], 2) }}</div>
                <div class="stk-kpi-sub" style="color:#9ca3af;">Variance value</div>
            </div>
        </div>
    </div>

    {{-- New Stocktake bar --}}
    <div class="stk-new-bar">
        <a href="{{ route('admin.stock-takes.create') }}" class="stk-btn-new">
            <svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
            New Stocktake
        </a>
        <p>Start a new physical stock count session.</p>
        <a href="#" class="stk-link">
            <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><path stroke-linecap="round" d="M12 8v4m0 4h.01"/></svg>
            Learn more
        </a>
    </div>

    {{-- Table --}}
    <div class="stk-table-card">
        <div class="stk-table-head">
            <div class="stk-table-head-left">
                <svg width="18" height="18" fill="none" stroke="#6b7280" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/></svg>
                <h3>Stocktake Sessions</h3>
            </div>
            <div style="display:flex;gap:8px;align-items:center;flex-wrap:wrap;">
                <div class="stk-search-wrap">
                    <span class="stk-search-icon"><svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="11" cy="11" r="8"/><path stroke-linecap="round" d="M21 21l-4.35-4.35"/></svg></span>
                    <input type="text" placeholder="Search stocktakes...">
                </div>
                <button class="stk-filters-btn">
                    <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2a1 1 0 01-.293.707L13 13.414V19a1 1 0 01-.553.894l-4 2A1 1 0 017 21v-7.586L3.293 6.707A1 1 0 013 6V4z"/></svg>
                    Filters
                    <svg width="12" height="12" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" d="M19 9l-7 7-7-7"/></svg>
                </button>
            </div>
        </div>

        <div style="overflow-x:auto;">
            <table class="stk-table">
                <thead>
                    <tr>
                        <th>Number</th>
                        <th>Date ↕</th>
                        <th>Status ↕</th>
                        <th>Counted By</th>
                        <th>Variance (+ / −) ↕</th>
                        <th>Items Counted ↕</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                @forelse($takes as $take)
                    @php
                        $posQty = (int) $take->items->where('variance', '>', 0)->sum('variance');
                        $negQty = abs((int) $take->items->where('variance', '<', 0)->sum('variance'));
                        $itemCount = $take->items->count();
                        $pillClass = match($take->status) {
                            'approved'  => 'approved',
                            'review'    => 'review',
                            'counting'  => 'counting',
                            'cancelled' => 'cancelled',
                            default     => 'cancelled',
                        };
                        $initials = strtoupper(substr($take->user?->name ?? 'A', 0, 1));
                    @endphp
                    <tr>
                        <td>
                            <div class="stk-ref">{{ $take->reference }}</div>
                            <div class="stk-loc">
                                <svg width="11" height="11" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                                Main Warehouse
                            </div>
                        </td>
                        <td>
                            <div class="stk-date-main">
                                <svg width="12" height="12" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" style="display:inline;vertical-align:-1px;"><rect x="3" y="4" width="18" height="18" rx="2"/><path stroke-linecap="round" d="M16 2v4M8 2v4M3 10h18"/></svg>
                                {{ ($take->stocktake_date ?? $take->created_at)?->format('d M Y') }}
                            </div>
                            <div class="stk-date-time">{{ $take->created_at->format('g:i A') }}</div>
                        </td>
                        <td>
                            <span class="stk-pill {{ $pillClass }}">{{ ucfirst($take->status) }}</span>
                            <div style="font-size:11px;color:#9ca3af;margin-top:3px;">In progress</div>
                        </td>
                        <td>
                            <div style="display:flex;align-items:center;gap:8px;">
                                <div class="stk-avatar">{{ $initials }}</div>
                                <div>
                                    <div class="stk-user-name">{{ $take->user?->name ?: 'Unknown' }}</div>
                                    <div class="stk-user-role">{{ $take->user?->roles?->first()?->name ?? 'Super Admin' }}</div>
                                </div>
                            </div>
                        </td>
                        <td>
                            <div class="stk-variance">
                                <span class="stk-var-pos">+{{ number_format($posQty) }}</span>
                                <span style="color:#9ca3af;"> / </span>
                                <span class="stk-var-neg">−{{ number_format($negQty) }}</span>
                            </div>
                            <div class="stk-var-kes">KES {{ number_format((float)($take->positive_variance_value ?? 0), 2) }}</div>
                        </td>
                        <td>
                            <div class="stk-items-count">{{ number_format($itemCount) }}</div>
                            <div class="stk-items-of">of {{ $take->items->count() }} items</div>
                        </td>
                        <td>
                            <div style="display:flex;gap:6px;align-items:center;">
                                <a href="{{ route('admin.stock-takes.show', $take) }}" class="stk-btn-view">
                                    <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                    View
                                </a>
                                <button class="stk-more">
                                    <svg width="14" height="14" fill="currentColor" viewBox="0 0 24 24"><circle cx="12" cy="5" r="1.5"/><circle cx="12" cy="12" r="1.5"/><circle cx="12" cy="19" r="1.5"/></svg>
                                </button>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" style="text-align:center;padding:48px 16px;color:#9ca3af;font-size:14px;">No stocktakes yet.</td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>

        <div class="stk-footer">
            <span class="stk-showing">Showing {{ $takes->firstItem() ?? 0 }} to {{ $takes->lastItem() ?? 0 }} of {{ $takes->total() }} stocktakes</span>
            {{ $takes->links() }}
        </div>
    </div>

    {{-- How it works --}}
    <div class="stk-howto">
        <div class="stk-howto-left">
            <div class="stk-howto-icon">
                <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><path stroke-linecap="round" d="M12 8v4m0 4h.01"/></svg>
            </div>
            <div>
                <h4>How it works</h4>
                <p>Items counted in stocktake sessions will only affect inventory after final approval.</p>
            </div>
        </div>
        <a href="#" class="stk-guide-btn">
            <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/></svg>
            View guide
        </a>
    </div>

</div>
@endsection
