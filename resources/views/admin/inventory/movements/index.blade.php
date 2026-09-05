@extends('layouts.admin')
@section('title', 'Stock Movements')

@php
    $filtersOpen = request()->anyFilled(['q', 'date_from', 'date_to', 'type', 'from_location_id', 'to_location_id', 'user_id', 'category_id', 'direction', 'product_id']);
    $trendPill = function (?float $value): array {
        if ($value === null) {
            return ['—', 'bg-gray-100 text-gray-600'];
        }
        $label = ($value >= 0 ? '+' : '').number_format($value, 0).'%';
        $class = $value >= 0 ? 'bg-success-50 text-success-700' : 'bg-error-50 text-error-700';

        return [$label, $class];
    };
    $typePill = function (string $type): array {
        $label = str_replace('_', ' ', strtoupper($type));

        return match (strtoupper($type)) {
            'TRANSFER' => [$label, 'bg-brand-50 text-brand-800 dark:bg-brand-500/15 dark:text-brand-300'],
            'SALE' => [$label, 'bg-success-50 text-success-700 dark:bg-success-500/15 dark:text-success-400'],
            'PURCHASE', 'OPENING_STOCK' => [$label, 'bg-purple-50 text-purple-700 dark:bg-purple-500/15 dark:text-purple-300'],
            'ADJUSTMENT', 'DAMAGE', 'STOCK_COUNT' => [$label, 'bg-error-50 text-error-700 dark:bg-error-500/15 dark:text-error-400'],
            'RETURN' => [$label, 'bg-blue-light-50 text-blue-light-700 dark:bg-blue-light-500/15 dark:text-blue-light-300'],
            default => [$label, 'bg-gray-100 text-gray-600 dark:bg-white/5 dark:text-gray-300'],
        };
    };
    [$totalTrendLabel, $totalTrendClass] = $trendPill($stats['trend_total'] ?? null);
    [$incTrendLabel, $incTrendClass] = $trendPill($stats['trend_increases'] ?? null);
    [$decTrendLabel, $decTrendClass] = $trendPill($stats['trend_decreases'] ?? null);
@endphp

@section('page_header')
<div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
    <div class="min-w-0">
        <nav class="mb-2 flex flex-wrap items-center gap-1.5 text-sm text-gray-400">
            <a href="{{ route('admin.dashboard') }}" class="hover:text-brand-500">Dashboard</a>
            <span>/</span>
            <span>Stock &amp; Catalog</span>
            <span>/</span>
            <span class="text-gray-600 dark:text-gray-300">Stock Movements</span>
        </nav>
        <div class="flex items-start gap-3">
            <span class="mt-0.5 flex h-11 w-11 shrink-0 items-center justify-center rounded-full bg-brand-50 text-brand-700 dark:bg-brand-500/15 dark:text-brand-400">
                <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M7 16V4m0 0L3 8m4-4l4 4m6 0v12m0 0l4-4m-4 4l-4-4"/></svg>
            </span>
            <div class="min-w-0">
                <h1 class="text-xl font-bold text-gray-800 dark:text-white/90 sm:text-2xl">Stock Movements</h1>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Full inventory ledger: transfers, sales, purchases, adjustments, and opening stock.</p>
            </div>
        </div>
    </div>
    <div class="flex flex-wrap items-center gap-2">
        <a href="{{ route('admin.stock-adjustments.create') }}" class="ta-btn-outline w-full justify-center sm:w-auto">Adjust Stock</a>
        <a href="{{ route('admin.stock-transfers.create') }}" class="ta-btn w-full justify-center sm:w-auto">
            <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M8 7h12m0 0l-4-4m4 4l-4 4M16 17H4m0 0l4 4m-4-4l4-4"/></svg>
            Transfer Stock
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
                    <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4 7h16M4 12h16M4 17h10"/></svg>
                </div>
                <span class="rounded-full px-2 py-0.5 text-[10px] font-bold {{ $totalTrendClass }}">{{ $totalTrendLabel }}</span>
            </div>
            <p class="mt-3 text-[11px] font-semibold uppercase tracking-wide text-gray-500">Total Movements</p>
            <p class="mt-0.5 text-xl font-bold text-gray-800 dark:text-white/90 sm:text-2xl">{{ number_format($stats['total'] ?? 0) }}</p>
        </div>

        <div class="rounded-2xl border border-gray-200 bg-white p-3.5 shadow-theme-xs dark:border-gray-800 dark:bg-white/[0.03] sm:p-5">
            <div class="flex items-start justify-between gap-2">
                <div class="ta-kpi-icon is-success !h-10 !w-10 sm:!h-12 sm:!w-12">
                    <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 10l7-7m0 0l7 7m-7-7v18"/></svg>
                </div>
                <span class="rounded-full px-2 py-0.5 text-[10px] font-bold {{ $incTrendClass }}">{{ $incTrendLabel }}</span>
            </div>
            <p class="mt-3 text-[11px] font-semibold uppercase tracking-wide text-gray-500">Increases</p>
            <p class="mt-0.5 text-xl font-bold text-gray-800 dark:text-white/90 sm:text-2xl">{{ number_format($stats['increases'] ?? 0) }}</p>
        </div>

        <div class="rounded-2xl border border-gray-200 bg-white p-3.5 shadow-theme-xs dark:border-gray-800 dark:bg-white/[0.03] sm:p-5">
            <div class="flex items-start justify-between gap-2">
                <div class="ta-kpi-icon is-error !h-10 !w-10 sm:!h-12 sm:!w-12">
                    <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 14l-7 7m0 0l-7-7m7 7V4"/></svg>
                </div>
                <span class="rounded-full px-2 py-0.5 text-[10px] font-bold {{ $decTrendClass }}">{{ $decTrendLabel }}</span>
            </div>
            <p class="mt-3 text-[11px] font-semibold uppercase tracking-wide text-gray-500">Decreases</p>
            <p class="mt-0.5 text-xl font-bold text-gray-800 dark:text-white/90 sm:text-2xl">{{ number_format($stats['decreases'] ?? 0) }}</p>
        </div>

        <div class="rounded-2xl border border-gray-200 bg-white p-3.5 shadow-theme-xs dark:border-gray-800 dark:bg-white/[0.03] sm:p-5">
            <div class="flex items-start justify-between gap-2">
                <div class="ta-kpi-icon is-purple !h-10 !w-10 sm:!h-12 sm:!w-12">
                    <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </div>
                <span class="rounded-full bg-success-50 px-2 py-0.5 text-[10px] font-bold text-success-700">Live</span>
            </div>
            <p class="mt-3 text-[11px] font-semibold uppercase tracking-wide text-gray-500">Stock Value</p>
            <p class="mt-0.5 text-lg font-bold text-gray-800 dark:text-white/90 sm:text-2xl">KES {{ number_format($stats['stock_value'] ?? 0, 0) }}</p>
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
        <form method="GET" action="{{ route('admin.stock-movements.index') }}"
              class="grid grid-cols-1 gap-3 sm:grid-cols-2 xl:grid-cols-4"
              :class="filtersOpen ? 'grid' : 'hidden lg:grid'">
            <div class="ta-field sm:col-span-2">
                <label>Search</label>
                <input type="text" name="q" class="ta-input" value="{{ request('q') }}" placeholder="Product, SKU, barcode, reason…">
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
                <label>Type</label>
                <select name="type" class="ta-select">
                    <option value="">All types</option>
                    @foreach(['OPENING_STOCK','PURCHASE','TRANSFER','SALE','RETURN','ADJUSTMENT','DAMAGE','STOCK_COUNT'] as $type)
                        <option value="{{ $type }}" @selected(strtoupper((string) request('type')) === $type)>{{ str_replace('_', ' ', $type) }}</option>
                    @endforeach
                </select>
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
                <label>User</label>
                <select name="user_id" class="ta-select">
                    <option value="">Any user</option>
                    @foreach($users as $user)
                        <option value="{{ $user->id }}" @selected((string) request('user_id') === (string) $user->id)>{{ $user->name }}</option>
                    @endforeach
                </select>
            </div>
            @if($categories->isNotEmpty())
                <div class="ta-field">
                    <label>Category</label>
                    <select name="category_id" class="ta-select">
                        <option value="">Any category</option>
                        @foreach($categories as $cat)
                            <option value="{{ $cat->id }}" @selected((string) request('category_id') === (string) $cat->id)>{{ $cat->name }}</option>
                        @endforeach
                    </select>
                </div>
            @endif
            <div class="ta-field">
                <label>Direction</label>
                <select name="direction" class="ta-select">
                    <option value="">All</option>
                    <option value="in" @selected(request('direction') === 'in')>Increases (+)</option>
                    <option value="out" @selected(request('direction') === 'out')>Decreases (−)</option>
                </select>
            </div>
            <input type="hidden" name="per_page" value="{{ $perPage }}">
            <div class="flex flex-wrap items-end gap-2 sm:col-span-2 xl:col-span-4">
                <button type="submit" class="ta-btn">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 4h18l-7 8v6l-4 2v-8L3 4z"/></svg>
                    Filter
                </button>
                @if($filtersOpen)
                    <a href="{{ route('admin.stock-movements.index') }}" class="ta-btn-outline">Reset</a>
                @endif
            </div>
        </form>
    </div>

    {{-- Ledger --}}
    <div class="rounded-2xl border border-gray-200 bg-white shadow-theme-xs dark:border-gray-800 dark:bg-white/[0.03]">
        <div class="flex flex-wrap items-center justify-between gap-3 border-b border-gray-100 px-4 py-3 dark:border-gray-800 sm:px-5">
            <h2 class="text-sm font-semibold text-gray-800 dark:text-white/90 sm:text-base">Movement Ledger</h2>
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
            @forelse($movements as $m)
                @php
                    $thumb = $m->product?->primaryImageUrl();
                    [$typeLabel, $typeClass] = $typePill((string) $m->type);
                    $qty = (int) $m->quantity;
                    $userInitial = strtoupper(substr($m->user?->name ?? 'A', 0, 1));
                    $ref = $m->reference_type && $m->reference_id
                        ? class_basename($m->reference_type).' #'.$m->reference_id
                        : null;
                @endphp
                <div class="rounded-2xl border border-gray-200 bg-white p-3.5 shadow-theme-xs dark:border-gray-800 dark:bg-white/[0.02]">
                    <div class="flex items-start gap-3">
                        <div class="h-12 w-12 shrink-0 overflow-hidden rounded-xl border border-gray-100 bg-gray-50 dark:border-gray-800">
                            @if($thumb)
                                <img src="{{ $thumb }}" alt="" class="h-full w-full object-cover">
                            @else
                                <div class="flex h-full w-full items-center justify-center text-gray-300">
                                    <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                                </div>
                            @endif
                        </div>
                        <div class="min-w-0 flex-1">
                            <div class="flex items-start justify-between gap-2">
                                <div class="min-w-0">
                                    <p class="truncate text-sm font-bold text-gray-800 dark:text-white/90">{{ $m->product?->name ?: '—' }}</p>
                                    <p class="truncate text-xs text-gray-400">{{ $m->variant?->name ?: ($m->product?->sku ?: '—') }}</p>
                                </div>
                                <span class="shrink-0 text-sm font-bold {{ $qty < 0 ? 'text-error-600' : 'text-success-600' }}">
                                    {{ $qty > 0 ? '+' : '' }}{{ number_format($qty) }}
                                </span>
                            </div>
                            <div class="mt-2 flex flex-wrap items-center gap-2">
                                <span class="rounded-full px-2.5 py-0.5 text-[10px] font-bold uppercase tracking-wide {{ $typeClass }}">{{ $typeLabel }}</span>
                                <span class="text-[11px] text-gray-400">{{ $m->created_at?->format('d M Y · h:i A') }}</span>
                            </div>
                        </div>
                    </div>
                    <div class="mt-3 grid grid-cols-2 gap-2 border-t border-gray-100 pt-3 text-xs dark:border-gray-800">
                        <div>
                            <p class="text-gray-400">From</p>
                            <p class="font-medium text-gray-700 dark:text-gray-200">{{ $m->fromLocation?->name ?: '—' }}</p>
                        </div>
                        <div>
                            <p class="text-gray-400">To</p>
                            <p class="font-medium text-gray-700 dark:text-gray-200">{{ $m->toLocation?->name ?: ($m->location?->name ?: '—') }}</p>
                        </div>
                        <div>
                            <p class="text-gray-400">User</p>
                            <p class="inline-flex items-center gap-1.5 font-medium text-gray-700 dark:text-gray-200">
                                <span class="flex h-5 w-5 items-center justify-center rounded-full bg-brand-500 text-[9px] font-bold text-white">{{ $userInitial }}</span>
                                {{ $m->user?->name ?: '—' }}
                            </p>
                        </div>
                        <div>
                            <p class="text-gray-400">Reason</p>
                            <p class="truncate font-medium text-gray-700 dark:text-gray-200" title="{{ $m->reason }}">{{ $m->reason ?: '—' }}</p>
                        </div>
                        @if($ref)
                            <div class="col-span-2">
                                <p class="text-gray-400">Reference</p>
                                <p class="font-medium text-gray-700 dark:text-gray-200">{{ $ref }}</p>
                            </div>
                        @endif
                    </div>
                </div>
            @empty
                <div class="rounded-2xl border border-dashed border-gray-200 px-4 py-10 text-center text-sm text-gray-400 dark:border-gray-800">
                    No movements found.
                </div>
            @endforelse
        </div>

        {{-- Desktop table --}}
        <div class="ta-table-wrap hidden lg:block">
            <table class="ta-table" style="min-width:1100px;">
                <thead>
                    <tr>
                        <th style="width:48px;">#</th>
                        <th>Date &amp; Time</th>
                        <th>Product</th>
                        <th>Type</th>
                        <th>From</th>
                        <th>To</th>
                        <th class="text-right">Qty</th>
                        <th>User</th>
                        <th>Reason</th>
                        <th>Reference</th>
                    </tr>
                </thead>
                <tbody>
                @forelse($movements as $index => $m)
                    @php
                        $thumb = $m->product?->primaryImageUrl();
                        [$typeLabel, $typeClass] = $typePill((string) $m->type);
                        $qty = (int) $m->quantity;
                        $userInitial = strtoupper(substr($m->user?->name ?? 'A', 0, 1));
                        $ref = $m->reference_type && $m->reference_id
                            ? class_basename($m->reference_type).' #'.$m->reference_id
                            : '—';
                    @endphp
                    <tr>
                        <td class="text-gray-400">{{ $movements->firstItem() + $index }}</td>
                        <td class="whitespace-nowrap">
                            <div>{{ $m->created_at?->format('d M Y') }}</div>
                            <div class="ta-muted">{{ $m->created_at?->format('h:i A') }}</div>
                        </td>
                        <td>
                            <div class="flex items-center gap-3">
                                <div class="h-10 w-10 flex-shrink-0 overflow-hidden rounded-lg border border-gray-100 bg-gray-50 dark:border-gray-800">
                                    @if($thumb)
                                        <img src="{{ $thumb }}" alt="" class="h-full w-full object-cover">
                                    @else
                                        <div class="flex h-full w-full items-center justify-center text-gray-300">
                                            <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                                        </div>
                                    @endif
                                </div>
                                <div class="min-w-0">
                                    <div class="ta-name truncate">{{ $m->product?->name ?: '—' }}</div>
                                    <div class="ta-muted truncate">{{ $m->variant?->name ?: ($m->product?->sku ?: '—') }}</div>
                                </div>
                            </div>
                        </td>
                        <td><span class="rounded-full px-2.5 py-1 text-[11px] font-bold uppercase tracking-wide {{ $typeClass }}">{{ $typeLabel }}</span></td>
                        <td>{{ $m->fromLocation?->name ?: '—' }}</td>
                        <td>{{ $m->toLocation?->name ?: ($m->location?->name ?: '—') }}</td>
                        <td class="text-right font-semibold {{ $qty < 0 ? 'text-error-600' : 'text-success-600' }}">
                            {{ $qty > 0 ? '+' : '' }}{{ number_format($qty) }}
                        </td>
                        <td>
                            <span class="inline-flex items-center gap-2">
                                <span class="flex h-7 w-7 items-center justify-center rounded-full bg-brand-500 text-[11px] font-bold text-white">{{ $userInitial }}</span>
                                {{ $m->user?->name ?: '—' }}
                            </span>
                        </td>
                        <td>
                            <div class="max-w-[180px] truncate" title="{{ $m->reason }}">{{ $m->reason ?: '—' }}</div>
                        </td>
                        <td class="ta-muted whitespace-nowrap">{{ $ref }}</td>
                    </tr>
                @empty
                    <tr><td colspan="10" class="ta-empty">No movements found.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>

        <div class="flex flex-col gap-3 border-t border-gray-100 px-4 py-3 dark:border-gray-800 sm:flex-row sm:items-center sm:justify-between sm:px-5">
            <span class="ta-muted">Showing {{ $movements->firstItem() ?? 0 }} to {{ $movements->lastItem() ?? 0 }} of {{ $movements->total() }}</span>
            {{ $movements->links() }}
        </div>
    </div>
</div>
@endsection
