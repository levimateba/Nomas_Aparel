@extends('layouts.admin')
@section('title', 'Stock Valuation')

@php
    $exportQuery = array_filter([
        'q' => $filters['q'] ?? null,
        'category' => $filters['category'] ?? null,
        'brand' => $filters['brand'] ?? null,
        'stock' => $filters['stock'] ?? null,
    ], fn ($v) => $v !== null && $v !== '');
    $money = fn ($n) => 'KES '.number_format((float) $n, 2);
@endphp

@section('page_header')
<div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between" x-data="{ exportOpen: false }">
    <div class="min-w-0">
        <nav class="mb-2 flex flex-wrap items-center gap-1.5 text-sm text-gray-400">
            <a href="{{ route('admin.dashboard') }}" class="hover:text-brand-500">Dashboard</a>
            <span>/</span>
            <a href="{{ route('admin.reports.index') }}" class="hover:text-brand-500">Analytics</a>
            <span>/</span>
            <span class="text-gray-600 dark:text-gray-300">Stock Valuation</span>
        </nav>
        <div class="flex items-start gap-3">
            <span class="mt-0.5 flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-brand-50 text-brand-700 dark:bg-brand-500/15 dark:text-brand-400">
                <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6m6 0V9a2 2 0 012-2h2a2 2 0 012 2v10m6 0V5a2 2 0 00-2-2h-2a2 2 0 00-2 2v14"/></svg>
            </span>
            <div>
                <h1 class="text-xl font-bold text-gray-800 dark:text-white/90 sm:text-2xl">Stock Valuation</h1>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Inventory financial overview based on current stock quantities and configured prices.</p>
            </div>
        </div>
    </div>
    <div class="flex flex-wrap items-center gap-2">
        <span class="inline-flex items-center gap-2 rounded-xl border border-gray-200 bg-white px-3.5 py-2.5 text-sm font-semibold text-gray-700 dark:border-gray-700 dark:bg-transparent dark:text-gray-300">
            <svg class="h-4 w-4 text-gray-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><rect x="3" y="4" width="18" height="18" rx="2"/><path d="M16 2v4M8 2v4M3 10h18"/></svg>
            As of {{ now()->format('d M Y') }}
        </span>
        <div class="relative">
            <button type="button" @click="exportOpen = !exportOpen"
                    class="ta-btn inline-flex items-center gap-2">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4 16v2a2 2 0 002 2h12a2 2 0 002-2v-2M12 3v13m0 0l-4-4m4 4l4-4"/></svg>
                Export
                <svg class="h-3.5 w-3.5 opacity-70" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/></svg>
            </button>
            <div x-show="exportOpen" @click.outside="exportOpen = false" x-cloak
                 class="absolute right-0 z-30 mt-2 w-44 overflow-hidden rounded-xl border border-gray-200 bg-white py-1 shadow-theme-lg dark:border-gray-700 dark:bg-gray-900">
                <a href="{{ route('admin.reports.stock-valuation.export', array_merge($exportQuery, ['format' => 'pdf'])) }}"
                   class="block px-3.5 py-2.5 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:text-gray-200 dark:hover:bg-white/5">Export PDF</a>
                <a href="{{ route('admin.reports.stock-valuation.export', array_merge($exportQuery, ['format' => 'excel'])) }}"
                   class="block px-3.5 py-2.5 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:text-gray-200 dark:hover:bg-white/5">Export Excel</a>
            </div>
        </div>
    </div>
</div>
@endsection

@section('content')
<div class="space-y-5 pb-24 xl:pb-0">
    {{-- KPI cards --}}
    <div class="grid grid-cols-2 gap-3 xl:grid-cols-4">
        <div class="rounded-2xl border border-gray-200 bg-white p-3.5 shadow-theme-xs dark:border-gray-800 dark:bg-white/[0.03] sm:p-5">
            <div class="ta-kpi-icon is-blue !h-10 !w-10">
                <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
            </div>
            <p class="mt-3 text-[11px] font-semibold uppercase tracking-wide text-gray-500">Total Stock Cost</p>
            <p class="mt-0.5 text-lg font-bold text-gray-800 dark:text-white/90 sm:text-2xl">{{ $money($summary['total_stock_cost']) }}</p>
            <p class="mt-1 text-[11px] text-gray-400">Current inventory cost value</p>
        </div>
        <div class="rounded-2xl border border-gray-200 bg-white p-3.5 shadow-theme-xs dark:border-gray-800 dark:bg-white/[0.03] sm:p-5">
            <div class="ta-kpi-icon is-success !h-10 !w-10">
                <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 3h2l.4 2M7 13h10l3-8H6.4M7 13L5.4 5M7 13l-2 9m12-9l2 9"/></svg>
            </div>
            <p class="mt-3 text-[11px] font-semibold uppercase tracking-wide text-gray-500">Expected Gross Sales</p>
            <p class="mt-0.5 text-lg font-bold text-gray-800 dark:text-white/90 sm:text-2xl">{{ $money($summary['expected_gross_sales']) }}</p>
            <p class="mt-1 text-[11px] text-gray-400">Potential sales value of current stock</p>
        </div>
        <div class="rounded-2xl border border-gray-200 bg-white p-3.5 shadow-theme-xs dark:border-gray-800 dark:bg-white/[0.03] sm:p-5">
            <div class="ta-kpi-icon is-purple !h-10 !w-10">
                <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/></svg>
            </div>
            <p class="mt-3 text-[11px] font-semibold uppercase tracking-wide text-gray-500">Expected Gross Profit</p>
            <p class="mt-0.5 text-lg font-bold text-gray-800 dark:text-white/90 sm:text-2xl">{{ $money($summary['expected_gross_profit']) }}</p>
            <p class="mt-1 text-[11px] text-gray-400">Potential gross profit before expenses</p>
        </div>
        <div class="rounded-2xl border border-gray-200 bg-white p-3.5 shadow-theme-xs dark:border-gray-800 dark:bg-white/[0.03] sm:p-5">
            <div class="ta-kpi-icon is-warning !h-10 !w-10">
                <span class="text-sm font-bold">%</span>
            </div>
            <p class="mt-3 text-[11px] font-semibold uppercase tracking-wide text-gray-500">Profit Margin</p>
            <p class="mt-0.5 text-lg font-bold text-gray-800 dark:text-white/90 sm:text-2xl">{{ number_format($summary['expected_profit_margin'], 2) }}%</p>
            <p class="mt-1 text-[11px] text-gray-400">Profit relative to expected sales</p>
        </div>
    </div>

    {{-- Filters --}}
    <div class="rounded-2xl border border-gray-200 bg-white p-3 shadow-theme-xs dark:border-gray-800 dark:bg-white/[0.03] sm:p-4">
        <form method="GET" action="{{ route('admin.reports.stock-valuation') }}" class="flex flex-col gap-3 lg:flex-row lg:flex-wrap lg:items-end">
            <div class="ta-field min-w-0 flex-1 lg:min-w-[220px]">
                <label>Search</label>
                <div class="relative">
                    <span class="pointer-events-none absolute inset-y-0 left-3 flex items-center text-gray-400">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-4.35-4.35M11 18a7 7 0 100-14 7 7 0 000 14z"/></svg>
                    </span>
                    <input type="text" name="q" value="{{ $filters['q'] ?? '' }}" class="ta-input !pl-9" placeholder="Search product name, SKU, or barcode...">
                </div>
            </div>
            <div class="ta-field w-full sm:w-auto sm:min-w-[150px]">
                <label>Category</label>
                <select name="category" class="ta-select">
                    <option value="">All Categories</option>
                    @foreach($categories as $cat)
                        <option value="{{ $cat->id }}" @selected((string) ($filters['category'] ?? '') === (string) $cat->id)>{{ $cat->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="ta-field w-full sm:w-auto sm:min-w-[150px]">
                <label>Brand</label>
                <select name="brand" class="ta-select">
                    <option value="">All Brands</option>
                    @foreach($brands as $brand)
                        <option value="{{ $brand->id }}" @selected((string) ($filters['brand'] ?? '') === (string) $brand->id)>{{ $brand->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="ta-field w-full sm:w-auto sm:min-w-[140px]">
                <label>Stock Status</label>
                <select name="stock" class="ta-select">
                    <option value="">All Stock</option>
                    <option value="in" @selected(($filters['stock'] ?? '') === 'in')>In stock</option>
                    <option value="low" @selected(($filters['stock'] ?? '') === 'low')>Low stock</option>
                    <option value="out" @selected(($filters['stock'] ?? '') === 'out')>Out of stock</option>
                </select>
            </div>
            <input type="hidden" name="per_page" value="{{ $perPage }}">
            <div class="flex flex-wrap items-center gap-2">
                <button type="submit" class="ta-btn">Apply</button>
                <a href="{{ route('admin.reports.stock-valuation') }}" class="ta-btn-outline inline-flex items-center gap-1.5">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4 4v6h6M20 20v-6h-6M5 19a9 9 0 0114.1-7.1M19 5a9 9 0 00-14.1 7.1"/></svg>
                    Reset
                </a>
            </div>
        </form>
    </div>

    {{-- Details table --}}
    <div class="overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-theme-xs dark:border-gray-800 dark:bg-white/[0.03]">
        <div class="flex flex-col gap-3 border-b border-gray-100 px-4 py-4 dark:border-gray-800 sm:flex-row sm:items-center sm:justify-between sm:px-5">
            <div class="min-w-0">
                <div class="flex items-center gap-2">
                    <span class="flex h-8 w-8 items-center justify-center rounded-lg bg-brand-50 text-brand-700">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                    </span>
                    <h2 class="text-base font-semibold text-gray-800 dark:text-white/90">Stock Valuation Details</h2>
                </div>
                <p class="mt-1 text-xs text-gray-500 sm:text-sm">
                    Total stock value (qty × buying price): <strong class="text-gray-700 dark:text-gray-300">{{ $money($summary['total_stock_cost']) }}</strong>
                    · Inventory products only (services excluded).
                </p>
            </div>
            <form method="GET" action="{{ route('admin.reports.stock-valuation') }}" class="flex items-center gap-2 text-sm text-gray-500">
                @foreach(array_filter($filters ?? [], fn ($v) => $v !== null && $v !== '') as $key => $value)
                    <input type="hidden" name="{{ $key }}" value="{{ $value }}">
                @endforeach
                <label for="sv-per-page" class="whitespace-nowrap">Show</label>
                <select id="sv-per-page" name="per_page" class="ta-select !w-auto !py-1.5" onchange="this.form.submit()">
                    @foreach([10, 25, 50, 100] as $n)
                        <option value="{{ $n }}" @selected($perPage === $n)>{{ $n }}</option>
                    @endforeach
                </select>
                <span class="whitespace-nowrap">per page</span>
            </form>
        </div>

        {{-- Mobile cards --}}
        <div class="divide-y divide-gray-100 dark:divide-gray-800 lg:hidden">
            @forelse($products as $i => $product)
                @php
                    $m = $valuation->lineMetrics($product);
                    $status = $valuation->stockStatus($product);
                    $statusClass = match ($status) {
                        'In stock' => 'bg-success-50 text-success-700',
                        'Low stock' => 'bg-warning-50 text-warning-700',
                        default => 'bg-error-50 text-error-700',
                    };
                    $img = $product->primaryImageUrl() ?? $product->image_url ?? $product->image ?? null;
                    $rowNum = $products->firstItem() ? ($products->firstItem() + $i) : ($i + 1);
                @endphp
                <div class="p-4">
                    <div class="flex items-start gap-3">
                        <div class="flex h-12 w-12 shrink-0 items-center justify-center overflow-hidden rounded-xl bg-gray-100 text-xs font-bold text-gray-500">
                            @if($img)
                                <img src="{{ $img }}" alt="" class="h-full w-full object-cover">
                            @else
                                {{ strtoupper(substr($product->name, 0, 1)) }}
                            @endif
                        </div>
                        <div class="min-w-0 flex-1">
                            <div class="flex items-start justify-between gap-2">
                                <div class="min-w-0">
                                    <p class="truncate text-sm font-semibold text-gray-800 dark:text-white/90">{{ $product->name }}</p>
                                    <p class="text-xs text-gray-400">#{{ $rowNum }} · {{ $product->sku ?: 'No SKU' }}</p>
                                </div>
                                <span class="shrink-0 rounded-full px-2 py-0.5 text-[10px] font-bold {{ $statusClass }}">{{ $status }}</span>
                            </div>
                            <div class="mt-3 grid grid-cols-2 gap-2 text-xs">
                                <div><span class="text-gray-400">Qty</span><p class="font-semibold text-gray-800 dark:text-white/90">{{ number_format((int) $product->stock) }}</p></div>
                                <div><span class="text-gray-400">Buying</span><p class="font-semibold text-gray-800 dark:text-white/90">{{ $money($product->buying_price ?? 0) }}</p></div>
                                <div><span class="text-gray-400">Selling</span><p class="font-semibold text-gray-800 dark:text-white/90">{{ $money($product->price) }}</p></div>
                                <div><span class="text-gray-400">Stock Cost</span><p class="font-semibold text-gray-800 dark:text-white/90">{{ $money($m['stock_cost']) }}</p></div>
                                <div><span class="text-gray-400">Exp. Sales</span><p class="font-semibold text-gray-800 dark:text-white/90">{{ $money($m['expected_sales']) }}</p></div>
                                <div><span class="text-gray-400">Exp. Profit</span><p class="font-semibold text-success-600">{{ $money($m['expected_profit']) }}</p></div>
                            </div>
                            @if(auth()->user()?->hasPermission('manage_products'))
                                <a href="{{ route('admin.products.edit', $product) }}" class="mt-3 inline-flex text-xs font-semibold text-brand-600 hover:underline">Edit product →</a>
                            @endif
                        </div>
                    </div>
                </div>
            @empty
                <div class="px-4 py-12 text-center text-sm text-gray-400">No products match these filters.</div>
            @endforelse
        </div>

        {{-- Desktop table --}}
        <div class="hidden overflow-x-auto lg:block">
            <table class="w-full min-w-[1100px] text-left text-sm">
                <thead class="border-b border-gray-100 bg-gray-50 dark:border-gray-800 dark:bg-white/[0.02]">
                    <tr>
                        <th class="px-4 py-3 text-xs font-semibold uppercase tracking-wide text-gray-500">#</th>
                        <th class="px-4 py-3 text-xs font-semibold uppercase tracking-wide text-gray-500">Product</th>
                        <th class="px-4 py-3 text-xs font-semibold uppercase tracking-wide text-gray-500">SKU</th>
                        <th class="px-4 py-3 text-right text-xs font-semibold uppercase tracking-wide text-gray-500">Qty</th>
                        <th class="px-4 py-3 text-right text-xs font-semibold uppercase tracking-wide text-gray-500">Buying Price</th>
                        <th class="px-4 py-3 text-right text-xs font-semibold uppercase tracking-wide text-gray-500">Selling Price</th>
                        <th class="px-4 py-3 text-right text-xs font-semibold uppercase tracking-wide text-gray-500">Stock Cost</th>
                        <th class="px-4 py-3 text-right text-xs font-semibold uppercase tracking-wide text-gray-500">Expected Sales</th>
                        <th class="px-4 py-3 text-right text-xs font-semibold uppercase tracking-wide text-gray-500">Expected Profit</th>
                        <th class="px-4 py-3 text-xs font-semibold uppercase tracking-wide text-gray-500">Status</th>
                        <th class="px-4 py-3 text-right text-xs font-semibold uppercase tracking-wide text-gray-500">Actions</th>
                    </tr>
                </thead>
                <tbody>
                @forelse($products as $i => $product)
                    @php
                        $m = $valuation->lineMetrics($product);
                        $status = $valuation->stockStatus($product);
                        $statusClass = match ($status) {
                            'In stock' => 'bg-success-50 text-success-700',
                            'Low stock' => 'bg-warning-50 text-warning-700',
                            default => 'bg-error-50 text-error-700',
                        };
                        $img = $product->primaryImageUrl() ?? $product->image_url ?? $product->image ?? null;
                        $rowNum = $products->firstItem() ? ($products->firstItem() + $i) : ($i + 1);
                    @endphp
                    <tr class="border-b border-gray-100 last:border-0 dark:border-gray-800" x-data="{ open: false }">
                        <td class="px-4 py-3.5 text-gray-400">{{ $rowNum }}</td>
                        <td class="px-4 py-3.5">
                            <div class="flex items-center gap-3">
                                <div class="flex h-10 w-10 shrink-0 items-center justify-center overflow-hidden rounded-xl bg-gray-100 text-xs font-bold text-gray-500">
                                    @if($img)
                                        <img src="{{ $img }}" alt="" class="h-full w-full object-cover">
                                    @else
                                        {{ strtoupper(substr($product->name, 0, 1)) }}
                                    @endif
                                </div>
                                <div class="min-w-0">
                                    <p class="truncate font-semibold text-gray-800 dark:text-white/90">{{ $product->name }}</p>
                                    <p class="truncate text-xs text-gray-400">{{ $product->category?->name ?: 'Uncategorised' }}</p>
                                </div>
                            </div>
                        </td>
                        <td class="px-4 py-3.5 text-gray-600">{{ $product->sku ?: '—' }}</td>
                        <td class="px-4 py-3.5 text-right font-semibold text-gray-800 dark:text-white/90">{{ number_format((int) $product->stock) }}</td>
                        <td class="px-4 py-3.5 text-right text-gray-600">{{ $money($product->buying_price ?? 0) }}</td>
                        <td class="px-4 py-3.5 text-right text-gray-600">{{ $money($product->price) }}</td>
                        <td class="px-4 py-3.5 text-right font-semibold text-gray-800 dark:text-white/90">{{ $money($m['stock_cost']) }}</td>
                        <td class="px-4 py-3.5 text-right font-semibold text-gray-800 dark:text-white/90">{{ $money($m['expected_sales']) }}</td>
                        <td class="px-4 py-3.5 text-right font-semibold text-success-600">{{ $money($m['expected_profit']) }}</td>
                        <td class="px-4 py-3.5">
                            <span class="inline-flex rounded-full px-2.5 py-0.5 text-xs font-semibold {{ $statusClass }}">{{ $status }}</span>
                        </td>
                        <td class="relative px-4 py-3.5 text-right">
                            <button type="button" @click="open = !open" class="inline-flex h-8 w-8 items-center justify-center rounded-lg text-gray-400 hover:bg-gray-50 hover:text-gray-700 dark:hover:bg-white/5" aria-label="Actions">
                                <svg class="h-4 w-4" fill="currentColor" viewBox="0 0 20 20"><path d="M10 6a2 2 0 110-4 2 2 0 010 4zm0 6a2 2 0 110-4 2 2 0 010 4zm0 6a2 2 0 110-4 2 2 0 010 4z"/></svg>
                            </button>
                            <div x-show="open" @click.outside="open = false" x-cloak
                                 class="absolute right-4 z-20 mt-1 w-40 overflow-hidden rounded-xl border border-gray-200 bg-white py-1 text-left shadow-theme-lg dark:border-gray-700 dark:bg-gray-900">
                                @if(auth()->user()?->hasPermission('manage_products'))
                                    <a href="{{ route('admin.products.edit', $product) }}" class="block px-3 py-2 text-sm text-gray-700 hover:bg-gray-50 dark:text-gray-200 dark:hover:bg-white/5">Edit product</a>
                                @endif
                                <a href="{{ route('admin.products.index', ['q' => $product->sku ?: $product->name]) }}" class="block px-3 py-2 text-sm text-gray-700 hover:bg-gray-50 dark:text-gray-200 dark:hover:bg-white/5">View in catalog</a>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="11" class="px-4 py-12 text-center text-gray-400">No products match these filters.</td>
                    </tr>
                @endforelse
                </tbody>
                @if($products->count())
                <tfoot class="border-t border-gray-100 bg-gray-50 dark:border-gray-800 dark:bg-white/[0.02]">
                    <tr>
                        <td colspan="6" class="px-4 py-3 text-sm font-bold text-gray-800 dark:text-white/90">Totals (filtered)</td>
                        <td class="px-4 py-3 text-right text-sm font-bold text-gray-800 dark:text-white/90">{{ $money($tableTotals['stock_cost']) }}</td>
                        <td class="px-4 py-3 text-right text-sm font-bold text-gray-800 dark:text-white/90">{{ $money($tableTotals['expected_sales']) }}</td>
                        <td class="px-4 py-3 text-right text-sm font-bold text-success-600">{{ $money($tableTotals['expected_profit']) }}</td>
                        <td colspan="2"></td>
                    </tr>
                </tfoot>
                @endif
            </table>
        </div>

        @if($products->hasPages() || $products->total() > 0)
        <div class="flex flex-col gap-3 border-t border-gray-100 px-4 py-3 dark:border-gray-800 sm:flex-row sm:items-center sm:justify-between sm:px-5">
            <p class="text-sm text-gray-500">
                Showing {{ $products->firstItem() ?? 0 }} to {{ $products->lastItem() ?? 0 }} of {{ number_format($products->total()) }} products
            </p>
            <div>{{ $products->links() }}</div>
        </div>
        @endif
    </div>

    {{-- Secondary widgets --}}
    <div class="grid grid-cols-1 gap-4 xl:grid-cols-2">
        <div class="rounded-2xl border border-gray-200 bg-white p-4 shadow-theme-xs dark:border-gray-800 dark:bg-white/[0.03] sm:p-5">
            <div class="mb-3 flex items-center justify-between">
                <h3 class="text-sm font-semibold text-gray-800 dark:text-white/90">Stocktake Variances</h3>
                <a href="{{ route('admin.stock-takes.index') }}" class="text-xs font-semibold text-brand-600 hover:underline">All stocktakes</a>
            </div>
            <ul class="divide-y divide-gray-100 dark:divide-gray-800">
                @forelse($stocktakes as $take)
                    <li class="flex items-center justify-between gap-3 py-2.5">
                        <div class="min-w-0">
                            <p class="truncate text-sm font-medium text-gray-800 dark:text-white/90">{{ $take->reference ?? ('Stocktake #'.$take->id) }}</p>
                            <p class="text-xs text-gray-400">{{ optional($take->approved_at ?? $take->updated_at)->format('d M Y') }}</p>
                        </div>
                    </li>
                @empty
                    <li class="py-6 text-center text-sm text-gray-400">No approved stocktakes yet.</li>
                @endforelse
            </ul>
        </div>
        <div class="rounded-2xl border border-gray-200 bg-white p-4 shadow-theme-xs dark:border-gray-800 dark:bg-white/[0.03] sm:p-5">
            <div class="mb-3 flex items-center justify-between">
                <h3 class="text-sm font-semibold text-gray-800 dark:text-white/90">Pending Purchase Orders</h3>
                <a href="{{ route('admin.purchase-orders.index') }}" class="text-xs font-semibold text-brand-600 hover:underline">All POs</a>
            </div>
            <ul class="divide-y divide-gray-100 dark:divide-gray-800">
                @forelse($pendingPos as $po)
                    <li class="flex items-center justify-between gap-3 py-2.5">
                        <div class="min-w-0">
                            <a href="{{ route('admin.purchase-orders.show', $po) }}" class="truncate text-sm font-medium text-gray-800 hover:text-brand-600 dark:text-white/90">{{ $po->po_number }}</a>
                            <p class="text-xs text-gray-400">{{ $po->supplier?->name ?: 'No supplier' }} · {{ str_replace('_', ' ', $po->status) }}</p>
                        </div>
                        <span class="shrink-0 text-sm font-semibold text-gray-800 dark:text-white/90">{{ $money($po->total) }}</span>
                    </li>
                @empty
                    <li class="py-6 text-center text-sm text-gray-400">No pending purchase orders.</li>
                @endforelse
            </ul>
        </div>
        <div class="rounded-2xl border border-gray-200 bg-white p-4 shadow-theme-xs dark:border-gray-800 dark:bg-white/[0.03] sm:p-5">
            <div class="mb-3 flex items-center justify-between">
                <h3 class="text-sm font-semibold text-gray-800 dark:text-white/90">Customer Balances</h3>
                <a href="{{ route('admin.customers.index') }}" class="text-xs font-semibold text-brand-600 hover:underline">All customers</a>
            </div>
            <ul class="divide-y divide-gray-100 dark:divide-gray-800">
                @forelse($customerBalances as $customer)
                    <li class="flex items-center justify-between gap-3 py-2.5">
                        <p class="truncate text-sm font-medium text-gray-800 dark:text-white/90">{{ $customer->name }}</p>
                        <span class="shrink-0 text-sm font-semibold text-gray-800 dark:text-white/90">{{ $money($customer->balance) }}</span>
                    </li>
                @empty
                    <li class="py-6 text-center text-sm text-gray-400">No outstanding customer balances.</li>
                @endforelse
            </ul>
        </div>
        <div class="rounded-2xl border border-gray-200 bg-white p-4 shadow-theme-xs dark:border-gray-800 dark:bg-white/[0.03] sm:p-5">
            <div class="mb-3 flex items-center justify-between">
                <h3 class="text-sm font-semibold text-gray-800 dark:text-white/90">Supplier Balances</h3>
                <a href="{{ route('admin.suppliers.index') }}" class="text-xs font-semibold text-brand-600 hover:underline">All suppliers</a>
            </div>
            <ul class="divide-y divide-gray-100 dark:divide-gray-800">
                @forelse($supplierBalances as $row)
                    <li class="flex items-center justify-between gap-3 py-2.5">
                        <p class="truncate text-sm font-medium text-gray-800 dark:text-white/90">{{ $row->supplier?->name ?: 'Supplier #'.$row->supplier_id }}</p>
                        <span class="shrink-0 text-sm font-semibold text-gray-800 dark:text-white/90">{{ $money($row->outstanding) }}</span>
                    </li>
                @empty
                    <li class="py-6 text-center text-sm text-gray-400">No outstanding supplier balances.</li>
                @endforelse
            </ul>
        </div>
    </div>
</div>
@endsection
