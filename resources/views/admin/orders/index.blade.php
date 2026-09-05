@extends('layouts.admin')
@section('title', 'Sales History')

@php
    $filtersOpen = request()->anyFilled(['q', 'status', 'payment_method', 'source', 'from_date', 'to_date']);
    $trendPill = function (?float $value): array {
        if ($value === null) {
            return ['—', 'bg-gray-100 text-gray-600'];
        }
        $label = ($value >= 0 ? '+' : '').number_format($value, 0).'%';
        $class = $value >= 0 ? 'bg-success-50 text-success-700' : 'bg-error-50 text-error-700';

        return [$label, $class];
    };
    [$todayTrendLabel, $todayTrendClass] = $trendPill($summaries['today']['trend'] ?? null);
    [$weekTrendLabel, $weekTrendClass] = $trendPill($summaries['week']['trend'] ?? null);
    [$monthTrendLabel, $monthTrendClass] = $trendPill($summaries['month']['trend'] ?? null);
@endphp

@section('page_header')
<div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
    <div class="min-w-0">
        <nav class="mb-2 flex flex-wrap items-center gap-1.5 text-sm text-gray-400">
            <a href="{{ route('admin.dashboard') }}" class="hover:text-brand-500">Dashboard</a>
            <span>/</span>
            <span>Sales</span>
            <span>/</span>
            <span class="text-gray-600 dark:text-gray-300">Sales History</span>
        </nav>
        <div class="flex items-start gap-3">
            <span class="mt-0.5 flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-brand-50 text-brand-700 dark:bg-brand-500/15 dark:text-brand-400">
                <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
            </span>
            <div>
                <h1 class="text-xl font-bold text-gray-800 dark:text-white/90 sm:text-2xl">Sales History</h1>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">POS tickets and online orders in one desk. View, search and manage all sales transactions.</p>
            </div>
        </div>
    </div>
    <div class="flex flex-wrap items-center gap-2">
        <span class="inline-flex items-center gap-2 rounded-xl border border-gray-200 bg-white px-3 py-2.5 text-sm font-medium text-gray-600 dark:border-gray-800 dark:bg-white/[0.03] dark:text-gray-300">
            <svg class="h-4 w-4 text-brand-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><rect x="3" y="4" width="18" height="18" rx="2"/><path d="M16 2v4M8 2v4M3 10h18"/></svg>
            {{ now()->format('D, d M Y') }}
        </span>
        @if(auth()->user()?->hasPermission('create_sale'))
            <a href="{{ route('admin.pos.index') }}" class="ta-btn w-full justify-center sm:w-auto">
                <span class="text-lg leading-none">+</span> New Sale
            </a>
        @endif
    </div>
</div>
@endsection

@section('content')
<div class="space-y-5 pb-20 lg:pb-0" x-data="{ filtersOpen: {{ $filtersOpen ? 'true' : 'false' }} }">
    <div class="grid grid-cols-2 gap-3 xl:grid-cols-4">
        <div class="rounded-2xl border border-gray-200 bg-white p-3.5 shadow-theme-xs dark:border-gray-800 dark:bg-white/[0.03] sm:p-5">
            <div class="flex items-start justify-between gap-2">
                <div class="ta-kpi-icon is-blue !h-10 !w-10 sm:!h-12 sm:!w-12">
                    <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                </div>
                <span class="rounded-full px-2 py-0.5 text-[10px] font-bold {{ $todayTrendClass }}">{{ $todayTrendLabel }}</span>
            </div>
            <p class="mt-3 text-[11px] font-semibold uppercase tracking-wide text-gray-500">Today</p>
            <p class="mt-0.5 text-xl font-bold text-gray-800 dark:text-white/90 sm:text-2xl">{{ number_format($summaries['today']['count']) }}</p>
            <p class="mt-1 text-[11px] text-gray-400">KES {{ number_format($summaries['today']['revenue'], 2) }}</p>
            <p class="mt-1 text-[10px] text-gray-400">vs yesterday</p>
        </div>
        <div class="rounded-2xl border border-gray-200 bg-white p-3.5 shadow-theme-xs dark:border-gray-800 dark:bg-white/[0.03] sm:p-5">
            <div class="flex items-start justify-between gap-2">
                <div class="ta-kpi-icon is-success !h-10 !w-10 sm:!h-12 sm:!w-12">
                    <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                </div>
                <span class="rounded-full px-2 py-0.5 text-[10px] font-bold {{ $weekTrendClass }}">{{ $weekTrendLabel }}</span>
            </div>
            <p class="mt-3 text-[11px] font-semibold uppercase tracking-wide text-gray-500">This Week</p>
            <p class="mt-0.5 text-xl font-bold text-gray-800 dark:text-white/90 sm:text-2xl">{{ number_format($summaries['week']['count']) }}</p>
            <p class="mt-1 text-[11px] text-gray-400">KES {{ number_format($summaries['week']['revenue'], 2) }}</p>
            <p class="mt-1 text-[10px] text-gray-400">vs last week</p>
        </div>
        <div class="rounded-2xl border border-gray-200 bg-white p-3.5 shadow-theme-xs dark:border-gray-800 dark:bg-white/[0.03] sm:p-5">
            <div class="flex items-start justify-between gap-2">
                <div class="ta-kpi-icon is-purple !h-10 !w-10 sm:!h-12 sm:!w-12">
                    <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/></svg>
                </div>
                <span class="rounded-full px-2 py-0.5 text-[10px] font-bold {{ $monthTrendClass }}">{{ $monthTrendLabel }}</span>
            </div>
            <p class="mt-3 text-[11px] font-semibold uppercase tracking-wide text-gray-500">This Month</p>
            <p class="mt-0.5 text-xl font-bold text-gray-800 dark:text-white/90 sm:text-2xl">{{ number_format($summaries['month']['count']) }}</p>
            <p class="mt-1 text-[11px] text-gray-400">KES {{ number_format($summaries['month']['revenue'], 2) }}</p>
            <p class="mt-1 text-[10px] text-gray-400">vs last month</p>
        </div>
        <div class="rounded-2xl border border-gray-200 bg-white p-3.5 shadow-theme-xs dark:border-gray-800 dark:bg-white/[0.03] sm:p-5">
            <div class="flex items-start justify-between gap-2">
                <div class="ta-kpi-icon is-warning !h-10 !w-10 sm:!h-12 sm:!w-12">
                    <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </div>
                <span class="rounded-full bg-blue-light-50 px-2 py-0.5 text-[10px] font-bold text-blue-light-700">All time</span>
            </div>
            <p class="mt-3 text-[11px] font-semibold uppercase tracking-wide text-gray-500">Total Sales</p>
            <p class="mt-0.5 text-xl font-bold text-gray-800 dark:text-white/90 sm:text-2xl">{{ number_format($summaries['all']['count'] ?? 0) }}</p>
            <p class="mt-1 text-[11px] text-gray-400">KES {{ number_format($summaries['all']['revenue'] ?? 0, 2) }}</p>
        </div>
    </div>

    <div class="rounded-2xl border border-gray-200 bg-white p-3 shadow-theme-xs dark:border-gray-800 dark:bg-white/[0.03] sm:p-4">
        <div class="mb-3 flex items-center justify-between">
            <p class="text-sm font-semibold text-gray-800 dark:text-white/90">Filters &amp; Search</p>
            <button type="button" @click="filtersOpen = !filtersOpen" class="ta-btn-outline ta-btn-sm lg:hidden">
                <span x-text="filtersOpen ? 'Hide' : 'Show'"></span>
            </button>
        </div>
        <form method="GET" action="{{ route('admin.orders.index') }}"
              class="grid grid-cols-1 gap-3 sm:grid-cols-2 xl:grid-cols-3"
              :class="filtersOpen ? 'grid' : 'hidden lg:grid'">
            <div class="ta-field sm:col-span-2 xl:col-span-1">
                <label>Search</label>
                <input type="text" name="q" class="ta-input" value="{{ request('q') }}" placeholder="Order # / customer">
            </div>
            <div class="ta-field">
                <label>Status</label>
                <select name="status" class="ta-select">
                    <option value="">All Statuses</option>
                    @foreach(['pending','processing','paid','shipped','delivered','cancellation_requested','cancelled'] as $s)
                        <option value="{{ $s }}" @selected(request('status') === $s)>{{ ucwords(str_replace('_',' ',$s)) }}</option>
                    @endforeach
                </select>
            </div>
            <div class="ta-field">
                <label>Payment</label>
                <select name="payment_method" class="ta-select">
                    <option value="">All Methods</option>
                    @foreach(['cash','cash_on_delivery','mobile_money','bank_transfer','card'] as $m)
                        <option value="{{ $m }}" @selected(request('payment_method') === $m)>{{ ucwords(str_replace('_',' ',$m)) }}</option>
                    @endforeach
                </select>
            </div>
            <div class="ta-field">
                <label>Source</label>
                <select name="source" class="ta-select">
                    <option value="">All Sources</option>
                    <option value="pos" @selected(request('source') === 'pos')>POS</option>
                    <option value="online" @selected(request('source') === 'online')>Online</option>
                </select>
            </div>
            <div class="ta-field">
                <label>From</label>
                <input type="date" name="from_date" class="ta-input" value="{{ request('from_date') }}">
            </div>
            <div class="ta-field">
                <label>To</label>
                <input type="date" name="to_date" class="ta-input" value="{{ request('to_date') }}">
            </div>
            <input type="hidden" name="per_page" value="{{ $perPage ?? 10 }}">
            <div class="flex flex-wrap items-end gap-2 sm:col-span-2 xl:col-span-3">
                <button type="submit" class="ta-btn">Filter</button>
                @if($filtersOpen)
                    <a href="{{ route('admin.orders.index') }}" class="ta-btn-outline">Reset</a>
                @endif
                <a href="{{ route('admin.orders.export.csv', request()->query()) }}" class="ta-btn-outline">Export CSV</a>
            </div>
        </form>
    </div>

    <div class="rounded-2xl border border-gray-200 bg-white shadow-theme-xs dark:border-gray-800 dark:bg-white/[0.03]">
        <form method="POST" action="{{ route('admin.orders.bulk-update') }}">
            @csrf
            <div class="flex flex-wrap items-center gap-2 border-b border-gray-100 px-4 py-3 dark:border-gray-800 sm:px-5">
                <select name="status" required class="ta-select !py-1.5" style="max-width:220px;">
                    <option value="">Set status for selected</option>
                    @foreach(['pending','processing','paid','shipped','delivered','cancellation_requested','cancelled'] as $s)
                        <option value="{{ $s }}">{{ ucwords(str_replace('_',' ',$s)) }}</option>
                    @endforeach
                </select>
                <button type="submit" class="ta-btn ta-btn-sm" onclick="return confirm('Update selected orders?')">Apply</button>
                <div class="ml-auto flex items-center gap-2 text-sm text-gray-500">
                    <span class="hidden sm:inline">Show</span>
                    <select class="ta-select !py-1.5" style="max-width:90px;" onchange="const u=new URL(window.location.href);u.searchParams.set('per_page',this.value);window.location=u;">
                        @foreach([10, 20, 50, 100] as $n)
                            <option value="{{ $n }}" @selected(($perPage ?? 10) === $n)>{{ $n }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            {{-- Mobile cards --}}
            <div class="space-y-3 p-3 lg:hidden">
                @forelse($orders as $order)
                    @php
                        $pillClass = match($order->status) {
                            'paid','delivered','completed' => 'ta-pill-success',
                            'processing','shipped' => 'ta-pill-info',
                            'cancelled' => 'ta-pill-error',
                            default => 'ta-pill-warning',
                        };
                        $isPos = ($order->source ?? null) === 'pos' || str_starts_with((string) $order->order_number, 'POS-');
                        $initial = strtoupper(substr($order->customer_name ?: 'G', 0, 1));
                    @endphp
                    <div class="rounded-2xl border border-gray-200 bg-white p-3.5 shadow-theme-xs dark:border-gray-800 dark:bg-white/[0.02]">
                        <div class="flex items-start gap-3">
                            <input type="checkbox" name="order_ids[]" value="{{ $order->id }}" class="order-check mt-1 h-4 w-4 accent-brand-500">
                            <div class="min-w-0 flex-1">
                                <div class="flex items-start justify-between gap-2">
                                    <div class="min-w-0">
                                        <p class="truncate text-sm font-bold text-gray-800 dark:text-white/90">{{ $order->order_number }}</p>
                                        <p class="mt-0.5 truncate text-xs text-gray-400">{{ $order->created_at?->format('d M Y · h:i A') }}</p>
                                    </div>
                                    <span class="ta-pill {{ $pillClass }}">{{ ucwords(str_replace('_',' ',$order->status)) }}</span>
                                </div>
                                <div class="mt-3 flex items-center gap-2">
                                    <span class="flex h-8 w-8 items-center justify-center rounded-full bg-brand-500 text-xs font-bold text-white">{{ $initial }}</span>
                                    <div class="min-w-0">
                                        <p class="truncate text-sm font-medium text-gray-800 dark:text-white/90">{{ $order->customer_name ?: 'Guest' }}</p>
                                        <p class="truncate text-xs text-gray-400">{{ $order->customer_email ?: ($order->customer_phone ?? '—') }}</p>
                                    </div>
                                </div>
                                <div class="mt-3 flex flex-wrap items-center justify-between gap-2 border-t border-gray-100 pt-3 dark:border-gray-800">
                                    <div>
                                        <p class="text-sm font-bold text-gray-800 dark:text-white/90">KES {{ number_format((float) $order->total_amount, 2) }}</p>
                                        <p class="text-xs text-gray-400">{{ ucwords(str_replace('_',' ',$order->payment_method)) }} · {{ $order->user?->name ?: '—' }}</p>
                                    </div>
                                    <div class="flex items-center gap-2">
                                        @if($isPos)
                                            <span class="rounded-full bg-brand-50 px-2.5 py-0.5 text-[10px] font-bold text-brand-800">POS</span>
                                        @else
                                            <span class="rounded-full bg-blue-light-50 px-2.5 py-0.5 text-[10px] font-bold text-blue-light-700">Online</span>
                                        @endif
                                        <a href="{{ route('admin.orders.show', $order) }}" class="ta-btn-outline ta-btn-sm">View</a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="rounded-2xl border border-dashed border-gray-200 px-4 py-10 text-center text-sm text-gray-400 dark:border-gray-800">No sales found.</div>
                @endforelse
            </div>

            <div class="ta-table-wrap hidden lg:block">
                <table class="ta-table" style="min-width:1100px;">
                    <thead>
                        <tr>
                            <th style="width:40px;">
                                <input type="checkbox" id="select-all-orders" class="h-4 w-4 accent-brand-500">
                            </th>
                            <th style="width:48px;">#</th>
                            <th>Order ID</th>
                            <th>Customer</th>
                            <th>Cashier</th>
                            <th>Total</th>
                            <th>Payment</th>
                            <th>Source</th>
                            <th>Status</th>
                            <th>Date &amp; Time</th>
                            <th class="text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                    @forelse($orders as $index => $order)
                        @php
                            $pillClass = match($order->status) {
                                'paid','delivered','completed' => 'ta-pill-success',
                                'processing','shipped' => 'ta-pill-info',
                                'cancelled' => 'ta-pill-error',
                                default => 'ta-pill-warning',
                            };
                            $isPos = ($order->source ?? null) === 'pos' || str_starts_with((string) $order->order_number, 'POS-');
                            $initial = strtoupper(substr($order->customer_name ?: 'G', 0, 1));
                        @endphp
                        <tr>
                            <td>
                                <input type="checkbox" name="order_ids[]" value="{{ $order->id }}" class="order-check h-4 w-4 accent-brand-500">
                            </td>
                            <td class="text-gray-400">{{ $orders->firstItem() + $index }}</td>
                            <td><span class="ta-name">{{ $order->order_number }}</span></td>
                            <td>
                                <div class="flex items-center gap-2.5">
                                    <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-brand-500 text-xs font-bold text-white">{{ $initial }}</span>
                                    <div class="min-w-0">
                                        <div class="ta-name truncate">{{ $order->customer_name ?: 'Guest' }}</div>
                                        <div class="ta-muted truncate">{{ $order->customer_email ?: ($order->customer_phone ?? '—') }}</div>
                                    </div>
                                </div>
                            </td>
                            <td class="text-gray-500">{{ $order->user?->name ?: '—' }}</td>
                            <td class="font-semibold text-gray-800 dark:text-white/90">KES {{ number_format((float) $order->total_amount, 2) }}</td>
                            <td class="text-gray-500">{{ ucwords(str_replace('_',' ',$order->payment_method)) }}</td>
                            <td>
                                @if($isPos)
                                    <span class="rounded-full bg-brand-50 px-2.5 py-1 text-[11px] font-bold text-brand-800">POS</span>
                                @else
                                    <span class="rounded-full bg-blue-light-50 px-2.5 py-1 text-[11px] font-bold text-blue-light-700">Online</span>
                                @endif
                            </td>
                            <td><span class="ta-pill {{ $pillClass }}">{{ ucwords(str_replace('_',' ',$order->status)) }}</span></td>
                            <td class="whitespace-nowrap text-xs text-gray-500">{{ $order->created_at?->format('d M Y h:i A') }}</td>
                            <td class="text-right">
                                <a href="{{ route('admin.orders.show', $order) }}" class="inline-flex items-center gap-1.5 rounded-lg px-2.5 py-1.5 text-sm font-medium text-brand-700 hover:bg-brand-50">
                                    <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                    View
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="11" class="ta-empty">No sales found.</td></tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </form>

        <div class="flex flex-col gap-3 border-t border-gray-100 px-4 py-3 dark:border-gray-800 sm:flex-row sm:items-center sm:justify-between sm:px-5">
            <span class="ta-muted">Showing {{ $orders->firstItem() ?? 0 }} to {{ $orders->lastItem() ?? 0 }} of {{ $orders->total() }}</span>
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
