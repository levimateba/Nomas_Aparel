@extends('layouts.admin')
@section('title', 'Stock Overview')

@section('page_header')
<div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
    <div>
        <nav class="mb-2 flex flex-wrap items-center gap-1.5 text-sm text-gray-400">
            <a href="{{ route('admin.dashboard') }}" class="hover:text-brand-500">Dashboard</a>
            <span>/</span>
            <span>Stock &amp; Catalog</span>
            <span>/</span>
            <span class="text-gray-600 dark:text-gray-300">Stock Overview</span>
        </nav>
        <h1 class="text-2xl font-bold text-gray-800 dark:text-white/90">Stock Overview</h1>
        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Main Store + Shop Floor = total stock across active locations.</p>
    </div>
    <div class="flex flex-wrap items-center gap-2">
        <a href="{{ route('admin.stock-adjustments.create') }}" class="ta-btn-outline">
            <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
            Adjust Stock
        </a>
        <a href="{{ route('admin.stock-transfers.create') }}" class="ta-btn">
            <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M8 7h12m0 0l-4-4m4 4l-4 4M16 17H4m0 0l4 4m-4-4l4-4"/></svg>
            Transfer Stock
        </a>
    </div>
</div>
@endsection

@section('content')
@php
    $mainId = $mainStore?->id;
    $shopId = $shop?->id;
@endphp

<div class="ta-page">
    {{-- KPIs --}}
    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4">
        <div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-theme-xs dark:border-gray-800 dark:bg-white/[0.03]">
            <div class="flex items-start justify-between gap-3">
                <div class="ta-kpi-icon is-brand">
                    <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                </div>
                <div data-sparkline='@json($stats['sparklines']['total'] ?? [])' data-color="#a58112"></div>
            </div>
            <p class="mt-4 text-xs font-semibold uppercase tracking-wide text-gray-500">Total Stock</p>
            <p class="mt-1 text-2xl font-bold text-gray-800 dark:text-white/90">{{ number_format($stats['total']) }}</p>
            <div class="mt-2 flex items-center justify-between text-xs">
                <span class="text-gray-400">{{ number_format($stats['products']) }} active products</span>
                <span class="font-semibold text-brand-600">Units</span>
            </div>
        </div>

        <a href="{{ route('admin.stock-overview.index', ['location_id' => $mainId]) }}" class="rounded-2xl border border-gray-200 bg-white p-5 no-underline shadow-theme-xs dark:border-gray-800 dark:bg-white/[0.03]">
            <div class="flex items-start justify-between gap-3">
                <div class="ta-kpi-icon is-blue">
                    <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 21h18M5 21V7l7-4 7 4v14M9 21v-6h6v6"/></svg>
                </div>
                <div data-sparkline='@json($stats['sparklines']['main'] ?? [])' data-color="#465FFF"></div>
            </div>
            <p class="mt-4 text-xs font-semibold uppercase tracking-wide text-gray-500">{{ $mainStore?->name ?? 'Main Store' }}</p>
            <p class="mt-1 text-2xl font-bold text-gray-800 dark:text-white/90">{{ number_format($stats['main']) }}</p>
            <div class="mt-2 flex items-center justify-between text-xs">
                <span class="text-gray-400">Warehouse / store</span>
                <span class="font-semibold text-blue-light-600">View</span>
            </div>
        </a>

        <a href="{{ route('admin.stock-overview.index', ['location_id' => $shopId]) }}" class="rounded-2xl border border-gray-200 bg-white p-5 no-underline shadow-theme-xs dark:border-gray-800 dark:bg-white/[0.03]">
            <div class="flex items-start justify-between gap-3">
                <div class="ta-kpi-icon is-success">
                    <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 10.5l9-7 9 7V20a1 1 0 01-1 1h-5v-6H9v6H4a1 1 0 01-1-1v-9.5z"/></svg>
                </div>
                <div data-sparkline='@json($stats['sparklines']['shop'] ?? [])' data-color="#12B76A"></div>
            </div>
            <p class="mt-4 text-xs font-semibold uppercase tracking-wide text-gray-500">{{ $shop?->name ?? 'Shop Floor' }}</p>
            <p class="mt-1 text-2xl font-bold text-gray-800 dark:text-white/90">{{ number_format($stats['shop']) }}</p>
            <div class="mt-2 flex items-center justify-between text-xs">
                <span class="text-gray-400">POS selling floor</span>
                <span class="font-semibold text-success-600">View</span>
            </div>
        </a>

        <a href="{{ route('admin.stock-overview.index', ['stock' => 'low']) }}" class="rounded-2xl border border-gray-200 bg-white p-5 no-underline shadow-theme-xs dark:border-gray-800 dark:bg-white/[0.03]">
            <div class="flex items-start justify-between gap-3">
                <div class="ta-kpi-icon is-warning">
                    <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/></svg>
                </div>
                <div data-sparkline='@json($stats['sparklines']['alerts'] ?? [])' data-color="#F79009"></div>
            </div>
            <p class="mt-4 text-xs font-semibold uppercase tracking-wide text-gray-500">Low / Out</p>
            <p class="mt-1 text-2xl font-bold text-gray-800 dark:text-white/90">{{ number_format($stats['low'] + $stats['out']) }}</p>
            <div class="mt-2 flex items-center justify-between text-xs">
                <span class="text-gray-400">{{ $stats['low'] }} low · {{ $stats['out'] }} out</span>
                <span class="font-semibold {{ ($stats['low'] + $stats['out']) > 0 ? 'text-warning-600' : 'text-success-600' }}">
                    {{ ($stats['low'] + $stats['out']) > 0 ? 'Alert' : 'OK' }}
                </span>
            </div>
        </a>
    </div>

    @if($lowShop->isNotEmpty())
        <div class="rounded-2xl border border-warning-200 bg-warning-50/60 p-4 dark:border-warning-500/30 dark:bg-warning-500/10">
            <div class="mb-3 flex flex-wrap items-center justify-between gap-2">
                <div>
                    <h3 class="text-sm font-semibold text-gray-800 dark:text-white/90">Low Shop Floor Stock</h3>
                    <p class="text-xs text-gray-500">Replenish from Main Store before sell-outs.</p>
                </div>
                <a href="{{ route('admin.stock-overview.index', ['location_id' => $shopId, 'stock' => 'low']) }}" class="ta-btn-outline ta-btn-sm">View all</a>
            </div>
            <div class="grid gap-2 sm:grid-cols-2 xl:grid-cols-4">
                @foreach($lowShop as $row)
                    <div class="flex items-center justify-between gap-3 rounded-xl border border-white/80 bg-white px-3 py-2.5 dark:border-gray-800 dark:bg-gray-900">
                        <div class="min-w-0">
                            <p class="truncate text-sm font-semibold text-gray-800 dark:text-white/90">{{ $row->product?->name }}</p>
                            <p class="ta-muted">{{ $row->quantity }} / min {{ $row->reorder_level }}</p>
                        </div>
                        <a class="ta-btn-outline ta-btn-sm whitespace-nowrap" href="{{ route('admin.stock-transfers.create', ['from_location_id' => $mainId, 'to_location_id' => $shopId, 'product_id' => $row->product_id, 'product_variant_id' => $row->product_variant_id ?: null]) }}">Transfer</a>
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    {{-- Filters --}}
    <form method="GET" action="{{ route('admin.stock-overview.index') }}" class="ta-toolbar">
        <div class="ta-field" style="flex:2;min-width:220px;">
            <label>Search</label>
            <input type="text" name="q" class="ta-input" value="{{ request('q') }}" placeholder="Search by name, SKU, or barcode…">
        </div>
        <div class="ta-field" style="max-width:180px;">
            <label>Location</label>
            <select name="location_id" class="ta-select">
                <option value="">All locations</option>
                @foreach($locations as $loc)
                    <option value="{{ $loc->id }}" @selected((string) request('location_id') === (string) $loc->id)>{{ $loc->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="ta-field" style="max-width:160px;">
            <label>Stock Status</label>
            <select name="stock" class="ta-select">
                <option value="">All</option>
                <option value="in" @selected(request('stock') === 'in')>In stock</option>
                <option value="low" @selected(request('stock') === 'low')>Low stock</option>
                <option value="out" @selected(request('stock') === 'out')>Out of stock</option>
            </select>
        </div>
        <input type="hidden" name="per_page" value="{{ $perPage }}">
        <button type="submit" class="ta-btn">
            <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"/></svg>
            Filter
        </button>
        @if(request()->anyFilled(['q', 'location_id', 'stock']))
            <a href="{{ route('admin.stock-overview.index') }}" class="ta-btn-outline">Reset</a>
        @endif
    </form>

    {{-- Table --}}
    <div class="ta-table-card">
        <div class="ta-bulk">
            <div class="text-sm font-medium text-gray-700 dark:text-gray-300">Inventory rows</div>
            <div class="ml-auto flex items-center gap-2 text-sm text-gray-500">
                <span>Show</span>
                <select class="ta-select" style="max-width:90px;padding-top:6px;padding-bottom:6px;" onchange="const u=new URL(window.location.href);u.searchParams.set('per_page',this.value);window.location=u;">
                    @foreach([10, 20, 50, 100] as $n)
                        <option value="{{ $n }}" @selected($perPage === $n)>{{ $n }}</option>
                    @endforeach
                </select>
                <span>per page</span>
            </div>
        </div>

        <div class="ta-table-wrap">
            <table class="ta-table" style="min-width:1000px;">
                <thead>
                    <tr>
                        <th style="width:48px;">#</th>
                        <th>Product</th>
                        <th>SKU / Barcode</th>
                        <th>Brand</th>
                        <th>Location</th>
                        <th class="text-right">Stock</th>
                        <th class="text-right">Reorder</th>
                        <th>Status</th>
                        <th class="text-right">Actions</th>
                    </tr>
                </thead>
                <tbody>
                @forelse($rows as $index => $row)
                    @php
                        $product = $row->product;
                        $thumb = $product?->primaryImageUrl();
                        $sku = $row->variant?->sku ?: $product?->sku;
                        $barcode = $row->variant?->barcode ?: $product?->barcode;
                        if ($row->isOut()) {
                            $pill = 'ta-pill-error';
                            $label = 'Out of Stock';
                        } elseif ($row->isLow()) {
                            $pill = 'ta-pill-warning';
                            $label = 'Low Stock';
                        } else {
                            $pill = 'ta-pill-success';
                            $label = 'In Stock';
                        }
                        $desc = \Illuminate\Support\Str::limit(strip_tags((string) ($row->variant?->name ?: $product?->category?->name ?: '—')), 40);
                    @endphp
                    <tr>
                        <td class="text-gray-400">{{ $rows->firstItem() + $index }}</td>
                        <td>
                            <div class="flex items-center gap-3">
                                <div class="h-11 w-11 flex-shrink-0 overflow-hidden rounded-lg border border-gray-100 bg-gray-50 dark:border-gray-800">
                                    @if($thumb)
                                        <img src="{{ $thumb }}" alt="" class="h-full w-full object-cover">
                                    @else
                                        <div class="flex h-full w-full items-center justify-center text-gray-300">
                                            <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                                        </div>
                                    @endif
                                </div>
                                <div class="min-w-0">
                                    <div class="ta-name truncate">{{ $product?->name ?: '—' }}</div>
                                    <div class="ta-muted truncate">{{ $desc }}</div>
                                </div>
                            </div>
                        </td>
                        <td>
                            <div class="font-medium text-gray-800 dark:text-white/90">{{ $sku ?: '—' }}</div>
                            <div class="ta-muted">{{ $barcode ?: 'No barcode' }}</div>
                        </td>
                        <td>{{ $product?->brand?->name ?: '—' }}</td>
                        <td>
                            <span class="ta-pill ta-pill-muted">{{ $row->location?->name }}</span>
                        </td>
                        <td class="text-right font-semibold text-gray-800 dark:text-white/90">{{ number_format($row->quantity) }}</td>
                        <td class="text-right text-gray-500">{{ number_format($row->reorder_level) }}</td>
                        <td><span class="ta-pill {{ $pill }}">{{ $label }}</span></td>
                        <td>
                            <div class="flex items-center justify-end gap-1">
                                @if($product)
                                    <a href="{{ route('admin.products.edit', $product) }}" class="inline-flex h-8 w-8 items-center justify-center rounded-lg text-gray-500 hover:bg-gray-100 hover:text-brand-600 dark:hover:bg-white/5" title="Edit product">
                                        <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                    </a>
                                @endif
                                <a href="{{ route('admin.stock-transfers.create', ['product_id' => $row->product_id, 'product_variant_id' => $row->product_variant_id ?: null, 'from_location_id' => $row->stock_location_id]) }}" class="inline-flex h-8 w-8 items-center justify-center rounded-lg text-gray-500 hover:bg-gray-100 hover:text-brand-600 dark:hover:bg-white/5" title="Transfer">
                                    <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M8 7h12m0 0l-4-4m4 4l-4 4M16 17H4m0 0l4 4m-4-4l4-4"/></svg>
                                </a>
                                <a href="{{ route('admin.stock-adjustments.create', ['product_id' => $row->product_id, 'stock_location_id' => $row->stock_location_id, 'product_variant_id' => $row->product_variant_id ?: null]) }}" class="inline-flex h-8 w-8 items-center justify-center rounded-lg text-gray-500 hover:bg-gray-100 hover:text-brand-600 dark:hover:bg-white/5" title="Adjust">
                                    <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
                                </a>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="9" class="ta-empty">No inventory rows found.</td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>

        <div class="ta-table-footer">
            <span class="ta-muted">
                Showing {{ $rows->firstItem() ?? 0 }} to {{ $rows->lastItem() ?? 0 }} of {{ $rows->total() }} rows
            </span>
            {{ $rows->links() }}
        </div>
    </div>

    @if(!empty($onlineAvailableHint))
        <p class="ta-muted">{{ $onlineAvailableHint }}</p>
    @endif
</div>
@endsection
