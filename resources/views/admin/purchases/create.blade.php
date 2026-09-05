@extends('layouts.admin')
@section('title', 'Receive Stock')

@section('page_header')
<div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
    <div>
        <nav class="mb-2 flex flex-wrap items-center gap-1.5 text-sm text-gray-400">
            <a href="{{ route('admin.dashboard') }}" class="hover:text-brand-500">Dashboard</a>
            <span>/</span>
            <a href="{{ route('admin.purchases.index') }}" class="hover:text-brand-500">Purchases</a>
            <span>/</span>
            <span class="text-gray-600 dark:text-gray-300">Receive Stock</span>
        </nav>
        <h1 class="text-2xl font-bold text-gray-800 dark:text-white/90">Receive Stock</h1>
        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Search products and add only what arrived — no long product list to scroll.</p>
    </div>
    <div class="flex flex-wrap items-center gap-2">
        <a href="{{ route('admin.purchases.index') }}" class="ta-btn-outline">Back</a>
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
        'label' => trim($p->name.($p->sku ? ' ('.$p->sku.')' : '')),
    ])->values();
@endphp

<div
    class="ta-page"
    x-data="receiveStock(@js($catalog))"
>
    <form method="POST" action="{{ route('admin.purchases.store') }}" @submit="prepareSubmit">
        @csrf

        <x-admin.list-card title="Receipt details" desc="Supplier, location, payment, and invoice information.">
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
                    <label>Receiving location *</label>
                    <select name="stock_location_id" class="ta-select" required>
                        @foreach(($locations ?? \App\Models\StockLocation::orderedActive()) as $loc)
                            <option value="{{ $loc->id }}" @selected((int)old('stock_location_id', \App\Models\StockLocation::mainStore()?->id) === (int)$loc->id)>{{ $loc->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="ta-field">
                    <label>Purchase date *</label>
                    <input type="date" name="purchase_date" class="ta-input" value="{{ old('purchase_date', now()->toDateString()) }}" required>
                </div>
                <div class="ta-field">
                    <label>Invoice reference</label>
                    <input type="text" name="invoice_reference" class="ta-input" value="{{ old('invoice_reference') }}" placeholder="Supplier invoice #">
                </div>
                <div class="ta-field">
                    <label>Due date</label>
                    <input type="date" name="due_date" class="ta-input" value="{{ old('due_date') }}">
                </div>
                <div class="ta-field">
                    <label>Payment status *</label>
                    <select name="payment_status" class="ta-select" x-model="paymentStatus">
                        @foreach(['unpaid','partial','paid'] as $status)
                            <option value="{{ $status }}" @selected(old('payment_status', 'unpaid') === $status)>{{ ucfirst($status) }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="ta-field" x-show="paymentStatus === 'partial'" x-cloak>
                    <label>Amount paid (partial)</label>
                    <input type="number" step="0.01" min="0" name="amount_paid" class="ta-input" value="{{ old('amount_paid') }}">
                </div>
                <div class="ta-field">
                    <label>Payment method</label>
                    <select name="payment_method" class="ta-select">
                        @foreach(['Cash','M-Pesa','Bank','Card','Credit'] as $method)
                            <option value="{{ $method }}">{{ $method }}</option>
                        @endforeach
                    </select>
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
                    <h3 class="text-base font-medium text-gray-800 dark:text-white/90">Line items</h3>
                    <p class="mt-1 text-sm text-gray-500">Search and add products from this delivery.</p>
                </div>
                <div class="text-right text-sm">
                    <div class="text-gray-500"><span x-text="lines.length"></span> product(s)</div>
                    <div class="text-lg font-bold text-gray-800 dark:text-white/90">KES <span x-text="subtotal.toLocaleString(undefined,{minimumFractionDigits:2,maximumFractionDigits:2})"></span></div>
                </div>
            </div>

            <div class="border-b border-gray-100 px-5 py-4 dark:border-gray-800">
                <label class="mb-1.5 block text-xs font-semibold uppercase tracking-wide text-gray-500">Add product</label>
                <div class="product-combobox relative max-w-xl">
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
                                <span x-text="[p.sku, p.barcode ? 'Barcode '+p.barcode : null, 'Stock '+p.stock].filter(Boolean).join(' · ')"></span>
                            </button>
                        </template>
                    </div>
                    <p class="ta-muted mt-2" x-show="open && query && !filtered.length">No matching products.</p>
                </div>
            </div>

            <div class="ta-table-wrap">
                <table class="ta-table">
                    <thead>
                        <tr>
                            <th>Product</th>
                            <th style="width:120px;">Qty</th>
                            <th style="width:140px;">Buying price</th>
                            <th style="width:120px;" class="text-right">Line total</th>
                            <th style="width:90px;">In stock</th>
                            <th style="width:60px;"></th>
                        </tr>
                    </thead>
                    <tbody>
                        <template x-if="!lines.length">
                            <tr>
                                <td colspan="6" class="ta-empty">No products added yet. Search above to add delivery items.</td>
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
                                <td x-text="line.stock"></td>
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
                    <a href="{{ route('admin.purchases.index') }}" class="ta-btn-outline">Cancel</a>
                    <button type="submit" class="ta-btn" :disabled="!lines.length">Receive stock</button>
                </div>
            </div>
        </div>
    </form>
</div>

<script>
function receiveStock(catalog) {
    return {
        catalog,
        query: '',
        open: false,
        active: 0,
        lines: [],
        discount: {{ (float) old('discount', 0) }},
        paymentStatus: @json(old('payment_status', 'unpaid')),
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
        addProduct(p) {
            if (!p) return;
            this.lines.push({
                product_id: p.id,
                name: p.name,
                sku: p.sku,
                stock: p.stock,
                quantity: 1,
                buying_price: p.buying_price || 0,
            });
            this.query = '';
            this.open = false;
            this.active = 0;
        },
        removeLine(index) {
            this.lines.splice(index, 1);
        },
        prepareSubmit(e) {
            if (!this.lines.length) {
                e.preventDefault();
                alert('Add at least one product to receive.');
            }
        }
    };
}
</script>
@endsection
