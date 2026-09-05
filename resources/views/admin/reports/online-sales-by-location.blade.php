@extends('layouts.admin')
@section('title', 'Online Sales by Location')

@php
    $fromDate = \Illuminate\Support\Carbon::parse($from)->toDateString();
    $toDate = \Illuminate\Support\Carbon::parse($to)->toDateString();
@endphp

@section('page_header')
<div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
    <div class="min-w-0">
        <nav class="mb-2 flex flex-wrap items-center gap-1.5 text-sm text-gray-400">
            <a href="{{ route('admin.dashboard') }}" class="hover:text-brand-500">Dashboard</a>
            <span>/</span>
            <a href="{{ route('admin.reports.index') }}" class="hover:text-brand-500">Reports</a>
            <span>/</span>
            <span class="text-gray-600 dark:text-gray-300">Online by Location</span>
        </nav>
        <div class="flex items-start gap-3">
            <span class="mt-0.5 flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-brand-50 text-brand-700 dark:bg-brand-500/15 dark:text-brand-400">
                <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
            </span>
            <div>
                <h1 class="text-xl font-bold text-gray-800 dark:text-white/90 sm:text-2xl">Online Sales by Location</h1>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Units fulfilled from each inventory location for online orders.</p>
            </div>
        </div>
    </div>
    <div class="flex flex-wrap items-center gap-2">
        <a href="{{ route('admin.reports.index') }}" class="ta-btn-outline w-full justify-center sm:w-auto">Back to Reports</a>
    </div>
</div>
@endsection

@section('content')
<div class="space-y-5 pb-24 xl:pb-0">
    <div class="rounded-2xl border border-gray-200 bg-white p-3 shadow-theme-xs dark:border-gray-800 dark:bg-white/[0.03] sm:p-4">
        <form method="GET" action="{{ route('admin.reports.online-sales-by-location') }}" class="grid grid-cols-1 gap-3 sm:grid-cols-2 lg:grid-cols-4">
            <div class="ta-field">
                <label>From</label>
                <input type="date" name="from" class="ta-input" value="{{ $fromDate }}">
            </div>
            <div class="ta-field">
                <label>To</label>
                <input type="date" name="to" class="ta-input" value="{{ $toDate }}">
            </div>
            <div class="flex flex-wrap items-end gap-2 sm:col-span-2">
                <button type="submit" class="ta-btn">Apply</button>
                <a href="{{ route('admin.reports.online-sales-by-location') }}" class="ta-btn-outline">Reset</a>
            </div>
        </form>
    </div>

    <div class="grid grid-cols-2 gap-3 xl:grid-cols-4">
        @forelse($rows as $row)
            <div class="rounded-2xl border border-gray-200 bg-white p-3.5 shadow-theme-xs dark:border-gray-800 dark:bg-white/[0.03] sm:p-5">
                <div class="ta-kpi-icon is-blue !h-10 !w-10">
                    <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 21h18M5 21V7l7-4 7 4v14"/></svg>
                </div>
                <p class="mt-3 text-[11px] font-semibold uppercase tracking-wide text-gray-500">{{ $row->location?->name ?: 'Location #'.$row->stock_location_id }}</p>
                <p class="mt-0.5 text-xl font-bold text-gray-800 dark:text-white/90 sm:text-2xl">{{ number_format((int) $row->units) }}</p>
                <p class="mt-1 text-[11px] text-gray-400">{{ (int) $row->orders_count }} order{{ (int) $row->orders_count === 1 ? '' : 's' }}</p>
            </div>
        @empty
            <div class="col-span-2 rounded-2xl border border-dashed border-gray-200 px-4 py-8 text-center text-sm text-gray-400 xl:col-span-3 dark:border-gray-800">
                No online allocations in this period.
            </div>
        @endforelse
        <div class="rounded-2xl border border-brand-200 bg-brand-50/60 p-3.5 shadow-theme-xs dark:border-brand-500/30 dark:bg-brand-500/10 sm:p-5">
            <div class="ta-kpi-icon is-brand !h-10 !w-10">
                <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6m6 0V9a2 2 0 012-2h2a2 2 0 012 2v10m6 0V5a2 2 0 00-2-2h-2a2 2 0 00-2 2v14"/></svg>
            </div>
            <p class="mt-3 text-[11px] font-semibold uppercase tracking-wide text-brand-800 dark:text-brand-200">Total Units</p>
            <p class="mt-0.5 text-xl font-bold text-gray-800 dark:text-white/90 sm:text-2xl">{{ number_format($totalUnits) }}</p>
            <p class="mt-1 text-[11px] text-gray-500">All locations</p>
        </div>
    </div>

    <div class="rounded-2xl border border-gray-200 bg-white shadow-theme-xs dark:border-gray-800 dark:bg-white/[0.03]">
        <div class="border-b border-gray-100 px-4 py-3 dark:border-gray-800 sm:px-5">
            <h2 class="text-sm font-semibold text-gray-800 dark:text-white/90 sm:text-base">Recent allocations</h2>
            <p class="text-xs text-gray-500">Latest online order stock fulfillments</p>
        </div>

        {{-- Mobile cards --}}
        <div class="space-y-3 p-3 lg:hidden">
            @forelse($lines as $line)
                <div class="rounded-2xl border border-gray-200 bg-white p-3.5 shadow-theme-xs dark:border-gray-800 dark:bg-white/[0.02]">
                    <div class="flex items-start justify-between gap-2">
                        <div class="min-w-0">
                            <p class="truncate text-sm font-bold text-gray-800 dark:text-white/90">{{ $line->product?->name ?: '—' }}</p>
                            @if($line->variant)
                                <p class="truncate text-xs text-gray-400">{{ $line->variant->name }}</p>
                            @endif
                        </div>
                        <span class="shrink-0 text-sm font-bold text-brand-700">×{{ $line->quantity }}</span>
                    </div>
                    <div class="mt-3 grid grid-cols-2 gap-2 border-t border-gray-100 pt-3 text-xs dark:border-gray-800">
                        <div>
                            <p class="text-gray-400">Order</p>
                            <p class="font-medium text-gray-700 dark:text-gray-200">{{ $line->order?->order_number ?: '—' }}</p>
                        </div>
                        <div>
                            <p class="text-gray-400">Location</p>
                            <p class="font-medium text-gray-700 dark:text-gray-200">{{ $line->location?->name ?: '—' }}</p>
                        </div>
                        <div class="col-span-2">
                            <p class="text-gray-400">Date</p>
                            <p class="font-medium text-gray-700 dark:text-gray-200">{{ $line->created_at?->format('d M Y · h:i A') }}</p>
                        </div>
                    </div>
                </div>
            @empty
                <div class="rounded-2xl border border-dashed border-gray-200 px-4 py-10 text-center text-sm text-gray-400 dark:border-gray-800">No rows.</div>
            @endforelse
        </div>

        <div class="ta-table-wrap hidden lg:block">
            <table class="ta-table">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Order</th>
                        <th>Product</th>
                        <th>Location</th>
                        <th class="text-right">Qty</th>
                    </tr>
                </thead>
                <tbody>
                @forelse($lines as $line)
                    <tr>
                        <td class="whitespace-nowrap">{{ $line->created_at?->format('d M Y H:i') }}</td>
                        <td>
                            @if($line->order)
                                <a href="{{ route('admin.orders.show', $line->order) }}" class="ta-name hover:text-brand-600">{{ $line->order->order_number }}</a>
                            @else
                                —
                            @endif
                        </td>
                        <td>
                            <div class="ta-name">{{ $line->product?->name }}</div>
                            @if($line->variant)
                                <div class="ta-muted">{{ $line->variant->name }}</div>
                            @endif
                        </td>
                        <td>{{ $line->location?->name }}</td>
                        <td class="text-right font-semibold text-gray-800 dark:text-white/90">{{ $line->quantity }}</td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="ta-empty">No rows.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
