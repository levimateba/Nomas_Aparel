@extends('layouts.admin')
@section('title', 'Sales History')
@section('heading', 'Sales History')
@section('subheading', 'POS tickets and online orders in one desk.')

@push('styles')
<style>
.ord-page { display: flex; flex-direction: column; gap: 18px; }

/* KPIs */
.ord-kpis { display: grid; grid-template-columns: repeat(3,1fr); gap: 14px; }
@media(max-width:700px) { .ord-kpis { grid-template-columns: 1fr; } }
.ord-kpi {
    background: #fff; border: 1px solid #eaecf0; border-radius: 16px;
    padding: 18px 20px; display: flex; align-items: center; gap: 14px;
    box-shadow: 0 1px 4px rgba(0,0,0,.05);
}
.ord-kpi-icon { width: 46px; height: 46px; border-radius: 12px; display: flex; align-items: center; justify-content: center; flex-shrink: 0; }
.ord-kpi-icon svg { width: 22px; height: 22px; }
.ord-kpi-icon.blue   { background: #eff6ff; color: #3b82f6; }
.ord-kpi-icon.green  { background: #ecfdf5; color: #059669; }
.ord-kpi-icon.purple { background: #f3f0ff; color: #7c3aed; }
.ord-kpi-label { font-size: 11px; font-weight: 700; color: #6b7280; text-transform: uppercase; letter-spacing: .06em; }
.ord-kpi-value { font-size: 1.5rem; font-weight: 800; color: #111827; margin-top: 2px; }
.ord-kpi-sub   { font-size: 12px; color: #9ca3af; margin-top: 2px; }

/* Toolbar */
.ord-toolbar {
    background: #fff; border: 1px solid #eaecf0; border-radius: 16px;
    padding: 14px 18px; box-shadow: 0 1px 4px rgba(0,0,0,.04);
    display: flex; flex-wrap: wrap; gap: 12px; align-items: flex-end;
}
.ord-field { display: flex; flex-direction: column; gap: 5px; min-width: 130px; flex: 1; }
.ord-field label { font-size: 11px; font-weight: 700; color: #6b7280; text-transform: uppercase; letter-spacing: .05em; }
.ord-field input,
.ord-field select {
    padding: 9px 12px; border: 1px solid #d1d5db; border-radius: 10px;
    font-size: 13px; color: #111827; background: #fff; outline: none; margin: 0 !important;
}
.ord-field input:focus,
.ord-field select:focus { border-color: #d4af37; box-shadow: 0 0 0 3px rgba(212,175,55,.12); }
.ord-search-wrap { position: relative; }
.ord-search-wrap input { padding-left: 34px; }
.ord-search-icon { position: absolute; left: 10px; top: 50%; transform: translateY(-50%); color: #9ca3af; pointer-events: none; }
.ord-btn-apply {
    background: linear-gradient(135deg,#d4af37,#b8942d); color: #1a1300;
    border: none; border-radius: 10px; padding: 9px 18px;
    font-size: 13px; font-weight: 800; cursor: pointer;
    display: inline-flex; align-items: center; gap: 6px;
}
.ord-btn-apply:hover { opacity: .92; }
.ord-btn-outline {
    background: #fff; color: #374151; border: 1px solid #d1d5db; border-radius: 10px; padding: 9px 14px;
    font-size: 13px; font-weight: 700; cursor: pointer; text-decoration: none;
    display: inline-flex; align-items: center; gap: 6px;
}
.ord-btn-outline:hover { background: #f9fafb; }

/* Table card */
.ord-card {
    background: #fff; border: 1px solid #eaecf0; border-radius: 16px;
    overflow: hidden; box-shadow: 0 1px 4px rgba(0,0,0,.04);
}
.ord-bulk {
    display: flex; gap: 10px; align-items: center; flex-wrap: wrap;
    padding: 14px 18px; border-bottom: 1px solid #f3f4f6;
}
.ord-bulk select { padding: 8px 12px; border: 1px solid #d1d5db; border-radius: 10px; font-size: 13px; min-width: 220px; margin: 0 !important; }

.ord-table { width: 100%; border-collapse: collapse; }
.ord-table thead tr { background: #f9fafb; border-bottom: 1px solid #eaecf0; }
.ord-table thead th { font-size: 11px; font-weight: 700; color: #6b7280; text-transform: uppercase; letter-spacing: .06em; padding: 11px 14px; white-space: nowrap; text-align: left; }
.ord-table tbody tr { border-bottom: 1px solid #f3f4f6; transition: background .12s; }
.ord-table tbody tr:last-child { border-bottom: none; }
.ord-table tbody tr:hover { background: rgba(212,175,55,.04); }
.ord-table td { padding: 12px 14px; font-size: 13px; color: #374151; vertical-align: middle; }

.ord-num  { font-weight: 700; color: #111827; }
.ord-name { font-weight: 600; color: #111827; }
.ord-email { font-size: 11px; color: #9ca3af; margin-top: 2px; }

.ord-pill { display: inline-flex; border-radius: 999px; padding: 3px 10px; font-size: 11.5px; font-weight: 700; }
.ord-pill.paid, .ord-pill.delivered, .ord-pill.completed { background: #dcfce7; color: #166534; }
.ord-pill.processing, .ord-pill.shipped { background: #dbeafe; color: #1d4ed8; }
.ord-pill.cancelled { background: #fee2e2; color: #991b1b; }
.ord-pill.pending, .ord-pill.cancellation_requested { background: #fef3c7; color: #92400e; }
.ord-pill.pos { background: #faf6ea; color: #b8942d; border: 1px solid #e8d99a; }
.ord-pill.online { background: #f3f4f6; color: #4b5563; }

.ord-view-btn {
    background: #fff; border: 1px solid #e5e7eb; border-radius: 8px;
    padding: 6px 14px; font-size: 12.5px; font-weight: 700; color: #374151;
    text-decoration: none; display: inline-flex; align-items: center; gap: 5px;
}
.ord-view-btn:hover { background: #f3f4f6; }

.ord-footer {
    padding: 12px 18px; border-top: 1px solid #f3f4f6;
    display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 10px;
}
.ord-showing { font-size: 12px; color: #6b7280; }
</style>
@endpush

@section('content')
<div class="ord-page">

    {{-- KPIs --}}
    <div class="ord-kpis">
        <div class="ord-kpi">
            <div class="ord-kpi-icon blue">
                <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
            </div>
            <div>
                <div class="ord-kpi-label">Today</div>
                <div class="ord-kpi-value">{{ $summaries['today']['count'] }}</div>
                <div class="ord-kpi-sub">KES {{ number_format($summaries['today']['revenue'], 2) }}</div>
            </div>
        </div>
        <div class="ord-kpi">
            <div class="ord-kpi-icon green">
                <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
            </div>
            <div>
                <div class="ord-kpi-label">This Week</div>
                <div class="ord-kpi-value">{{ $summaries['week']['count'] }}</div>
                <div class="ord-kpi-sub">KES {{ number_format($summaries['week']['revenue'], 2) }}</div>
            </div>
        </div>
        <div class="ord-kpi">
            <div class="ord-kpi-icon purple">
                <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/></svg>
            </div>
            <div>
                <div class="ord-kpi-label">This Month</div>
                <div class="ord-kpi-value">{{ $summaries['month']['count'] }}</div>
                <div class="ord-kpi-sub">KES {{ number_format($summaries['month']['revenue'], 2) }}</div>
            </div>
        </div>
    </div>

    {{-- Filters --}}
    <form method="GET" action="{{ route('admin.orders.index') }}">
        <div class="ord-toolbar">
            <div class="ord-field" style="max-width:260px;">
                <label>Search</label>
                <div class="ord-search-wrap">
                    <span class="ord-search-icon"><svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="11" cy="11" r="8"/><path stroke-linecap="round" d="M21 21l-4.35-4.35"/></svg></span>
                    <input type="text" name="q" value="{{ request('q') }}" placeholder="Order # / customer">
                </div>
            </div>
            <div class="ord-field" style="max-width:170px;">
                <label>Status</label>
                <select name="status">
                    <option value="">All Status</option>
                    @foreach(['pending','processing','paid','shipped','delivered','cancellation_requested','cancelled'] as $s)
                        <option value="{{ $s }}" @selected(request('status') === $s)>{{ ucwords(str_replace('_',' ',$s)) }}</option>
                    @endforeach
                </select>
            </div>
            <div class="ord-field" style="max-width:170px;">
                <label>Payment</label>
                <select name="payment_method">
                    <option value="">All Methods</option>
                    @foreach(['cash','cash_on_delivery','mobile_money','bank_transfer','card'] as $m)
                        <option value="{{ $m }}" @selected(request('payment_method') === $m)>{{ ucwords(str_replace('_',' ',$m)) }}</option>
                    @endforeach
                </select>
            </div>
            <div class="ord-field" style="max-width:140px;">
                <label>Source</label>
                <select name="source">
                    <option value="">All</option>
                    <option value="pos" @selected(request('source') === 'pos')>In-store POS</option>
                    <option value="online" @selected(request('source') === 'online')>Online</option>
                </select>
            </div>
            <div class="ord-field" style="max-width:160px;">
                <label>From</label>
                <input type="date" name="from_date" value="{{ request('from_date') }}">
            </div>
            <div class="ord-field" style="max-width:160px;">
                <label>To</label>
                <input type="date" name="to_date" value="{{ request('to_date') }}">
            </div>
            <div style="display:flex;gap:8px;align-items:flex-end;">
                <button type="submit" class="ord-btn-apply">
                    <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2a1 1 0 01-.293.707L13 13.414V19a1 1 0 01-.553.894l-4 2A1 1 0 017 21v-7.586L3.293 6.707A1 1 0 013 6V4z"/></svg>
                    Apply
                </button>
                @if(request()->anyFilled(['q','status','payment_method','source','from_date','to_date']))
                    <a href="{{ route('admin.orders.index') }}" class="ord-btn-outline">Clear</a>
                @endif
                <a href="{{ route('admin.orders.export.csv', request()->query()) }}" class="ord-btn-outline">
                    <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                    Export CSV
                </a>
            </div>
        </div>
    </form>

    {{-- Table --}}
    <div class="ord-card">
        <form method="POST" action="{{ route('admin.orders.bulk-update') }}">
            @csrf
            <div class="ord-bulk">
                <select name="status" required>
                    <option value="">Set status for selected</option>
                    @foreach(['pending','processing','paid','shipped','delivered','cancellation_requested','cancelled'] as $s)
                        <option value="{{ $s }}">{{ ucwords(str_replace('_',' ',$s)) }}</option>
                    @endforeach
                </select>
                <button type="submit" class="ord-btn-outline" onclick="return confirm('Update selected orders?')">Apply to selected</button>
            </div>

            <div style="overflow-x:auto;">
                <table class="ord-table">
                    <thead>
                        <tr>
                            <th style="width:40px;padding-left:18px;"><input type="checkbox" id="select-all-orders" style="width:16px;height:16px;accent-color:#d4af37;cursor:pointer;"></th>
                            <th>Order ID</th>
                            <th>Customer</th>
                            <th>Cashier</th>
                            <th>Total</th>
                            <th>Payment</th>
                            <th>Source</th>
                            <th>Status</th>
                            <th>Date</th>
                            <th style="text-align:right;padding-right:18px;">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                    @forelse($orders as $order)
                        @php
                            $pillClass = match($order->status) {
                                'paid','delivered','completed' => 'paid',
                                'processing','shipped' => 'processing',
                                'cancelled' => 'cancelled',
                                default => 'pending',
                            };
                            $isPos = ($order->source ?? null) === 'pos' || str_starts_with((string)$order->order_number, 'POS-');
                        @endphp
                        <tr>
                            <td style="padding-left:18px;">
                                <input type="checkbox" name="order_ids[]" value="{{ $order->id }}" class="order-check" style="width:16px;height:16px;accent-color:#d4af37;cursor:pointer;">
                            </td>
                            <td><span class="ord-num">{{ $order->order_number }}</span></td>
                            <td>
                                <div class="ord-name">{{ $order->customer_name }}</div>
                                <div class="ord-email">{{ $order->customer_email }}</div>
                            </td>
                            <td style="color:#6b7280;">{{ $order->user?->name ?: '—' }}</td>
                            <td><strong>KES {{ number_format((float)$order->total_amount, 2) }}</strong></td>
                            <td style="color:#6b7280;">{{ ucwords(str_replace('_',' ',$order->payment_method)) }}</td>
                            <td>
                                @if($isPos)
                                    <span class="ord-pill pos">POS</span>
                                @else
                                    <span class="ord-pill online">Online</span>
                                @endif
                            </td>
                            <td><span class="ord-pill {{ $pillClass }}">{{ ucwords(str_replace('_',' ',$order->status)) }}</span></td>
                            <td style="color:#9ca3af;font-size:12px;">{{ $order->created_at->format('d M Y H:i') }}</td>
                            <td style="text-align:right;padding-right:18px;">
                                <a href="{{ route('admin.orders.show', $order) }}" class="ord-view-btn">
                                    <svg width="12" height="12" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                    View
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="10" style="text-align:center;padding:48px;color:#9ca3af;font-size:14px;">No sales found.</td></tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </form>

        <div class="ord-footer">
            <span class="ord-showing">Showing {{ $orders->firstItem() ?? 0 }} to {{ $orders->lastItem() ?? 0 }} of {{ $orders->total() }} orders</span>
            {{ $orders->links() }}
        </div>
    </div>

</div>
<script>
document.getElementById('select-all-orders')?.addEventListener('change', function () {
    document.querySelectorAll('.order-check').forEach(el => el.checked = this.checked);
});
</script>
@endsection
