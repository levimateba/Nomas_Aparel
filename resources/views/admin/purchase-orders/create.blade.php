@extends('layouts.admin')
@section('title', 'New Purchase Order')

@section('page_header')
<div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
    <div>
        <nav class="mb-2 flex flex-wrap items-center gap-1.5 text-sm text-gray-400">
            <a href="{{ route('admin.dashboard') }}" class="hover:text-brand-500">Dashboard</a>
            <span>/</span>
            <a href="{{ route('admin.purchase-orders.index') }}" class="hover:text-brand-500">Purchase Orders</a>
            <span>/</span>
            <span class="text-gray-600 dark:text-gray-300">New PO</span>
        </nav>
        <h1 class="text-2xl font-bold text-gray-800 dark:text-white/90">New Purchase Order</h1>
        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Search products and add only what you need to order.</p>
    </div>
    <div class="flex flex-wrap items-center gap-2">
        <a href="{{ route('admin.purchase-orders.index') }}" class="ta-btn-outline">Back</a>
    </div>
</div>
@endsection

@section('content')
@php
    $catalog = $products->map(fn ($p) => [
        'id' => $p->id,
        'name' => $p->name,
        'sku' => $p->sku,
        'barcode' => $p->barcode ?? null,
        'stock' => (int) $p->stock,
        'buying_price' => (float) ($p->buying_price ?? 0),
        'reorder_level' => (int) ($p->reorder_level ?? 0),
        'label' => trim($p->name.($p->sku ? ' ('.$p->sku.')' : '')),
    ])->values();
@endphp

<div class="ta-page" x-data="purchaseOrderForm(@js($catalog))">
    <form method="POST" action="{{ route('admin.purchase-orders.store') }}" @submit="prepareSubmit">
        @csrf

        <x-admin.list-card title="Order details" desc="Supplier, dates, discount, and notes.">
            <div class="admin-form-grid">
                <div class="ta-field">
                    <label>Supplier</label>
                    <select name="supplier_id" class="ta-select">
                        <option value="">— Optional —</option>
                        @foreach($suppliers as $supplier)
                            <option value="{{ $supplier->id }}" @selected(old('supplier_id') == $supplier->id)>{{ $supplier->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="ta-field">
                    <label>Order date *</label>
                    <input type="date" name="order_date" class="ta-input" value="{{ old('order_date', now()->toDateString()) }}" required>
                </div>
                <div class="ta-field">
                    <label>Expected date</label>
                    <input type="date" name="expected_date" class="ta-input" value="{{ old('expected_date') }}">
                </div>
                <div class="ta-field">
                    <label>Discount</label>
                    <input type="number" step="0.01" min="0" name="discount" class="ta-input" x-model.number="discount" value="{{ old('discount', 0) }}">
                </div>
                <div class="ta-field" style="grid-column:1/-1;">
                    <label>Notes</label>
                    <textarea name="notes" class="ta-input" rows="2">{{ old('notes') }}</textarea>
                </div>
            </div>
        </x-admin.list-card>

        <div class="ta-table-card">
            <div class="flex flex-wrap items-start justify-between gap-3 border-b border-gray-100 px-5 py-4 dark:border-gray-800">
                <div>
                    <h3 class="text-base font-medium text-gray-800 dark:text-white/90">Products to order</h3>
                    <p class="mt-1 text-sm text-gray-500">Search and add products — no long list to scroll.</p>
                </div>
                <div class="text-right text-sm">
                    <div class="text-gray-500"><span x-text="lines.length"></span> product(s)</div>
                    <div class="text-lg font-bold text-gray-800 dark:text-white/90">KES <span x-text="subtotal.toLocaleString(undefined,{minimumFractionDigits:2,maximumFractionDigits:2})"></span></div>
                </div>
            </div>

            <div class="border-b border-gray-100 px-5 py-4 dark:border-gray-800">
                <label class="mb-1.5 block text-xs font-semibold uppercase tracking-wide text-gray-500">Add product</label>
                <div class="flex flex-wrap items-end gap-3">
                    <div class="product-combobox relative max-w-xl flex-1" style="min-width:220px;">
                        <input
                            type="search"
                            class="product-combobox-input"
                            placeholder="Search by name, SKU, or barcode…"
                            autocomplete="off"
                            x-model="query"
                            @focus="open = true"
                            @input="open = true; active = 0"
                            @keydown.arrow-down.prevent="active = Math.min(active + 1, filtered.length - 1)"
                            @keydown.arrow-up.prevent="active = Math.max(active - 1, 0)"
                            @keydown.enter.prevent="filtered[active] && addProduct(filtered[active])"
                            @keydown.escape="open = false"
                        >
                        <div class="product-combobox-panel" :class="{ 'is-open': open && filtered.length }" @mousedown.prevent>
                            <template x-for="(p, i) in filtered" :key="p.id">
                                <button type="button" class="product-combobox-option" :class="{ 'is-active': i === active }" @click="addProduct(p)">
                                    <strong x-text="p.name"></strong>
                                    <span x-text="[p.sku, 'Stock '+p.stock, p.reorder_level ? 'Reorder '+p.reorder_level : null].filter(Boolean).join(' · ')"></span>
                                </button>
                            </template>
                        </div>
                        <p class="ta-muted mt-2" x-show="open && query && !filtered.length">No matching products.</p>
                    </div>
                    <button type="button" class="ta-btn-outline" @click="addLowStock" title="Add all products at or below reorder level">
                        Add low-stock items
                    </button>
                </div>
            </div>

            <div class="ta-table-wrap">
                <table class="ta-table">
                    <thead>
                        <tr>
                            <th>Product</th>
                            <th style="width:120px;">Order qty</th>
                            <th style="width:140px;">Buying price</th>
                            <th style="width:120px;" class="text-right">Line total</th>
                            <th style="width:90px;">Stock</th>
                            <th style="width:60px;"></th>
                        </tr>
                    </thead>
                    <tbody>
                        <template x-if="!lines.length">
                            <tr>
                                <td colspan="6" class="ta-empty">No products added yet. Search above or use “Add low-stock items”.</td>
                            </tr>
                        </template>
                        <template x-for="(line, index) in lines" :key="line.product_id">
                            <tr>
                                <td>
                                    <input type="hidden" :name="`items[${index}][product_id]`" :value="line.product_id">
                                    <div class="ta-name" x-text="line.name"></div>
                                    <div class="ta-muted" x-text="line.sku || 'No SKU'"></div>
                                </td>
                                <td>
                                    <input type="number" min="1" class="ta-input" :name="`items[${index}][quantity]`" x-model.number="line.quantity" required>
                                </td>
                                <td>
                                    <input type="number" step="0.01" min="0" class="ta-input" :name="`items[${index}][buying_price]`" x-model.number="line.buying_price">
                                </td>
                                <td class="text-right font-semibold text-gray-800 dark:text-white/90" x-text="'KES ' + (line.quantity * line.buying_price).toLocaleString(undefined,{minimumFractionDigits:2,maximumFractionDigits:2})"></td>
                                <td>
                                    <span x-text="line.stock"></span>
                                    <span class="ta-muted" x-show="line.reorder_level" x-text="' / '+line.reorder_level"></span>
                                </td>
                                <td>
                                    <button type="button" class="inline-flex h-8 w-8 items-center justify-center rounded-lg text-error-600 hover:bg-error-50" @click="removeLine(index)" title="Remove">
                                        <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                                    </button>
                                </td>
                            </tr>
                        </template>
                    </tbody>
                </table>
            </div>

            <div class="flex flex-wrap items-center justify-between gap-3 border-t border-gray-100 px-5 py-4 dark:border-gray-800">
                <div class="text-sm text-gray-500">
                    Discount: KES <span x-text="Number(discount||0).toLocaleString(undefined,{minimumFractionDigits:2})"></span>
                    · Total: <strong class="text-gray-800 dark:text-white/90">KES <span x-text="total.toLocaleString(undefined,{minimumFractionDigits:2,maximumFractionDigits:2})"></span></strong>
                </div>
                <div class="flex gap-2">
                    <a href="{{ route('admin.purchase-orders.index') }}" class="ta-btn-outline">Cancel</a>
                    <button type="submit" class="ta-btn" :disabled="!lines.length">Save draft</button>
                </div>
            </div>
        </div>
    </form>
</div>

<script>
function purchaseOrderForm(catalog) {
    return {
        catalog,
        query: '',
        open: false,
        active: 0,
        lines: [],
        discount: {{ (float) old('discount', 0) }},
        get filtered() {
            const q = this.query.toLowerCase().trim();
            const added = new Set(this.lines.map(l => l.product_id));
            let rows = this.catalog.filter(p => !added.has(p.id));
            if (!q) return rows.slice(0, 12);
            return rows.filter(p =>
                (p.name || '').toLowerCase().includes(q)
                || (p.sku || '').toLowerCase().includes(q)
                || (p.barcode || '').toLowerCase().includes(q)
                || (p.label || '').toLowerCase().includes(q)
            ).slice(0, 20);
        },
        get subtotal() {
            return this.lines.reduce((sum, l) => sum + (Number(l.quantity) || 0) * (Number(l.buying_price) || 0), 0);
        },
        get total() {
            return Math.max(this.subtotal - (Number(this.discount) || 0), 0);
        },
        suggestedQty(p) {
            const reorder = Number(p.reorder_level) || 0;
            const stock = Number(p.stock) || 0;
            if (reorder > 0) return Math.max(reorder - stock, reorder, 1);
            return 1;
        },
        addProduct(p) {
            if (!p) return;
            this.lines.push({
                product_id: p.id,
                name: p.name,
                sku: p.sku,
                stock: p.stock,
                reorder_level: p.reorder_level || 0,
                quantity: this.suggestedQty(p),
                buying_price: p.buying_price || 0,
            });
            this.query = '';
            this.open = false;
            this.active = 0;
        },
        addLowStock() {
            const added = new Set(this.lines.map(l => l.product_id));
            let count = 0;
            this.catalog.forEach((p) => {
                if (added.has(p.id)) return;
                const reorder = Number(p.reorder_level) || 0;
                const stock = Number(p.stock) || 0;
                if (reorder > 0 && stock <= reorder) {
                    this.addProduct(p);
                    count++;
                } else if (reorder === 0 && stock <= 5) {
                    this.addProduct(p);
                    count++;
                }
            });
            if (!count) alert('No low-stock products to add.');
        },
        removeLine(index) {
            this.lines.splice(index, 1);
        },
        prepareSubmit(e) {
            if (!this.lines.length) {
                e.preventDefault();
                alert('Add at least one product to the purchase order.');
            }
        }
    };
}
</script>
@endsection
