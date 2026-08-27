@extends('layouts.admin')
@section('title', 'Dashboard')
@section('heading', 'Dashboard')
@section('subheading', 'Welcome back, ' . (auth()->user()?->name ?? 'Admin') . ". Here's what's happening today.")

@push('styles')
<style>
.dash-page { display: flex; flex-direction: column; gap: 20px; }

/* Quick actions */
.dash-actions {
    display: flex; flex-wrap: wrap; gap: 10px; align-items: center;
}
.dash-btn {
    background: linear-gradient(135deg,#d4af37,#b8942d); color: #1a1300;
    border: none; border-radius: 10px; padding: 9px 16px;
    font-size: 13px; font-weight: 800; cursor: pointer;
    text-decoration: none; display: inline-flex; align-items: center; gap: 7px;
    box-shadow: 0 3px 10px rgba(212,175,55,.25);
}
.dash-btn:hover { opacity: .9; }
.dash-btn-outline {
    background: #fff; color: #374151;
    border: 1px solid #d1d5db; border-radius: 10px; padding: 9px 16px;
    font-size: 13px; font-weight: 700; cursor: pointer;
    text-decoration: none; display: inline-flex; align-items: center; gap: 7px;
}
.dash-btn-outline:hover { background: #f9fafb; }

/* KPI grid */
.dash-kpis { display: grid; grid-template-columns: repeat(4,1fr); gap: 14px; }
@media(max-width:1100px) { .dash-kpis { grid-template-columns: repeat(2,1fr); } }
@media(max-width:560px)  { .dash-kpis { grid-template-columns: 1fr; } }

.dash-kpi {
    background: #fff; border: 1px solid #eaecf0; border-radius: 16px;
    padding: 18px 20px; display: flex; align-items: flex-start; gap: 14px;
    text-decoration: none; color: inherit;
    box-shadow: 0 1px 4px rgba(0,0,0,.05);
    transition: box-shadow .15s, transform .15s;
}
.dash-kpi:hover { box-shadow: 0 4px 14px rgba(0,0,0,.1); transform: translateY(-1px); }
.dash-kpi-icon { width: 46px; height: 46px; border-radius: 12px; display: flex; align-items: center; justify-content: center; flex-shrink: 0; }
.dash-kpi-icon svg { width: 22px; height: 22px; }
.dash-kpi-icon.purple { background: #f3f0ff; color: #7c3aed; }
.dash-kpi-icon.red    { background: #fee2e2; color: #dc2626; }
.dash-kpi-icon.orange { background: #fff7ed; color: #ea580c; }
.dash-kpi-icon.green  { background: #ecfdf5; color: #059669; }
.dash-kpi-icon.blue   { background: #eff6ff; color: #3b82f6; }
.dash-kpi-icon.teal   { background: #f0fdfa; color: #0d9488; }
.dash-kpi-icon.gold   { background: #fffbeb; color: #d97706; }
.dash-kpi-icon.pink   { background: #fdf2f8; color: #db2777; }
.dash-kpi-body { flex: 1; min-width: 0; }
.dash-kpi-label { font-size: 11px; font-weight: 700; color: #6b7280; text-transform: uppercase; letter-spacing: .06em; }
.dash-kpi-value { font-size: 1.6rem; font-weight: 800; color: #111827; line-height: 1.2; margin-top: 4px; }
.dash-kpi-note  { font-size: 12px; color: #9ca3af; margin-top: 3px; }
.dash-kpi-trend { font-size: 12px; font-weight: 700; margin-top: 6px; display: inline-flex; align-items: center; gap: 3px; }
.dash-kpi-trend.up   { color: #059669; }
.dash-kpi-trend.down { color: #dc2626; }

/* Main body grid */
.dash-body { display: grid; grid-template-columns: 1.6fr 1fr; gap: 16px; }
@media(max-width:1000px) { .dash-body { grid-template-columns: 1fr; } }

/* Cards */
.dash-card {
    background: #fff; border: 1px solid #eaecf0; border-radius: 16px;
    overflow: hidden; box-shadow: 0 1px 4px rgba(0,0,0,.04);
}
.dash-card-head {
    display: flex; align-items: center; justify-content: space-between; gap: 10px;
    padding: 16px 20px; border-bottom: 1px solid #f3f4f6; flex-wrap: wrap;
}
.dash-card-head h3 { margin: 0; font-size: 15px; font-weight: 700; color: #111827; }
.dash-link { font-size: 13px; font-weight: 700; color: #d4af37; text-decoration: none; display: inline-flex; align-items: center; gap: 4px; }
.dash-link:hover { text-decoration: underline; }
.dash-card-body { padding: 16px 20px; }

/* Orders table */
.dash-table { width: 100%; border-collapse: collapse; }
.dash-table thead th { font-size: 11px; font-weight: 700; color: #6b7280; text-transform: uppercase; letter-spacing: .05em; padding: 8px 10px; border-bottom: 1px solid #f3f4f6; text-align: left; }
.dash-table tbody td { padding: 12px 10px; font-size: 13px; color: #374151; border-bottom: 1px solid #f9fafb; }
.dash-table tbody tr:last-child td { border-bottom: none; }
.dash-table tbody tr:hover td { background: rgba(212,175,55,.03); }

/* Status pills */
.dpill { display: inline-flex; border-radius: 999px; padding: 3px 9px; font-size: 11.5px; font-weight: 700; }
.dpill.pending, .dpill.processing { background: #fef3c7; color: #92400e; }
.dpill.paid, .dpill.delivered, .dpill.completed { background: #dcfce7; color: #166534; }
.dpill.shipped { background: #dbeafe; color: #1d4ed8; }
.dpill.cancelled { background: #fee2e2; color: #991b1b; }
.dpill.pos { background: #faf6ea; color: #b8942d; }

/* Low stock items */
.dash-stock-list { display: flex; flex-direction: column; gap: 8px; }
.dash-stock-item {
    display: flex; align-items: center; justify-content: space-between; gap: 10px;
    padding: 10px 12px; background: #fafafa; border: 1px solid #f3f4f6;
    border-radius: 10px; text-decoration: none; color: inherit;
    transition: background .12s;
}
.dash-stock-item:hover { background: #f3f4f6; }
.dash-stock-name { font-weight: 700; font-size: 13px; color: #111827; }
.dash-stock-cat  { font-size: 11px; color: #9ca3af; margin-top: 2px; }
.dash-stock-qty  { font-weight: 800; font-size: 14px; color: #dc2626; }
.dash-stock-lbl  { font-size: 10px; color: #9ca3af; text-align: right; }

/* All stocked */
.dash-all-good {
    text-align: center; padding: 32px 16px; color: #059669;
}
.dash-all-good-icon {
    width: 52px; height: 52px; border-radius: 50%; background: #dcfce7; color: #059669;
    display: flex; align-items: center; justify-content: center; margin: 0 auto 12px;
}
.dash-all-good h4 { margin: 0 0 4px; font-size: 14px; font-weight: 700; }
.dash-all-good p  { margin: 0; font-size: 12px; color: #9ca3af; }

/* View action btn in table */
.dash-view-btn {
    background: #fff; border: 1px solid #e5e7eb; border-radius: 7px;
    padding: 5px 12px; font-size: 12px; font-weight: 700; color: #374151;
    text-decoration: none; display: inline-flex; align-items: center; gap: 5px;
}
.dash-view-btn:hover { background: #f3f4f6; }

/* Today summary banner */
.dash-today {
    background: linear-gradient(135deg,#1a1300 0%,#2d2100 100%);
    border-radius: 16px; padding: 20px 24px;
    display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 16px;
    box-shadow: 0 4px 16px rgba(0,0,0,.18);
}
.dash-today-text h3 { margin: 0 0 4px; font-size: 16px; font-weight: 800; color: #d4af37; }
.dash-today-text p  { margin: 0; font-size: 13px; color: #a09060; }
.dash-today-stats { display: flex; gap: 28px; flex-wrap: wrap; }
.dash-today-stat { text-align: center; }
.dash-today-stat-val { font-size: 1.5rem; font-weight: 800; color: #fff; }
.dash-today-stat-lbl { font-size: 11px; color: #a09060; font-weight: 600; margin-top: 2px; }
</style>
@endpush

@section('content')
<div class="dash-page">

    {{-- Quick actions --}}
    <div class="dash-actions">
        <a href="{{ route('admin.pos.index') }}" class="dash-btn">
            <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><rect x="2" y="3" width="20" height="14" rx="2"/><path stroke-linecap="round" d="M8 21h8m-4-4v4"/></svg>
            Open POS
        </a>
        <a href="{{ route('admin.stock-takes.create') }}" class="dash-btn">
            <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2"/></svg>
            Stock Take
        </a>
        <a href="{{ route('admin.products.create') }}" class="dash-btn">
            <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
            Add Product
        </a>
        <a href="{{ route('admin.orders.index') }}" class="dash-btn-outline">
            <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2"/></svg>
            Sales History
        </a>
        <a href="{{ route('admin.reports.index') }}" class="dash-btn-outline">
            <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
            Reports
        </a>
    </div>

    {{-- KPI grid --}}
    <div class="dash-kpis">
        <a class="dash-kpi" href="{{ route('admin.products.index') }}">
            <div class="dash-kpi-icon purple">
                <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
            </div>
            <div class="dash-kpi-body">
                <div class="dash-kpi-label">Total Products</div>
                <div class="dash-kpi-value">{{ $productCount }}</div>
                <div class="dash-kpi-note">{{ $activeProductCount }} active</div>
                @if(!is_null($trends['products'] ?? null))
                    <span class="dash-kpi-trend {{ $trends['products'] >= 0 ? 'up' : 'down' }}">
                        {{ $trends['products'] >= 0 ? '↑' : '↓' }} {{ abs($trends['products']) }}%
                    </span>
                @endif
            </div>
        </a>
        <a class="dash-kpi" href="{{ route('admin.products.index', ['stock' => 'low']) }}">
            <div class="dash-kpi-icon red">
                <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/></svg>
            </div>
            <div class="dash-kpi-body">
                <div class="dash-kpi-label">Low Stock</div>
                <div class="dash-kpi-value">{{ $lowStockCount }}</div>
                <div class="dash-kpi-note">5 or fewer units</div>
            </div>
        </a>
        <a class="dash-kpi" href="{{ route('admin.orders.index', ['status' => 'pending']) }}">
            <div class="dash-kpi-icon orange">
                <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
            </div>
            <div class="dash-kpi-body">
                <div class="dash-kpi-label">Pending Orders</div>
                <div class="dash-kpi-value">{{ $pendingOrderCount }}</div>
                <div class="dash-kpi-note">{{ $orderCount }} total orders</div>
                @if(!is_null($trends['pending'] ?? null))
                    <span class="dash-kpi-trend {{ $trends['pending'] >= 0 ? 'up' : 'down' }}">
                        {{ $trends['pending'] >= 0 ? '↑' : '↓' }} {{ abs($trends['pending']) }}%
                    </span>
                @endif
            </div>
        </a>
        <a class="dash-kpi" href="{{ route('admin.reports.index') }}">
            <div class="dash-kpi-icon green">
                <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/></svg>
            </div>
            <div class="dash-kpi-body">
                <div class="dash-kpi-label">Revenue This Month</div>
                <div class="dash-kpi-value" style="font-size:1.2rem;">KES {{ number_format($monthlyRevenue, 0) }}</div>
                <div class="dash-kpi-note">{{ $categoryCount }} categories</div>
                @if(!is_null($trends['revenue'] ?? null))
                    <span class="dash-kpi-trend {{ $trends['revenue'] >= 0 ? 'up' : 'down' }}">
                        {{ $trends['revenue'] >= 0 ? '↑' : '↓' }} {{ abs($trends['revenue']) }}%
                    </span>
                @endif
            </div>
        </a>
        <a class="dash-kpi" href="{{ route('admin.enquiries.index') }}">
            <div class="dash-kpi-icon blue">
                <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
            </div>
            <div class="dash-kpi-body">
                <div class="dash-kpi-label">Enquiries</div>
                <div class="dash-kpi-value">{{ $enquiryCount }}</div>
                <div class="dash-kpi-note">{{ $enquiryCount ? 'Open inbox' : 'All clear' }}</div>
            </div>
        </a>
        <a class="dash-kpi" href="{{ route('admin.newsletter-subscribers.index') }}">
            <div class="dash-kpi-icon pink">
                <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
            </div>
            <div class="dash-kpi-body">
                <div class="dash-kpi-label">Subscribers</div>
                <div class="dash-kpi-value">{{ $subscriberCount }}</div>
                <div class="dash-kpi-note">Newsletter list</div>
            </div>
        </a>
        <a class="dash-kpi" href="{{ route('admin.blog.index') }}">
            <div class="dash-kpi-icon gold">
                <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
            </div>
            <div class="dash-kpi-body">
                <div class="dash-kpi-label">Blog Posts</div>
                <div class="dash-kpi-value">{{ $blogCount }}</div>
                <div class="dash-kpi-note">Published content</div>
            </div>
        </a>
        <a class="dash-kpi" href="{{ route('admin.contact.index') }}">
            <div class="dash-kpi-icon teal">
                <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/></svg>
            </div>
            <div class="dash-kpi-body">
                <div class="dash-kpi-label">Contacts</div>
                <div class="dash-kpi-value">{{ $contactCount }}</div>
                <div class="dash-kpi-note">Total contacts</div>
            </div>
        </a>
    </div>

    {{-- Main body --}}
    <div class="dash-body">
        {{-- Recent Orders --}}
        <div class="dash-card">
            <div class="dash-card-head">
                <h3>Recent Orders</h3>
                <a href="{{ route('admin.orders.index') }}" class="dash-link">
                    Sales history
                    <svg width="12" height="12" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
                </a>
            </div>
            <div style="overflow-x:auto;">
                <table class="dash-table">
                    <thead>
                        <tr>
                            <th>Order ID</th>
                            <th>Customer</th>
                            <th>Total</th>
                            <th>Status</th>
                            <th>Date</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                    @forelse($recentOrders as $order)
                        @php
                            $dpill = match($order->status) {
                                'paid','delivered','completed' => 'paid',
                                'processing','shipped' => 'shipped',
                                'cancelled' => 'cancelled',
                                default => 'pending',
                            };
                        @endphp
                        <tr>
                            <td><strong>{{ $order->order_number }}</strong></td>
                            <td>{{ $order->customer_name }}</td>
                            <td><strong>KES {{ number_format((float)$order->total_amount, 2) }}</strong></td>
                            <td><span class="dpill {{ $dpill }}">{{ ucfirst(str_replace('_',' ',$order->status)) }}</span></td>
                            <td style="color:#9ca3af;font-size:12px;">{{ $order->created_at->format('d M Y') }}</td>
                            <td>
                                <a href="{{ route('admin.orders.show', $order) }}" class="dash-view-btn">
                                    <svg width="12" height="12" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                    View
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6" style="text-align:center;padding:32px;color:#9ca3af;font-size:13px;">No orders yet.</td></tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
            <div style="padding:10px 20px;font-size:12px;color:#9ca3af;border-top:1px solid #f3f4f6;">
                Showing {{ $recentOrders->count() }} latest {{ \Illuminate\Support\Str::plural('order', $recentOrders->count()) }}
            </div>
        </div>

        {{-- Low Stock Alert --}}
        <div class="dash-card">
            <div class="dash-card-head">
                <h3>Low Stock Alert</h3>
                <a href="{{ route('admin.products.index', ['stock' => 'low']) }}" class="dash-link">View all</a>
            </div>
            <div class="dash-card-body">
                @if($lowStockProducts->isEmpty())
                    <div class="dash-all-good">
                        <div class="dash-all-good-icon">
                            <svg width="24" height="24" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                        </div>
                        <h4>All products stocked</h4>
                        <p>No items are running low at the moment.</p>
                    </div>
                @else
                    <div class="dash-stock-list">
                        @foreach($lowStockProducts as $product)
                            <a class="dash-stock-item" href="{{ route('admin.products.edit', $product) }}">
                                <div>
                                    <div class="dash-stock-name">{{ $product->name }}</div>
                                    <div class="dash-stock-cat">{{ $product->category?->name ?? 'Uncategorized' }}</div>
                                </div>
                                <div>
                                    <div class="dash-stock-qty">{{ $product->stock }}</div>
                                    <div class="dash-stock-lbl">left</div>
                                </div>
                            </a>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>
    </div>

</div>
@endsection
