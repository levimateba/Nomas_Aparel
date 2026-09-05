@extends('layouts.admin')
@section('title', 'Reports')

@php
    $trendPill = function (?float $value): array {
        if ($value === null) {
            return ['—', 'text-gray-500 bg-gray-100'];
        }
        $label = ($value >= 0 ? '+ ' : '').number_format($value, 0).'%';
        $class = $value >= 0 ? 'text-success-700 bg-success-50' : 'text-error-700 bg-error-50';

        return [$label, $class];
    };
    $filtersOpen = request()->anyFilled(['from', 'to', 'preset']) || ($preset ?? '') === 'custom';
@endphp

@section('page_header')
<div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between" x-data="{ generateOpen: false }">
    <div class="min-w-0">
        <nav class="mb-2 flex flex-wrap items-center gap-1.5 text-sm text-gray-400">
            <a href="{{ route('admin.dashboard') }}" class="hover:text-brand-500">Dashboard</a>
            <span>/</span>
            <span class="text-gray-600 dark:text-gray-300">Reports</span>
        </nav>
        <div class="flex items-start gap-3">
            <span class="mt-0.5 flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-brand-50 text-brand-700 dark:bg-brand-500/15 dark:text-brand-400">
                <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6m6 0V9a2 2 0 012-2h2a2 2 0 012 2v10m6 0V5a2 2 0 00-2-2h-2a2 2 0 00-2 2v14"/></svg>
            </span>
            <div>
                <h1 class="text-xl font-bold text-gray-800 dark:text-white/90 sm:text-2xl">Reports</h1>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Monitor your business performance and sales insights.</p>
            </div>
        </div>
    </div>
    <div class="flex flex-wrap items-center gap-2">
        <span class="inline-flex items-center gap-2 rounded-xl border border-gray-200 bg-white px-3 py-2.5 text-sm font-medium text-gray-600 dark:border-gray-800 dark:bg-white/[0.03]">
            <svg class="h-4 w-4 text-brand-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><rect x="3" y="4" width="18" height="18" rx="2"/><path d="M16 2v4M8 2v4M3 10h18"/></svg>
            {{ now()->format('D, d M Y') }}
        </span>
        <div class="relative">
            <button type="button" @click="generateOpen = !generateOpen" class="ta-btn w-full justify-center sm:w-auto">
                Generate Report
                <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/></svg>
            </button>
            <div x-show="generateOpen" @click.outside="generateOpen = false" x-cloak
                 class="absolute right-0 z-20 mt-2 w-56 rounded-2xl border border-gray-200 bg-white p-2 shadow-theme-lg dark:border-gray-800 dark:bg-gray-900">
                <a href="{{ route('admin.reports.export', array_merge(request()->query(), ['format'=>'pdf','from'=>$from,'to'=>$to])) }}" class="block rounded-xl px-3 py-2.5 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:text-gray-200">Export PDF</a>
                <a href="{{ route('admin.reports.export', array_merge(request()->query(), ['format'=>'excel','from'=>$from,'to'=>$to])) }}" class="block rounded-xl px-3 py-2.5 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:text-gray-200">Export Excel / CSV</a>
                @if(auth()->user()?->hasPermission('view_cashier_performance'))
                    <a href="{{ route('admin.reports.cashier', ['preset'=>$preset,'from'=>$from,'to'=>$to]) }}" class="block rounded-xl px-3 py-2.5 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:text-gray-200">Cashier Performance</a>
                @endif
                <a href="{{ route('admin.reports.stock-valuation') }}" class="block rounded-xl px-3 py-2.5 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:text-gray-200">Stock Valuation</a>
            </div>
        </div>
    </div>
</div>
@endsection

@section('content')
<div class="space-y-5 pb-20 lg:pb-0" x-data="{ filtersOpen: {{ $filtersOpen ? 'true' : 'false' }} }">
    {{-- Filters --}}
    <div class="rounded-2xl border border-gray-200 bg-white p-3 shadow-theme-xs dark:border-gray-800 dark:bg-white/[0.03] sm:p-4">
        <div class="mb-3 flex flex-wrap items-center justify-between gap-2">
            <div class="flex flex-wrap gap-1.5">
                @foreach(['today' => 'Today', 'yesterday' => 'Yesterday', 'week' => 'This Week', 'month' => 'This Month'] as $key => $label)
                    <a href="{{ route('admin.reports.index', ['preset' => $key]) }}"
                       class="rounded-lg px-3 py-1.5 text-xs font-bold {{ ($preset ?? '') === $key ? 'bg-brand-500 text-gray-900' : 'border border-gray-200 text-gray-600 hover:bg-gray-50 dark:border-gray-700 dark:text-gray-300' }}">
                        {{ $label }}
                    </a>
                @endforeach
                <button type="button" @click="filtersOpen = true"
                        class="rounded-lg border border-gray-200 px-3 py-1.5 text-xs font-bold {{ ($preset ?? '') === 'custom' ? 'bg-brand-500 text-gray-900' : 'text-gray-600 hover:bg-gray-50 dark:border-gray-700' }}">
                    Custom
                </button>
            </div>
            <button type="button" @click="filtersOpen = !filtersOpen" class="ta-btn-outline ta-btn-sm lg:hidden">
                <span x-text="filtersOpen ? 'Hide' : 'Dates'"></span>
            </button>
        </div>

        <form method="GET" action="{{ route('admin.reports.index') }}"
              class="grid grid-cols-1 gap-3 sm:grid-cols-2 xl:grid-cols-5"
              :class="filtersOpen ? 'grid' : 'hidden lg:grid'">
            <input type="hidden" name="preset" value="custom">
            <div class="ta-field">
                <label>From date</label>
                <input type="date" name="from" class="ta-input" value="{{ $from }}">
            </div>
            <div class="ta-field">
                <label>To date</label>
                <input type="date" name="to" class="ta-input" value="{{ $to }}">
            </div>
            <div class="ta-field">
                <label>Report type</label>
                <select class="ta-select" disabled>
                    <option>All Reports</option>
                </select>
            </div>
            <div class="ta-field">
                <label>Location</label>
                <select class="ta-select" disabled>
                    <option>All Locations</option>
                    @foreach(($locations ?? []) as $loc)
                        <option>{{ $loc->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="flex flex-wrap items-end gap-2">
                <button type="submit" class="ta-btn">Apply</button>
                <a href="{{ route('admin.reports.index', ['preset' => 'today']) }}" class="ta-btn-outline">Reset</a>
            </div>
        </form>
    </div>

    {{-- KPI grid --}}
    <div class="grid grid-cols-2 gap-3 xl:grid-cols-4">
        @php
            $kpis = [
                ['label' => 'Total Sales', 'value' => 'KES '.number_format($summary['gross'], 2), 'sub' => number_format($summary['count']).' tickets', 'trend' => $trends['gross'] ?? null, 'icon' => 'blue', 'svg' => 'M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2 9m12-9l2 9'],
                ['label' => 'Gross Sales', 'value' => 'KES '.number_format($summary['gross'], 2), 'sub' => $trendLabel, 'trend' => $trends['gross'] ?? null, 'icon' => 'success', 'svg' => 'M13 7h8m0 0v8m0-8l-8 8-4-4-6 6'],
                ['label' => 'Net Sales', 'value' => 'KES '.number_format($summary['net'], 2), 'sub' => 'After returns', 'trend' => $trends['net'] ?? null, 'icon' => 'purple', 'svg' => 'M9 14l6-6m-5.5.5h.01m4.99 5h.01M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16l3.5-2 3.5 2 3.5-2 3.5 2z'],
                ['label' => 'Discounts', 'value' => 'KES '.number_format($summary['discount'], 2), 'sub' => $trendLabel, 'trend' => $trends['discount'] ?? null, 'icon' => 'warning', 'svg' => 'M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z'],
                ['label' => 'Returns', 'value' => 'KES '.number_format($summary['returns'], 2), 'sub' => $trendLabel, 'trend' => $trends['returns'] ?? null, 'icon' => 'error', 'svg' => 'M16 15v-1a4 4 0 00-4-4H8m0 0l3 3m-3-3l3-3m9 14V5a2 2 0 00-2-2H6a2 2 0 00-2 2v16l4-2 4 2 4-2 4 2z'],
                ['label' => 'Average Ticket', 'value' => 'KES '.number_format($summary['average'], 2), 'sub' => $trendLabel, 'trend' => $trends['average'] ?? null, 'icon' => 'brand', 'svg' => 'M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z'],
                ['label' => 'POS Sales', 'value' => 'KES '.number_format($summary['pos_revenue'], 2), 'sub' => number_format($summary['pos_count']).' sales', 'trend' => $trends['pos'] ?? null, 'icon' => 'blue', 'svg' => 'M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z'],
                ['label' => 'Online Orders', 'value' => 'KES '.number_format($summary['online_revenue'], 2), 'sub' => number_format($summary['online_count']).' orders', 'trend' => $trends['online'] ?? null, 'icon' => 'success', 'svg' => 'M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9 3-9m-9 9a9 9 0 019-9'],
            ];
        @endphp
        @foreach($kpis as $kpi)
            @php [$tLabel, $tClass] = $trendPill($kpi['trend']); @endphp
            <div class="rounded-2xl border border-gray-200 bg-white p-3.5 shadow-theme-xs dark:border-gray-800 dark:bg-white/[0.03] sm:p-5">
                <div class="flex items-start justify-between gap-2">
                    <div class="ta-kpi-icon is-{{ $kpi['icon'] }} !h-10 !w-10 sm:!h-12 sm:!w-12">
                        <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $kpi['svg'] }}"/></svg>
                    </div>
                    <span class="rounded-full px-2 py-0.5 text-[10px] font-bold {{ $tClass }}">{{ $tLabel }}</span>
                </div>
                <p class="mt-3 text-[11px] font-semibold uppercase tracking-wide text-gray-500">{{ $kpi['label'] }}</p>
                <p class="mt-0.5 text-base font-bold text-gray-800 dark:text-white/90 sm:text-xl">{{ $kpi['value'] }}</p>
                <p class="mt-1 text-[11px] text-gray-400">{{ $kpi['sub'] }}</p>
            </div>
        @endforeach
    </div>

    {{-- Charts --}}
    <div class="grid grid-cols-1 gap-4 xl:grid-cols-12">
        <div class="rounded-2xl border border-gray-200 bg-white p-4 shadow-theme-xs dark:border-gray-800 dark:bg-white/[0.03] sm:p-5 xl:col-span-7">
            <div class="mb-4">
                <h3 class="text-base font-semibold text-gray-800 dark:text-white/90">Sales Trend</h3>
                <p class="text-sm text-gray-500">Total sales for the selected period</p>
            </div>
            <div id="rpt-sales-chart"></div>
        </div>
        <div class="rounded-2xl border border-gray-200 bg-white p-4 shadow-theme-xs dark:border-gray-800 dark:bg-white/[0.03] sm:p-5 xl:col-span-5">
            <div class="mb-4">
                <h3 class="text-base font-semibold text-gray-800 dark:text-white/90">Sales by Payment Method</h3>
                <p class="text-sm text-gray-500">Share of revenue by tender</p>
            </div>
            <div id="rpt-payment-chart"></div>
            <div class="mt-3 space-y-2">
                @forelse($paymentBreakdown as $i => $row)
                    @php
                        $totalPay = max((float) ($paymentChart['total'] ?? 0), 0.0001);
                        $pct = round(((float) $row->total / $totalPay) * 100, 0);
                        $color = $paymentChart['colors'][$i] ?? '#a58112';
                    @endphp
                    <div class="flex items-center justify-between text-sm">
                        <span class="inline-flex items-center gap-2 text-gray-600 dark:text-gray-300">
                            <span class="h-2.5 w-2.5 rounded-full" style="background:{{ $color }}"></span>
                            {{ ucwords(str_replace('_',' ', $row->payment_method ?: 'Other')) }}
                        </span>
                        <span class="font-semibold text-gray-800 dark:text-white/90">{{ $pct }}%</span>
                    </div>
                @empty
                    <p class="text-sm text-gray-400">No payments recorded.</p>
                @endforelse
            </div>
        </div>
    </div>

    {{-- Tables --}}
    <div class="grid grid-cols-1 gap-4 xl:grid-cols-12">
        <div class="rounded-2xl border border-gray-200 bg-white shadow-theme-xs dark:border-gray-800 dark:bg-white/[0.03] xl:col-span-7">
            <div class="border-b border-gray-100 px-4 py-3 dark:border-gray-800 sm:px-5">
                <h3 class="text-base font-semibold text-gray-800 dark:text-white/90">Daily Sales Summary</h3>
            </div>
            <div class="ta-table-wrap">
                <table class="ta-table">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th class="text-right">Tickets</th>
                            <th class="text-right">Gross</th>
                            <th class="text-right hidden sm:table-cell">Net</th>
                            <th class="text-right">Returns</th>
                        </tr>
                    </thead>
                    <tbody>
                    @forelse($daily as $row)
                        <tr>
                            <td>{{ $row['label'] }}</td>
                            <td class="text-right">{{ $row['count'] }}</td>
                            <td class="text-right font-semibold">KES {{ number_format($row['sales'] + $row['returns'], 2) }}</td>
                            <td class="text-right hidden sm:table-cell">KES {{ number_format($row['sales'], 2) }}</td>
                            <td class="text-right">KES {{ number_format($row['returns'], 2) }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="ta-empty">No activity in this period.</td></tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="rounded-2xl border border-gray-200 bg-white shadow-theme-xs dark:border-gray-800 dark:bg-white/[0.03] xl:col-span-5">
            <div class="flex items-center justify-between border-b border-gray-100 px-4 py-3 dark:border-gray-800 sm:px-5">
                <h3 class="text-base font-semibold text-gray-800 dark:text-white/90">Top Selling Products</h3>
            </div>
            <div class="divide-y divide-gray-100 dark:divide-gray-800">
                @forelse($topProducts as $index => $item)
                    <div class="flex items-center gap-3 px-4 py-3 sm:px-5">
                        <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-gray-100 text-xs font-bold text-gray-500">{{ $index + 1 }}</span>
                        <div class="min-w-0 flex-1">
                            <p class="truncate text-sm font-semibold text-gray-800 dark:text-white/90">{{ $item->product_name }}</p>
                            <p class="text-xs text-gray-400">Qty {{ (int) $item->quantity }}</p>
                        </div>
                        <p class="shrink-0 text-sm font-bold text-gray-800 dark:text-white/90">KES {{ number_format((float) $item->revenue, 2) }}</p>
                    </div>
                @empty
                    <div class="px-4 py-10 text-center text-sm text-gray-400">No product sales in this period.</div>
                @endforelse
            </div>
        </div>
    </div>

    {{-- Quick reports --}}
    <div class="rounded-2xl border border-gray-200 bg-white p-3 shadow-theme-xs dark:border-gray-800 dark:bg-white/[0.03] sm:p-4">
        <div class="mb-3 flex items-center justify-between gap-2">
            <h3 class="text-sm font-semibold text-gray-800 dark:text-white/90">Quick Reports</h3>
            <a href="{{ route('admin.reports.export', array_merge(request()->query(), ['format'=>'excel','from'=>$from,'to'=>$to])) }}" class="ta-btn-outline ta-btn-sm">Export All</a>
        </div>
        <div class="grid grid-cols-2 gap-2 sm:grid-cols-3 lg:grid-cols-5">
            <a href="{{ route('admin.reports.index', ['preset' => $preset, 'from' => $from, 'to' => $to]) }}" class="flex flex-col items-center gap-2 rounded-xl border border-gray-100 bg-gray-50 px-2 py-3 text-center no-underline hover:bg-brand-50 dark:border-gray-800 dark:bg-white/[0.03]">
                <span class="ta-kpi-icon is-brand !h-9 !w-9"><svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6m6 0V9a2 2 0 012-2h2a2 2 0 012 2v10m6 0V5a2 2 0 00-2-2h-2a2 2 0 00-2 2v14"/></svg></span>
                <span class="text-[11px] font-bold text-gray-700 dark:text-gray-200">Sales Report</span>
            </a>
            <a href="{{ route('admin.stock-overview.index') }}" class="flex flex-col items-center gap-2 rounded-xl border border-gray-100 bg-gray-50 px-2 py-3 text-center no-underline hover:bg-brand-50 dark:border-gray-800 dark:bg-white/[0.03]">
                <span class="ta-kpi-icon is-blue !h-9 !w-9"><svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg></span>
                <span class="text-[11px] font-bold text-gray-700 dark:text-gray-200">Inventory</span>
            </a>
            <a href="{{ route('admin.reports.stock-valuation') }}" class="flex flex-col items-center gap-2 rounded-xl border border-gray-100 bg-gray-50 px-2 py-3 text-center no-underline hover:bg-brand-50 dark:border-gray-800 dark:bg-white/[0.03]">
                <span class="ta-kpi-icon is-purple !h-9 !w-9"><svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg></span>
                <span class="text-[11px] font-bold text-gray-700 dark:text-gray-200">Stock Valuation</span>
            </a>
            @if(auth()->user()?->hasPermission('view_cashier_performance'))
                <a href="{{ route('admin.reports.cashier', ['preset'=>$preset,'from'=>$from,'to'=>$to]) }}" class="flex flex-col items-center gap-2 rounded-xl border border-gray-100 bg-gray-50 px-2 py-3 text-center no-underline hover:bg-brand-50 dark:border-gray-800 dark:bg-white/[0.03]">
                    <span class="ta-kpi-icon is-success !h-9 !w-9"><svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a4 4 0 00-4-4h-1M9 20H4v-2a4 4 0 014-4h1m4-8a4 4 0 11-8 0 4 4 0 018 0zm6 4a4 4 0 11-8 0 4 4 0 018 0z"/></svg></span>
                    <span class="text-[11px] font-bold text-gray-700 dark:text-gray-200">Cashier Perf.</span>
                </a>
            @endif
            <a href="{{ route('admin.products.index', ['stock' => 'low']) }}" class="flex flex-col items-center gap-2 rounded-xl border border-gray-100 bg-gray-50 px-2 py-3 text-center no-underline hover:bg-brand-50 dark:border-gray-800 dark:bg-white/[0.03]">
                <span class="ta-kpi-icon is-warning !h-9 !w-9"><svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/></svg></span>
                <span class="text-[11px] font-bold text-gray-700 dark:text-gray-200">Low Stock</span>
            </a>
        </div>
    </div>
</div>
@endsection

@push('scripts')
@php
    $rptSalesChart = $chart ?? ['labels' => [], 'sales' => []];
    $rptPaymentChartPayload = $paymentChart ?? ['labels' => [], 'series' => [], 'colors' => [], 'total' => 0];
@endphp
<script>
window.rptSalesChart = @json($rptSalesChart);
window.rptPaymentChart = @json($rptPaymentChartPayload);

document.addEventListener('DOMContentLoaded', function () {
    if (!window.ApexCharts) return;

    const salesEl = document.querySelector('#rpt-sales-chart');
    if (salesEl) {
        new ApexCharts(salesEl, {
            chart: { type: 'area', height: 280, fontFamily: 'Outfit, sans-serif', toolbar: { show: false }, zoom: { enabled: false } },
            colors: ['#a58112'],
            dataLabels: { enabled: false },
            stroke: { curve: 'smooth', width: 3 },
            markers: { size: 4, colors: ['#a58112'], strokeColors: '#fff', strokeWidth: 2 },
            fill: { type: 'gradient', gradient: { shadeIntensity: 1, opacityFrom: 0.35, opacityTo: 0.02, stops: [0, 90, 100] } },
            series: [{ name: 'Sales', data: window.rptSalesChart.sales || [] }],
            xaxis: {
                categories: window.rptSalesChart.labels || [],
                axisBorder: { show: false },
                axisTicks: { show: false },
                labels: { style: { colors: '#64748B', fontSize: '11px' }, rotate: -45, hideOverlappingLabels: true }
            },
            yaxis: { labels: { style: { colors: '#64748B', fontSize: '12px' }, formatter: (v) => 'KES ' + Number(v).toLocaleString() } },
            grid: { borderColor: '#EEF2F7', strokeDashArray: 4 },
            tooltip: { y: { formatter: (v) => 'KES ' + Number(v).toLocaleString(undefined, { minimumFractionDigits: 2 }) } }
        }).render();
    }

    const payEl = document.querySelector('#rpt-payment-chart');
    if (payEl) {
        const labels = window.rptPaymentChart.labels || [];
        const series = (window.rptPaymentChart.series || []).map(Number);
        const total = Number(window.rptPaymentChart.total || 0);
        new ApexCharts(payEl, {
            chart: { type: 'donut', height: 240, fontFamily: 'Outfit, sans-serif' },
            colors: window.rptPaymentChart.colors || ['#a58112'],
            labels: labels.length ? labels : ['No data'],
            series: series.length ? series : [1],
            legend: { show: false },
            dataLabels: { enabled: false },
            plotOptions: {
                pie: {
                    donut: {
                        size: '72%',
                        labels: {
                            show: true,
                            name: { show: true, fontSize: '12px', color: '#64748B', offsetY: 18 },
                            value: { show: true, fontSize: '16px', fontWeight: 700, color: '#111827', offsetY: -8, formatter: () => 'KES ' + total.toLocaleString(undefined, { minimumFractionDigits: 2 }) },
                            total: { show: true, label: 'Total Sales', fontSize: '12px', color: '#64748B', formatter: () => 'KES ' + total.toLocaleString(undefined, { minimumFractionDigits: 2 }) }
                        }
                    }
                }
            }
        }).render();
    }
});
</script>
@endpush
