@extends('layouts.admin')
@section('title', 'Products')
@section('heading', 'Products')
@section('subheading', 'Manage catalog, pricing, stock, and visibility.')

@push('styles')
<style>
/* ── Products page ───────────────────────────────────────── */
.products-page { display: flex; flex-direction: column; gap: 18px; }

/* KPI cards */
.prod-kpis { display: grid; grid-template-columns: repeat(4, 1fr); gap: 14px; }
@media (max-width: 900px)  { .prod-kpis { grid-template-columns: repeat(2,1fr); } }
@media (max-width: 500px)  { .prod-kpis { grid-template-columns: 1fr; } }

.prod-kpi {
    background: #fff;
    border: 1px solid #eaecf0;
    border-radius: 16px;
    padding: 18px 20px;
    display: flex;
    align-items: center;
    gap: 16px;
    box-shadow: 0 1px 4px rgba(0,0,0,.05);
}
.prod-kpi-icon {
    width: 52px; height: 52px;
    border-radius: 12px;
    display: flex; align-items: center; justify-content: center;
    flex-shrink: 0;
}
.prod-kpi-icon svg { width: 26px; height: 26px; }
.prod-kpi-icon.purple  { background: #f3f0ff; color: #7c3aed; }
.prod-kpi-icon.green   { background: #ecfdf5; color: #059669; }
.prod-kpi-icon.orange  { background: #fff7ed; color: #ea580c; }
.prod-kpi-icon.gold    { background: #fffbeb; color: #d97706; }
.prod-kpi-body { min-width: 0; }
.prod-kpi-label { font-size: 11px; font-weight: 700; color: #6b7280; text-transform: uppercase; letter-spacing: .06em; }
.prod-kpi-value { font-size: 1.65rem; font-weight: 800; color: #111827; line-height: 1.15; margin-top: 2px; }
.prod-kpi-sub   { font-size: 12px; color: #9ca3af; margin-top: 2px; }

/* Toolbar */
.prod-toolbar {
    background: #fff;
    border: 1px solid #eaecf0;
    border-radius: 16px;
    padding: 14px 18px;
    display: flex;
    flex-wrap: wrap;
    gap: 12px;
    align-items: flex-end;
    box-shadow: 0 1px 4px rgba(0,0,0,.04);
}
.prod-toolbar-filters { display: flex; flex-wrap: wrap; gap: 10px; flex: 1; align-items: flex-end; }
.prod-toolbar-actions { display: flex; flex-wrap: wrap; gap: 8px; align-items: center; }
.prod-field { display: flex; flex-direction: column; gap: 5px; min-width: 160px; flex: 1; }
.prod-field label { font-size: 11px; font-weight: 700; color: #6b7280; text-transform: uppercase; letter-spacing: .05em; }
.prod-field input,
.prod-field select {
    padding: 9px 12px;
    border: 1px solid #d1d5db;
    border-radius: 10px;
    font-size: 13px;
    color: #111827;
    background: #fff;
    width: 100%;
    margin: 0 !important;
    outline: none;
}
.prod-field input:focus,
.prod-field select:focus { border-color: #d4af37; box-shadow: 0 0 0 3px rgba(212,175,55,.12); }
.prod-search-wrap { position: relative; }
.prod-search-wrap input { padding-right: 36px; }
.prod-search-icon {
    position: absolute; right: 10px; top: 50%; transform: translateY(-50%);
    color: #9ca3af; pointer-events: none;
}
.btn-apply {
    background: linear-gradient(135deg,#d4af37,#b8942d);
    color: #121212;
    border: none;
    border-radius: 10px;
    padding: 9px 18px;
    font-size: 13px;
    font-weight: 800;
    cursor: pointer;
    white-space: nowrap;
    display: inline-flex; align-items: center; gap: 6px;
}
.btn-apply:hover { opacity: .92; }
.btn-outline {
    background: #fff;
    color: #374151;
    border: 1px solid #d1d5db;
    border-radius: 10px;
    padding: 9px 14px;
    font-size: 13px;
    font-weight: 700;
    cursor: pointer;
    text-decoration: none;
    white-space: nowrap;
    display: inline-flex; align-items: center; gap: 6px;
}
.btn-outline:hover { background: #f9fafb; }
.btn-add {
    background: linear-gradient(135deg,#d4af37,#b8942d);
    color: #121212;
    border: none;
    border-radius: 10px;
    padding: 9px 16px;
    font-size: 13px;
    font-weight: 800;
    cursor: pointer;
    text-decoration: none;
    white-space: nowrap;
    display: inline-flex; align-items: center; gap: 6px;
}
.btn-add:hover { opacity: .92; }

/* Bulk bar */
.prod-bulk {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 0 0 14px;
    flex-wrap: wrap;
}
.prod-bulk select {
    padding: 8px 12px;
    border: 1px solid #d1d5db;
    border-radius: 10px;
    font-size: 13px;
    min-width: 200px;
    margin: 0 !important;
}
.prod-bulk .btn-outline { padding: 8px 14px; }

/* Table card */
.prod-table-card {
    background: #fff;
    border: 1px solid #eaecf0;
    border-radius: 16px;
    overflow: hidden;
    box-shadow: 0 1px 4px rgba(0,0,0,.04);
}
.prod-table { width: 100%; border-collapse: collapse; }
.prod-table thead tr { background: #f9fafb; border-bottom: 1px solid #eaecf0; }
.prod-table thead th {
    font-size: 11px;
    font-weight: 700;
    color: #6b7280;
    text-transform: uppercase;
    letter-spacing: .06em;
    padding: 11px 14px;
    white-space: nowrap;
}
.prod-table thead th.sort-col { cursor: pointer; user-select: none; }
.sort-arrow { margin-left: 4px; opacity: .5; font-size: 10px; }
.prod-table tbody tr { border-bottom: 1px solid #f3f4f6; transition: background .12s; }
.prod-table tbody tr:last-child { border-bottom: none; }
.prod-table tbody tr:hover { background: rgba(212,175,55,.04); }
.prod-table td { padding: 12px 14px; font-size: 13px; color: #374151; vertical-align: middle; }

/* Thumb */
.prod-thumb {
    width: 44px; height: 44px;
    object-fit: cover;
    border-radius: 10px;
    border: 1px solid #e5e7eb;
    display: block;
}

/* Product name cell */
.prod-name { font-weight: 700; color: #111827; font-size: 13.5px; }
.prod-sku  { font-size: 11px; color: #9ca3af; margin-top: 2px; }

/* Price */
.prod-price      { font-weight: 700; color: #111827; }
.prod-price-was  { font-size: 11px; color: #9ca3af; text-decoration: line-through; }
.prod-discount   { font-size: 11px; color: #ef4444; font-weight: 700; }

/* Status pills */
.spill {
    display: inline-flex; align-items: center;
    border-radius: 999px; padding: 3px 10px;
    font-size: 11.5px; font-weight: 700;
}
.spill-active   { background: #dcfce7; color: #166534; }
.spill-inactive { background: #f3f4f6; color: #4b5563; }
.spill-out      { background: #fee2e2; color: #991b1b; }
.spill-low      { background: #ffedd5; color: #c2410c; }

/* Icon action buttons */
.iact { display: inline-flex; align-items: center; justify-content: center; width: 32px; height: 32px; border-radius: 8px; border: 1px solid #e5e7eb; background: #fff; color: #374151; cursor: pointer; transition: background .12s, color .12s; text-decoration: none; }
.iact:hover { background: #f3f4f6; }
.iact.edit:hover { background: #eff6ff; color: #2563eb; border-color: #bfdbfe; }
.iact.del  { border-color: #fee2e2; color: #ef4444; }
.iact.del:hover { background: #fee2e2; }
.iact svg  { width: 15px; height: 15px; }
.iact-row  { display: flex; gap: 6px; align-items: center; justify-content: flex-end; }

/* Footer */
.prod-table-footer {
    padding: 12px 18px;
    border-top: 1px solid #f3f4f6;
    display: flex;
    align-items: center;
    justify-content: space-between;
    flex-wrap: wrap;
    gap: 10px;
    background: #fff;
}
.prod-showing { font-size: 12px; color: #6b7280; }
</style>
@endpush

@section('content')
<div class="products-page">

    {{-- ── KPI Cards ─────────────────────────────────────── --}}
    <div class="prod-kpis">
        <div class="prod-kpi">
            <div class="prod-kpi-icon purple">
                <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
            </div>
            <div class="prod-kpi-body">
                <div class="prod-kpi-label">Total Products</div>
                <div class="prod-kpi-value">{{ $stats['total'] }}</div>
                <div class="prod-kpi-sub">All products</div>
            </div>
        </div>
        <div class="prod-kpi">
            <div class="prod-kpi-icon green">
                <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            </div>
            <div class="prod-kpi-body">
                <div class="prod-kpi-label">Active Products</div>
                <div class="prod-kpi-value">{{ $stats['active'] }}</div>
                <div class="prod-kpi-sub">Available and active</div>
            </div>
        </div>
        <div class="prod-kpi">
            <div class="prod-kpi-icon orange">
                <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/></svg>
            </div>
            <div class="prod-kpi-body">
                <div class="prod-kpi-label">Low Stock</div>
                <div class="prod-kpi-value">{{ $stats['low'] }}</div>
                <div class="prod-kpi-sub">Reorder soon</div>
            </div>
        </div>
        <div class="prod-kpi">
            <div class="prod-kpi-icon gold">
                <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
            </div>
            <div class="prod-kpi-body">
                <div class="prod-kpi-label">Out of Stock</div>
                <div class="prod-kpi-value">{{ $stats['out'] }}</div>
                <div class="prod-kpi-sub">Currently out of stock</div>
            </div>
        </div>
    </div>

    {{-- ── Filter Toolbar ─────────────────────────────────── --}}
    <div class="prod-toolbar">
        <form id="products-filter-form" method="GET" action="{{ route('admin.products.index') }}" style="display:contents;">
            <div class="prod-toolbar-filters">
                <div class="prod-field" style="max-width:280px;">
                    <label>Search</label>
                    <div class="prod-search-wrap">
                        <input type="text" name="q" value="{{ request('q') }}" placeholder="Name, SKU or barcode...">
                        <span class="prod-search-icon">
                            <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="11" cy="11" r="8"/><path stroke-linecap="round" d="M21 21l-4.35-4.35"/></svg>
                        </span>
                    </div>
                </div>

                <div class="prod-field" style="max-width:170px;">
                    <label>Stock</label>
                    <select name="stock">
                        <option value="">All Stock</option>
                        <option value="low" @selected(request('stock') === 'low')>Low stock</option>
                        <option value="out" @selected(request('stock') === 'out')>Out of stock</option>
                    </select>
                </div>

                <div class="prod-field" style="max-width:170px;">
                    <label>Status</label>
                    <select name="status">
                        <option value="">All Status</option>
                        <option value="active"   @selected(request('status') === 'active')>Active</option>
                        <option value="inactive" @selected(request('status') === 'inactive')>Inactive</option>
                    </select>
                </div>

                <div style="display:flex;gap:8px;align-items:flex-end;">
                    <button type="submit" class="btn-apply">
                        <svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2a1 1 0 01-.293.707L13 13.414V19a1 1 0 01-.553.894l-4 2A1 1 0 017 21v-7.586L3.293 6.707A1 1 0 013 6V4z"/></svg>
                        Apply Filters
                    </button>
                    @if(request()->filled('q') || request()->filled('stock') || request()->filled('status'))
                        <a href="{{ route('admin.products.index') }}" class="btn-outline">Clear</a>
                    @endif
                </div>
            </div>
        </form>

        <div class="prod-toolbar-actions">
            <a href="{{ route('admin.products.export.csv', request()->query()) }}" class="btn-outline">
                <svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                Export CSV
            </a>
            <a href="{{ route('admin.stock-takes.index') }}" class="btn-outline">
                <svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/></svg>
                Stock Take
            </a>
            <a href="{{ route('admin.products.create') }}" class="btn-add">
                <svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
                Add Product
            </a>
        </div>
    </div>

    {{-- ── Table Card ─────────────────────────────────────── --}}
    <div class="prod-table-card">
        <form id="product-bulk-form" method="POST" action="{{ route('admin.products.bulk-update') }}">
            @csrf

            {{-- Bulk bar --}}
            <div class="prod-bulk" style="padding:14px 18px 0;">
                <select name="action" required>
                    <option value="">Bulk action</option>
                    <option value="activate">Activate selected</option>
                    <option value="deactivate">Deactivate selected</option>
                    <option value="delete">Delete selected</option>
                </select>
                <button type="submit" class="btn-outline" onclick="return confirm('Apply bulk action to selected products?')">Apply</button>
            </div>

            <div style="overflow-x:auto;">
                <table class="prod-table">
                    <thead>
                        <tr>
                            <th style="width:40px;padding-left:18px;">
                                <input type="checkbox" id="select-all-products" style="width:16px;height:16px;cursor:pointer;accent-color:#d4af37;">
                            </th>
                            <th>Product ↕</th>
                            <th>Vendor</th>
                            <th>Category</th>
                            <th>Price ↕</th>
                            <th>Stock ↕</th>
                            <th>Status</th>
                            <th style="text-align:right;padding-right:18px;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                    @forelse($products as $product)
                        <tr>
                            <td style="padding-left:18px;">
                                <input
                                    type="checkbox"
                                    name="product_ids[]"
                                    value="{{ $product->id }}"
                                    class="product-check"
                                    style="width:16px;height:16px;cursor:pointer;accent-color:#d4af37;"
                                >
                            </td>
                            <td>
                                <div style="display:flex;align-items:center;gap:12px;">
                                    <img
                                        class="prod-thumb"
                                        src="{{ $product->image_url ?: 'https://via.placeholder.com/44x44?text=?' }}"
                                        alt="{{ $product->name }}"
                                    >
                                    <div>
                                        <div class="prod-name">{{ $product->name }}</div>
                                        @if($product->sku)
                                            <div class="prod-sku">SKU {{ $product->sku }}</div>
                                        @endif
                                    </div>
                                </div>
                            </td>
                            <td>{{ $product->vendor?->name ?: 'In-house' }}</td>
                            <td>{{ $product->category?->name ?: '—' }}</td>
                            <td>
                                <div class="prod-price">KES {{ number_format($product->currentPrice(), 2) }}</div>
                                @if($product->hasSale())
                                    <div style="display:flex;align-items:center;gap:4px;margin-top:2px;">
                                        <span class="prod-price-was">{{ number_format((float)$product->price, 2) }}</span>
                                        <span class="prod-discount">-{{ $product->discountPercent() }}%</span>
                                    </div>
                                @endif
                            </td>
                            <td>
                                <span style="font-weight:700;color:#111827;">{{ $product->stock }}</span>
                                @if($product->stock === 0)
                                    <span class="spill spill-out" style="margin-left:6px;">Out</span>
                                @elseif($product->stock <= 5)
                                    <span class="spill spill-low" style="margin-left:6px;">Low</span>
                                @endif
                            </td>
                            <td>
                                @if($product->is_active)
                                    <span class="spill spill-active">Active</span>
                                @else
                                    <span class="spill spill-inactive">Inactive</span>
                                @endif
                            </td>
                            <td style="padding-right:18px;">
                                <div class="iact-row">
                                    <a href="{{ route('shop.show', $product) }}" target="_blank" rel="noopener" class="iact" title="View">
                                        <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                    </a>
                                    <a href="{{ route('admin.products.edit', $product) }}" class="iact edit" title="Edit">
                                        <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                    </a>
                                    <form method="POST" action="{{ route('admin.products.destroy', $product) }}" style="margin:0;">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="iact del" title="Delete" onclick="return confirm('Delete {{ addslashes($product->name) }}?')">
                                            <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" style="text-align:center;padding:48px 16px;color:#9ca3af;font-size:14px;">
                                No products found.
                            </td>
                        </tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </form>

        <div class="prod-table-footer">
            <span class="prod-showing">
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
