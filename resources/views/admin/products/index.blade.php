@extends('layouts.admin')
@section('title', 'Products')

@section('page_header')
<div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between" x-data="{ importOpen: false }">
    <div>
        <nav class="mb-2 flex flex-wrap items-center gap-1.5 text-sm text-gray-400">
            <a href="{{ route('admin.dashboard') }}" class="hover:text-brand-500">Dashboard</a>
            <span>/</span>
            <span>Stock &amp; Catalog</span>
            <span>/</span>
            <span class="text-gray-600 dark:text-gray-300">Products</span>
        </nav>
        <h1 class="text-2xl font-bold text-gray-800 dark:text-white/90">Products</h1>
        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Manage your product catalog, prices, barcodes, SKUs and stock levels.</p>
    </div>
    <div class="flex flex-wrap items-center gap-2">
        <a href="{{ route('admin.products.export.template') }}" class="ta-btn-outline">
            <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
            Template
        </a>
        <button type="button" class="ta-btn-outline" @click="importOpen = true">
            <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4 16v2a2 2 0 002 2h12a2 2 0 002-2v-2M7 10l5 5m0 0l5-5m-5 5V3"/></svg>
            Import
        </button>
        <a href="{{ route('admin.products.export.csv', request()->query()) }}" class="ta-btn-outline">
            <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4 16v2a2 2 0 002 2h12a2 2 0 002-2v-2M12 3v13m0 0l-4-4m4 4l4-4"/></svg>
            Export
        </a>
        <a href="{{ route('admin.products.create') }}" class="ta-btn">
            <span class="text-lg leading-none">+</span> Add Product
        </a>
    </div>

    {{-- Import modal --}}
    <div x-show="importOpen" x-cloak class="fixed inset-0 z-[100000] flex items-center justify-center p-4" style="display:none;">
        <div class="absolute inset-0 bg-gray-900/50" @click="importOpen = false"></div>
        <div class="relative w-full max-w-lg rounded-2xl border border-gray-200 bg-white p-6 shadow-theme-lg dark:border-gray-800 dark:bg-gray-900" @click.stop>
            <h3 class="text-lg font-semibold text-gray-800 dark:text-white/90">Import products</h3>
            <p class="mt-1 text-sm text-gray-500">Upload a CSV matching the template. Existing SKUs are updated.</p>
            <div class="mt-4 flex flex-wrap gap-2">
                <a href="{{ route('admin.products.export.template') }}" class="ta-btn-outline ta-btn-sm">Download template</a>
            </div>
            <form method="POST" action="{{ route('admin.products.import') }}" enctype="multipart/form-data" class="mt-5 space-y-4">
                @csrf
                <div class="ta-field">
                    <label>CSV file</label>
                    <input type="file" name="file" accept=".csv,text/csv" class="ta-input" required>
                </div>
                <div class="flex justify-end gap-2">
                    <button type="button" class="ta-btn-outline" @click="importOpen = false">Cancel</button>
                    <button type="submit" class="ta-btn">Upload &amp; import</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@section('content')
@php
    $mainId = $mainStore?->id;
    $shopId = $shopFloor?->id;
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
            <p class="mt-4 text-xs font-semibold uppercase tracking-wide text-gray-500">Total Products</p>
            <p class="mt-1 text-2xl font-bold text-gray-800 dark:text-white/90">{{ number_format($stats['total']) }}</p>
            <div class="mt-2 flex items-center justify-between text-xs">
                <span class="text-gray-400">All products</span>
                <span class="font-semibold {{ ($stats['total_trend'] ?? 0) >= 0 ? 'text-success-600' : 'text-error-600' }}">
                    {{ ($stats['total_trend'] ?? 0) >= 0 ? '↗ +' : '↘ ' }}{{ abs($stats['total_trend'] ?? 0) }}%
                </span>
            </div>
        </div>

        <div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-theme-xs dark:border-gray-800 dark:bg-white/[0.03]">
            <div class="flex items-start justify-between gap-3">
                <div class="ta-kpi-icon is-success">
                    <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </div>
                <div data-sparkline='@json($stats['sparklines']['active'] ?? [])' data-color="#12B76A"></div>
            </div>
            <p class="mt-4 text-xs font-semibold uppercase tracking-wide text-gray-500">Active Products</p>
            <p class="mt-1 text-2xl font-bold text-gray-800 dark:text-white/90">{{ number_format($stats['active']) }}</p>
            <div class="mt-2 flex items-center justify-between text-xs">
                <span class="text-gray-400">Available and active</span>
                <span class="font-semibold {{ ($stats['active_trend'] ?? 0) >= 0 ? 'text-success-600' : 'text-error-600' }}">
                    {{ ($stats['active_trend'] ?? 0) >= 0 ? '↗ +' : '↘ ' }}{{ abs($stats['active_trend'] ?? 0) }}%
                </span>
            </div>
        </div>

        <a href="{{ route('admin.products.index', ['stock' => 'low']) }}" class="rounded-2xl border border-gray-200 bg-white p-5 no-underline shadow-theme-xs dark:border-gray-800 dark:bg-white/[0.03]">
            <div class="flex items-start justify-between gap-3">
                <div class="ta-kpi-icon is-warning">
                    <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/></svg>
                </div>
                <div data-sparkline='@json($stats['sparklines']['low'] ?? [])' data-color="#F79009"></div>
            </div>
            <p class="mt-4 text-xs font-semibold uppercase tracking-wide text-gray-500">Low Stock</p>
            <p class="mt-1 text-2xl font-bold text-gray-800 dark:text-white/90">{{ number_format($stats['low']) }}</p>
            <div class="mt-2 flex items-center justify-between text-xs">
                <span class="text-gray-400">Reorder soon</span>
                <span class="font-semibold text-warning-600">Alert</span>
            </div>
        </a>

        <a href="{{ route('admin.products.index', ['stock' => 'out']) }}" class="rounded-2xl border border-gray-200 bg-white p-5 no-underline shadow-theme-xs dark:border-gray-800 dark:bg-white/[0.03]">
            <div class="flex items-start justify-between gap-3">
                <div class="ta-kpi-icon is-error">
                    <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M20 13V7a2 2 0 00-2-2h-3l-2-2H9L7 5H4a2 2 0 00-2 2v6m18 0v4a2 2 0 01-2 2H4a2 2 0 01-2-2v-4m18 0H2"/></svg>
                </div>
                <div data-sparkline='@json($stats['sparklines']['out'] ?? [])' data-color="#F04438"></div>
            </div>
            <p class="mt-4 text-xs font-semibold uppercase tracking-wide text-gray-500">Out of Stock</p>
            <p class="mt-1 text-2xl font-bold text-gray-800 dark:text-white/90">{{ number_format($stats['out']) }}</p>
            <div class="mt-2 flex items-center justify-between text-xs">
                <span class="text-gray-400">Currently unavailable</span>
                <span class="font-semibold text-error-600">{{ $stats['out'] > 0 ? 'Action' : 'OK' }}</span>
            </div>
        </a>
    </div>

    {{-- Filters --}}
    <form method="GET" action="{{ route('admin.products.index') }}" class="ta-toolbar">
        <div class="ta-field" style="flex:2;min-width:220px;">
            <label>Search</label>
            <input type="text" name="q" class="ta-input" value="{{ request('q') }}" placeholder="Search by name, brand, SKU, or barcode…">
        </div>
        <div class="ta-field" style="max-width:180px;">
            <label>Category</label>
            <select name="category_id" class="ta-select">
                <option value="">All categories</option>
                @foreach($categories as $cat)
                    <option value="{{ $cat->id }}" @selected((string) request('category_id') === (string) $cat->id)>{{ $cat->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="ta-field" style="max-width:160px;">
            <label>Brand</label>
            <select name="brand_id" class="ta-select">
                <option value="">All brands</option>
                @foreach($brands as $brand)
                    <option value="{{ $brand->id }}" @selected((string) request('brand_id') === (string) $brand->id)>{{ $brand->name }}</option>
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
        @if(request()->anyFilled(['q', 'category_id', 'brand_id', 'stock', 'status']))
            <a href="{{ route('admin.products.index') }}" class="ta-btn-outline">Reset</a>
        @endif
    </form>

    {{-- Table --}}
    <div class="ta-table-card">
        <form id="product-bulk-form" method="POST" action="{{ route('admin.products.bulk-update') }}">
            @csrf
            <div class="ta-bulk">
                <select name="action" class="ta-select" style="max-width:200px;" required>
                    <option value="">Bulk actions</option>
                    <option value="activate">Activate selected</option>
                    <option value="deactivate">Deactivate selected</option>
                    <option value="delete">Delete selected</option>
                </select>
                <button type="submit" class="ta-btn-outline ta-btn-sm" onclick="return confirm('Apply bulk action to selected products?')">Apply</button>
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
                <table class="ta-table" style="min-width:1100px;">
                    <thead>
                        <tr>
                            <th style="width:40px;">
                                <input type="checkbox" id="select-all-products" class="h-4 w-4 rounded border-gray-300 text-brand-500 focus:ring-brand-500/20">
                            </th>
                            <th style="width:48px;">#</th>
                            <th>Product</th>
                            <th>SKU / Barcode</th>
                            <th>Brand</th>
                            <th>Category</th>
                            <th>Stock</th>
                            <th>Price</th>
                            <th>Status</th>
                            <th class="text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                    @forelse($products as $index => $product)
                        @php
                            $totalStock = $product->totalAvailableStock();
                            $reorder = (int) ($product->reorder_level ?? 0);
                            $threshold = $reorder > 0 ? $reorder : 5;
                            if ($totalStock <= 0) {
                                $stockStatus = 'out';
                                $stockPill = 'ta-pill-error';
                                $stockLabel = 'Out of Stock';
                            } elseif ($totalStock <= $threshold) {
                                $stockStatus = 'low';
                                $stockPill = 'ta-pill-warning';
                                $stockLabel = 'Low Stock';
                            } else {
                                $stockStatus = 'in';
                                $stockPill = 'ta-pill-success';
                                $stockLabel = 'In Stock';
                            }
                            $byLoc = collect($product->inventoryStocks ?? [])->groupBy('stock_location_id');
                            $storeQty = $mainId ? (int) ($byLoc->get($mainId)?->sum('quantity') ?? 0) : $totalStock;
                            $shopQty = $shopId ? (int) ($byLoc->get($shopId)?->sum('quantity') ?? 0) : 0;
                            $thumb = $product->primaryImageUrl();
                            $desc = Str::limit(strip_tags((string) ($product->description ?: $product->category?->name ?: '—')), 42);
                        @endphp
                        <tr>
                            <td>
                                <input type="checkbox" name="product_ids[]" value="{{ $product->id }}" class="product-check h-4 w-4 rounded border-gray-300 text-brand-500 focus:ring-brand-500/20">
                            </td>
                            <td class="text-gray-400">{{ $products->firstItem() + $index }}</td>
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
                                        <div class="ta-name truncate">{{ $product->name }}</div>
                                        <div class="ta-muted truncate">{{ $desc }}</div>
                                        @if($product->has_variants)
                                            <div class="ta-muted">{{ $product->active_variants_count ?? 0 }} variants</div>
                                        @endif
                                    </div>
                                </div>
                            </td>
                            <td>
                                <div class="font-medium text-gray-800 dark:text-white/90">{{ $product->sku ?: '—' }}</div>
                                <div class="ta-muted">{{ $product->barcode ?: 'No barcode' }}</div>
                            </td>
                            <td>{{ $product->brand?->name ?: '—' }}</td>
                            <td>{{ $product->category?->name ?: '—' }}</td>
                            <td>
                                <div class="flex flex-col gap-1 text-xs text-gray-600 dark:text-gray-300">
                                    <div class="inline-flex items-center gap-1.5">
                                        <svg class="h-3.5 w-3.5 text-gray-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 21h18M5 21V7l7-4 7 4v14M9 21v-6h6v6"/></svg>
                                        <span>Store <strong class="text-gray-800 dark:text-white/90">{{ $storeQty }}</strong></span>
                                    </div>
                                    <div class="inline-flex items-center gap-1.5">
                                        <svg class="h-3.5 w-3.5 text-gray-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 10.5l9-7 9 7V20a1 1 0 01-1 1h-5v-6H9v6H4a1 1 0 01-1-1v-9.5z"/></svg>
                                        <span>Shop <strong class="text-gray-800 dark:text-white/90">{{ $shopQty }}</strong></span>
                                    </div>
                                </div>
                            </td>
                            <td class="whitespace-nowrap font-semibold text-gray-800 dark:text-white/90">KES {{ number_format($product->currentPrice(), 2) }}</td>
                            <td>
                                @unless($product->is_active)
                                    <span class="ta-pill ta-pill-muted">Archived</span>
                                @else
                                    <span class="ta-pill {{ $stockPill }}">{{ $stockLabel }}</span>
                                @endunless
                            </td>
                            <td>
                                <div class="flex items-center justify-end gap-1">
                                    <a href="{{ route('admin.products.edit', $product) }}" class="inline-flex h-8 w-8 items-center justify-center rounded-lg text-gray-500 hover:bg-gray-100 hover:text-brand-600 dark:hover:bg-white/5" title="View / Edit">
                                        <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                    </a>
                                    <a href="{{ route('admin.products.edit', $product) }}" class="inline-flex h-8 w-8 items-center justify-center rounded-lg text-gray-500 hover:bg-gray-100 hover:text-brand-600 dark:hover:bg-white/5" title="Edit">
                                        <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                    </a>
                                    <a href="{{ route('admin.stock-overview.index', ['q' => $product->sku ?: $product->name]) }}" class="inline-flex h-8 w-8 items-center justify-center rounded-lg text-gray-500 hover:bg-gray-100 hover:text-brand-600 dark:hover:bg-white/5" title="Stock analytics">
                                        <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
                                    </a>
                                    <div class="relative" x-data="{ open: false }">
                                        <button type="button" class="inline-flex h-8 w-8 items-center justify-center rounded-lg text-gray-500 hover:bg-gray-100 dark:hover:bg-white/5" @click="open = !open" title="More">
                                            <svg class="h-4 w-4" fill="currentColor" viewBox="0 0 24 24"><circle cx="5" cy="12" r="2"/><circle cx="12" cy="12" r="2"/><circle cx="19" cy="12" r="2"/></svg>
                                        </button>
                                        <div x-show="open" @click.outside="open = false" x-cloak class="absolute right-0 z-20 mt-1 w-44 rounded-xl border border-gray-200 bg-white py-1 shadow-theme-lg dark:border-gray-700 dark:bg-gray-900" style="display:none;">
                                            <a href="{{ route('admin.products.edit', $product) }}#stock-adjust" class="block px-3 py-2 text-sm text-gray-700 hover:bg-gray-50 dark:text-gray-300 dark:hover:bg-white/5">Adjust stock</a>
                                            <a href="{{ route('admin.purchase-orders.create', ['product_id' => $product->id]) }}" class="block px-3 py-2 text-sm text-gray-700 hover:bg-gray-50 dark:text-gray-300 dark:hover:bg-white/5">Create PO</a>
                                            @if($product->is_active)
                                                <button type="submit" form="archive-{{ $product->id }}" class="block w-full px-3 py-2 text-left text-sm text-error-600 hover:bg-error-50" onclick="return confirm('Archive {{ addslashes($product->name) }}?')">Archive</button>
                                            @else
                                                <button type="submit" form="activate-{{ $product->id }}" class="block w-full px-3 py-2 text-left text-sm text-success-600 hover:bg-success-50">Activate</button>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="10" class="ta-empty">No products found.</td>
                        </tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </form>

        {{-- Outside bulk form so archive/activate work --}}
        @foreach($products as $product)
            @if($product->is_active)
                <form id="archive-{{ $product->id }}" method="POST" action="{{ route('admin.products.destroy', $product) }}" class="hidden">@csrf @method('DELETE')</form>
            @else
                <form id="activate-{{ $product->id }}" method="POST" action="{{ route('admin.products.activate', $product) }}" class="hidden">@csrf</form>
            @endif
        @endforeach

        <div class="ta-table-footer">
            <span class="ta-muted">
                Showing {{ $products->firstItem() ?? 0 }} to {{ $products->lastItem() ?? 0 }} of {{ $products->total() }} products
            </span>
            {{ $products->links() }}
        </div>
    </div>
</div>

<script>
document.getElementById('select-all-products')?.addEventListener('change', function () {
    const checked = this.checked;
    document.querySelectorAll('.product-check').forEach(el => el.checked = checked);
});
</script>
@endsection
