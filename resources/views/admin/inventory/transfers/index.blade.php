@extends('layouts.admin')
@section('title', 'Stock Transfers')

@php
    $filtersOpen = request()->anyFilled(['q', 'date_from', 'date_to', 'from_location_id', 'to_location_id', 'status']);
    $trendLabel = isset($stats['trend']) && $stats['trend'] !== null
        ? (($stats['trend'] >= 0 ? '↑ ' : '↓ ').number_format(abs($stats['trend']), 0).'%')
        : '—';
    $statusPill = function (string $status): array {
        return match (strtolower($status)) {
            'completed' => ['Completed', 'bg-success-50 text-success-700 dark:bg-success-500/15 dark:text-success-400'],
            'cancelled' => ['Cancelled', 'bg-error-50 text-error-700 dark:bg-error-500/15 dark:text-error-400'],
            'pending', 'draft' => [ucfirst($status), 'bg-warning-50 text-warning-700 dark:bg-warning-500/15 dark:text-warning-400'],
            'approved' => ['Approved', 'bg-blue-light-50 text-blue-light-700 dark:bg-blue-light-500/15 dark:text-blue-light-400'],
            default => [ucfirst($status), 'bg-gray-100 text-gray-600 dark:bg-white/5 dark:text-gray-300'],
        };
    };
@endphp

@section('page_header')
<div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
    <div class="min-w-0">
        <nav class="mb-2 flex flex-wrap items-center gap-1.5 text-sm text-gray-400">
            <a href="{{ route('admin.dashboard') }}" class="hover:text-brand-500">Dashboard</a>
            <span>/</span>
            <span>Stock &amp; Catalog</span>
            <span>/</span>
            <span class="text-gray-600 dark:text-gray-300">Stock Transfers</span>
        </nav>
        <div class="flex items-start gap-3">
            <span class="mt-0.5 flex h-11 w-11 shrink-0 items-center justify-center rounded-full bg-brand-50 text-brand-700 dark:bg-brand-500/15 dark:text-brand-400">
                <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M8 7h12m0 0l-4-4m4 4l-4 4M16 17H4m0 0l4 4m-4-4l4-4"/></svg>
            </span>
            <div class="min-w-0">
                <h1 class="text-xl font-bold text-gray-800 dark:text-white/90 sm:text-2xl">Stock Transfers</h1>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Move stock between locations without changing total inventory.</p>
            </div>
            <div class="ml-auto hidden shrink-0 sm:block lg:hidden xl:block" aria-hidden="true">
                <svg class="h-14 w-20 text-brand-500/70" viewBox="0 0 80 56" fill="none">
                    <rect x="6" y="18" width="24" height="22" rx="3" fill="currentColor" opacity=".35"/>
                    <rect x="50" y="18" width="24" height="22" rx="3" fill="currentColor" opacity=".55"/>
                    <path d="M34 28h12" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"/>
                    <path d="M42 24l4 4-4 4" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"/>
                    <path d="M12 14h12l2 4H10l2-4zM56 14h12l2 4H54l2-4z" fill="currentColor" opacity=".8"/>
                </svg>
            </div>
        </div>
    </div>
    <div class="flex flex-wrap items-center gap-2">
        <a href="{{ route('admin.stock-overview.index') }}" class="ta-btn-outline w-full justify-center sm:w-auto">Stock Overview</a>
        <a href="{{ route('admin.stock-transfers.create') }}" class="ta-btn w-full justify-center sm:w-auto">
            <span class="text-lg leading-none">+</span> New Transfer
        </a>
    </div>
</div>
@endsection

@section('content')
<div class="space-y-5 pb-20 lg:pb-0" x-data="{ filtersOpen: {{ $filtersOpen ? 'true' : 'false' }} }">
    {{-- KPI cards --}}
    <div class="grid grid-cols-2 gap-3 xl:grid-cols-4">
        <div class="rounded-2xl border border-gray-200 bg-white p-3.5 shadow-theme-xs dark:border-gray-800 dark:bg-white/[0.03] sm:p-5">
            <div class="flex items-start justify-between gap-2">
                <div class="ta-kpi-icon is-blue !h-10 !w-10 sm:!h-12 sm:!w-12">
                    <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/></svg>
                </div>
                <span class="rounded-full bg-success-50 px-2 py-0.5 text-[10px] font-bold text-success-700">{{ $trendLabel }}</span>
            </div>
            <p class="mt-3 text-[11px] font-semibold uppercase tracking-wide text-gray-500">Total Transfers</p>
            <p class="mt-0.5 text-xl font-bold text-gray-800 dark:text-white/90 sm:text-2xl">{{ number_format($stats['total'] ?? 0) }}</p>
            <p class="mt-1 text-[11px] text-gray-400">All time transfers</p>
        </div>

        <div class="rounded-2xl border border-gray-200 bg-white p-3.5 shadow-theme-xs dark:border-gray-800 dark:bg-white/[0.03] sm:p-5">
            <div class="flex items-start justify-between gap-2">
                <div class="ta-kpi-icon is-success !h-10 !w-10 sm:!h-12 sm:!w-12">
                    <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                </div>
                <span class="rounded-full bg-success-50 px-2 py-0.5 text-[10px] font-bold text-success-700">{{ ($stats['completed_pct'] ?? 0) }}%</span>
            </div>
            <p class="mt-3 text-[11px] font-semibold uppercase tracking-wide text-gray-500">Completed</p>
            <p class="mt-0.5 text-xl font-bold text-gray-800 dark:text-white/90 sm:text-2xl">{{ number_format($stats['completed'] ?? 0) }}</p>
            <p class="mt-1 text-[11px] text-gray-400">Successfully completed</p>
        </div>

        <div class="rounded-2xl border border-gray-200 bg-white p-3.5 shadow-theme-xs dark:border-gray-800 dark:bg-white/[0.03] sm:p-5">
            <div class="flex items-start justify-between gap-2">
                <div class="ta-kpi-icon is-warning !h-10 !w-10 sm:!h-12 sm:!w-12">
                    <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="12" cy="12" r="9"/><path stroke-linecap="round" d="M12 7v5l3 2"/></svg>
                </div>
                <span class="rounded-full bg-gray-100 px-2 py-0.5 text-[10px] font-bold text-gray-600">{{ ($stats['pending_pct'] ?? 0) }}%</span>
            </div>
            <p class="mt-3 text-[11px] font-semibold uppercase tracking-wide text-gray-500">Pending</p>
            <p class="mt-0.5 text-xl font-bold text-gray-800 dark:text-white/90 sm:text-2xl">{{ number_format($stats['pending'] ?? 0) }}</p>
            <p class="mt-1 text-[11px] text-gray-400">Awaiting completion</p>
        </div>

        <div class="rounded-2xl border border-gray-200 bg-white p-3.5 shadow-theme-xs dark:border-gray-800 dark:bg-white/[0.03] sm:p-5">
            <div class="flex items-start justify-between gap-2">
                <div class="ta-kpi-icon is-error !h-10 !w-10 sm:!h-12 sm:!w-12">
                    <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                </div>
                <span class="rounded-full bg-gray-100 px-2 py-0.5 text-[10px] font-bold text-gray-600">{{ ($stats['cancelled_pct'] ?? 0) }}%</span>
            </div>
            <p class="mt-3 text-[11px] font-semibold uppercase tracking-wide text-gray-500">Cancelled</p>
            <p class="mt-0.5 text-xl font-bold text-gray-800 dark:text-white/90 sm:text-2xl">{{ number_format($stats['cancelled'] ?? 0) }}</p>
            <p class="mt-1 text-[11px] text-gray-400">Cancelled transfers</p>
        </div>
    </div>

    {{-- Filters --}}
    <div class="rounded-2xl border border-gray-200 bg-white p-3 shadow-theme-xs dark:border-gray-800 dark:bg-white/[0.03] sm:p-4">
        <div class="mb-3 flex items-center justify-between lg:hidden">
            <p class="text-sm font-semibold text-gray-800 dark:text-white/90">Filters</p>
            <button type="button" @click="filtersOpen = !filtersOpen" class="ta-btn-outline ta-btn-sm">
                <span x-text="filtersOpen ? 'Hide' : 'Show'"></span>
            </button>
        </div>
        <form method="GET" action="{{ route('admin.stock-transfers.index') }}"
              class="grid grid-cols-1 gap-3 sm:grid-cols-2 xl:grid-cols-6"
              :class="filtersOpen ? 'grid' : 'hidden lg:grid'">
            <div class="ta-field xl:col-span-2">
                <label>Search</label>
                <input type="text" name="q" class="ta-input" value="{{ request('q') }}" placeholder="Transfer no., product, SKU, reason…">
            </div>
            <div class="ta-field">
                <label>From date</label>
                <input type="date" name="date_from" class="ta-input" value="{{ request('date_from') }}">
            </div>
            <div class="ta-field">
                <label>To date</label>
                <input type="date" name="date_to" class="ta-input" value="{{ request('date_to') }}">
            </div>
            <div class="ta-field">
                <label>From location</label>
                <select name="from_location_id" class="ta-select">
                    <option value="">Any</option>
                    @foreach($locations as $loc)
                        <option value="{{ $loc->id }}" @selected((string) request('from_location_id') === (string) $loc->id)>{{ $loc->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="ta-field">
                <label>To location</label>
                <select name="to_location_id" class="ta-select">
                    <option value="">Any</option>
                    @foreach($locations as $loc)
                        <option value="{{ $loc->id }}" @selected((string) request('to_location_id') === (string) $loc->id)>{{ $loc->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="ta-field">
                <label>Status</label>
                <select name="status" class="ta-select">
                    <option value="">All statuses</option>
                    @foreach(['completed', 'pending', 'draft', 'approved', 'cancelled'] as $st)
                        <option value="{{ $st }}" @selected(strtolower((string) request('status')) === $st)>{{ ucfirst($st) }}</option>
                    @endforeach
                </select>
            </div>
            <input type="hidden" name="per_page" value="{{ $perPage }}">
            <div class="flex flex-wrap items-end gap-2 sm:col-span-2 xl:col-span-6">
                <button type="submit" class="ta-btn">Filter</button>
                @if($filtersOpen)
                    <a href="{{ route('admin.stock-transfers.index') }}" class="ta-btn-outline">Reset</a>
                @endif
            </div>
        </form>
    </div>

    {{-- History --}}
    <div class="rounded-2xl border border-gray-200 bg-white shadow-theme-xs dark:border-gray-800 dark:bg-white/[0.03]">
        <div class="flex flex-wrap items-center justify-between gap-3 border-b border-gray-100 px-4 py-3 dark:border-gray-800 sm:px-5">
            <h2 class="text-sm font-semibold text-gray-800 dark:text-white/90 sm:text-base">Transfer History</h2>
            <div class="flex items-center gap-2 text-sm text-gray-500">
                <span class="hidden sm:inline">Show</span>
                <select class="ta-select !py-1.5" style="max-width:90px;" onchange="const u=new URL(window.location.href);u.searchParams.set('per_page',this.value);window.location=u;">
                    @foreach([10, 20, 50, 100] as $n)
                        <option value="{{ $n }}" @selected($perPage === $n)>{{ $n }}</option>
                    @endforeach
                </select>
                <span class="hidden sm:inline">per page</span>
            </div>
        </div>

        {{-- Mobile cards --}}
        <div class="space-y-3 p-3 lg:hidden">
            @forelse($transfers as $transfer)
                @php
                    [$statusLabel, $statusClass] = $statusPill($transfer->status ?? 'completed');
                    $userInitial = strtoupper(substr($transfer->user?->name ?? 'A', 0, 1));
                    $itemCount = $transfer->items->count();
                    $qty = $transfer->items->sum('quantity');
                @endphp
                <a href="{{ route('admin.stock-transfers.show', $transfer) }}"
                   class="block rounded-2xl border border-gray-200 bg-white p-3.5 no-underline shadow-theme-xs dark:border-gray-800 dark:bg-white/[0.02]">
                    <div class="flex items-start justify-between gap-2">
                        <div class="min-w-0">
                            <p class="truncate text-sm font-bold text-gray-800 dark:text-white/90">{{ $transfer->transfer_number }}</p>
                            <p class="truncate text-xs text-gray-400">{{ $transfer->reason ?: 'Transfer' }}</p>
                        </div>
                        <span class="inline-flex shrink-0 items-center gap-1.5 rounded-full px-2.5 py-1 text-[11px] font-semibold {{ $statusClass }}">
                            <span class="h-1.5 w-1.5 rounded-full bg-current"></span>
                            {{ $statusLabel }}
                        </span>
                    </div>
                    <p class="mt-2 text-xs text-gray-500">{{ $transfer->created_at?->format('d M Y · h:i A') }}</p>
                    <div class="mt-3 flex items-center gap-2 text-sm">
                        <span class="inline-flex min-w-0 items-center gap-1.5 truncate rounded-lg bg-blue-light-50 px-2 py-1 text-xs font-medium text-blue-light-700 dark:bg-blue-light-500/10 dark:text-blue-light-300">
                            <svg class="h-3.5 w-3.5 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 21h18M5 21V7l7-4 7 4v14"/></svg>
                            <span class="truncate">{{ $transfer->fromLocation?->name ?: '—' }}</span>
                        </span>
                        <svg class="h-4 w-4 shrink-0 text-brand-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M13 7l5 5m0 0l-5 5m5-5H6"/></svg>
                        <span class="inline-flex min-w-0 items-center gap-1.5 truncate rounded-lg bg-brand-50 px-2 py-1 text-xs font-medium text-brand-800 dark:bg-brand-500/10 dark:text-brand-300">
                            <svg class="h-3.5 w-3.5 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 10.5l9-7 9 7V20a1 1 0 01-1 1h-5v-6H9v6H4a1 1 0 01-1-1v-9.5z"/></svg>
                            <span class="truncate">{{ $transfer->toLocation?->name ?: '—' }}</span>
                        </span>
                    </div>
                    <div class="mt-3 flex items-center justify-between border-t border-gray-100 pt-3 text-xs text-gray-500 dark:border-gray-800">
                        <span>{{ $itemCount }} item{{ $itemCount === 1 ? '' : 's' }} · Qty {{ number_format($qty) }}</span>
                        <span class="inline-flex items-center gap-1.5">
                            <span class="flex h-6 w-6 items-center justify-center rounded-full bg-brand-500 text-[10px] font-bold text-white">{{ $userInitial }}</span>
                            {{ $transfer->user?->name ?: '—' }}
                        </span>
                    </div>
                </a>
            @empty
                <div class="rounded-2xl border border-dashed border-gray-200 px-4 py-10 text-center text-sm text-gray-400 dark:border-gray-800">
                    No transfers yet.
                </div>
            @endforelse
        </div>

        {{-- Desktop table --}}
        <div class="ta-table-wrap hidden lg:block">
            <table class="ta-table" style="min-width:960px;">
                <thead>
                    <tr>
                        <th style="width:48px;">#</th>
                        <th>Transfer No.</th>
                        <th>Date &amp; Time</th>
                        <th>From / To</th>
                        <th class="text-right">Items</th>
                        <th class="text-right">Qty</th>
                        <th>User</th>
                        <th>Status</th>
                        <th class="text-right">Actions</th>
                    </tr>
                </thead>
                <tbody>
                @forelse($transfers as $index => $transfer)
                    @php
                        [$statusLabel, $statusClass] = $statusPill($transfer->status ?? 'completed');
                        $userInitial = strtoupper(substr($transfer->user?->name ?? 'A', 0, 1));
                    @endphp
                    <tr>
                        <td class="text-gray-400">{{ $transfers->firstItem() + $index }}</td>
                        <td>
                            <a href="{{ route('admin.stock-transfers.show', $transfer) }}" class="ta-name hover:text-brand-600">{{ $transfer->transfer_number }}</a>
                            @if($transfer->reason)
                                <div class="ta-muted">{{ $transfer->reason }}</div>
                            @endif
                        </td>
                        <td class="whitespace-nowrap">
                            <div>{{ $transfer->created_at?->format('d M Y') }}</div>
                            <div class="ta-muted">{{ $transfer->created_at?->format('h:i A') }}</div>
                        </td>
                        <td>
                            <div class="flex flex-col gap-1.5">
                                <span class="inline-flex items-center gap-1.5 text-sm text-gray-700 dark:text-gray-200">
                                    <svg class="h-3.5 w-3.5 text-blue-light-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 21h18M5 21V7l7-4 7 4v14"/></svg>
                                    {{ $transfer->fromLocation?->name ?: '—' }}
                                </span>
                                <span class="inline-flex items-center gap-1.5 text-sm text-gray-700 dark:text-gray-200">
                                    <svg class="h-3.5 w-3.5 text-brand-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M13 7l5 5m0 0l-5 5m5-5H6"/></svg>
                                    {{ $transfer->toLocation?->name ?: '—' }}
                                </span>
                            </div>
                        </td>
                        <td class="text-right">{{ $transfer->items->count() }}</td>
                        <td class="text-right font-semibold text-gray-800 dark:text-white/90">{{ number_format($transfer->items->sum('quantity')) }}</td>
                        <td>
                            <span class="inline-flex items-center gap-2">
                                <span class="flex h-7 w-7 items-center justify-center rounded-full bg-brand-500 text-[11px] font-bold text-white">{{ $userInitial }}</span>
                                {{ $transfer->user?->name ?: '—' }}
                            </span>
                        </td>
                        <td>
                            <span class="inline-flex items-center gap-1.5 rounded-full px-2.5 py-1 text-xs font-semibold {{ $statusClass }}">
                                <span class="h-1.5 w-1.5 rounded-full bg-current"></span>
                                {{ $statusLabel }}
                            </span>
                        </td>
                        <td class="text-right">
                            <a href="{{ route('admin.stock-transfers.show', $transfer) }}" class="inline-flex h-8 w-8 items-center justify-center rounded-lg text-gray-500 hover:bg-gray-100 hover:text-brand-600 dark:hover:bg-white/5" title="View">
                                <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="9" class="ta-empty">No transfers yet.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>

        <div class="flex flex-col gap-3 border-t border-gray-100 px-4 py-3 dark:border-gray-800 sm:flex-row sm:items-center sm:justify-between sm:px-5">
            <span class="ta-muted">Showing {{ $transfers->firstItem() ?? 0 }} to {{ $transfers->lastItem() ?? 0 }} of {{ $transfers->total() }}</span>
            {{ $transfers->links() }}
        </div>
    </div>
</div>
@endsection
