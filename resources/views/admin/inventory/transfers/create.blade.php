@extends('layouts.admin')
@section('title', 'Transfer Stock')

@section('page_header')
<div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
    <div>
        <nav class="mb-2 flex flex-wrap items-center gap-1.5 text-sm text-gray-400">
            <a href="{{ route('admin.dashboard') }}" class="hover:text-brand-500">Dashboard</a>
            <span>/</span>
            <a href="{{ route('admin.stock-transfers.index') }}" class="hover:text-brand-500">Stock Transfers</a>
            <span>/</span>
            <span class="text-gray-600 dark:text-gray-300">New Transfer</span>
        </nav>
        <h1 class="text-2xl font-bold text-gray-800 dark:text-white/90">Transfer Stock</h1>
        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Move quantity from one location to another. Total stock stays the same.</p>
    </div>
    <div class="flex flex-wrap items-center gap-2">
        <a href="{{ route('admin.stock-transfers.index') }}" class="ta-btn-outline">Back</a>
    </div>
</div>
@endsection

@push('styles')
<style>
.product-combobox { position: relative; }
.product-combobox-input {
    width: 100%;
    padding: 10px 12px;
    border: 1px solid #d1d5db;
    border-radius: 10px;
    font: inherit;
    background: #fff;
}
.product-combobox-input:focus {
    outline: none;
    border-color: #a58112;
    box-shadow: 0 0 0 3px rgba(165, 129, 18, .18);
}
.product-combobox-panel {
    display: none;
    position: absolute;
    z-index: 40;
    left: 0; right: 0;
    top: calc(100% + 4px);
    max-height: 280px;
    overflow: auto;
    background: #fff;
    border: 1px solid #e5e7eb;
    border-radius: 12px;
    box-shadow: 0 12px 28px rgba(0,0,0,.12);
}
.product-combobox-panel.is-open { display: block; }
.product-combobox-option {
    display: block;
    width: 100%;
    text-align: left;
    padding: 10px 12px;
    border: 0;
    background: transparent;
    cursor: pointer;
    font: inherit;
    border-bottom: 1px solid #f3f4f6;
}
.product-combobox-option:hover,
.product-combobox-option.is-active { background: rgba(165, 129, 18, 0.08); }
.product-combobox-option strong { display: block; font-size: 14px; color: #111827; }
.product-combobox-option span { font-size: 12px; color: #6b7280; }
.product-combobox-empty {
    padding: 14px 12px;
    color: #9ca3af;
    font-size: 13px;
}
</style>
@endpush

@section('content')
@php
    $productOptions = $products->map(fn ($p) => [
        'id' => $p->id,
        'name' => $p->name,
        'sku' => $p->sku,
        'barcode' => $p->barcode ?? null,
        'has_variants' => (bool) $p->has_variants,
        'label' => trim($p->name.($p->sku ? ' ('.$p->sku.')' : '')),
    ])->values();
    $selectedProduct = $productOptions->firstWhere('id', optional($product)->id);
@endphp
<div class="ta-page">
    <x-admin.list-card title="Transfer details" desc="Search a product by name, SKU, or barcode. Total stock will not change.">
        <form method="POST" action="{{ route('admin.stock-transfers.store') }}" id="transfer-form" class="admin-form-grid">
            @csrf
            <div class="ta-field">
                <label>From location *</label>
                <select name="from_location_id" id="from_location_id" class="ta-select" required>
                    @foreach($locations as $loc)
                        <option value="{{ $loc->id }}" @selected((int)$fromId === (int)$loc->id)>{{ $loc->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="ta-field">
                <label>To location *</label>
                <select name="to_location_id" id="to_location_id" class="ta-select" required>
                    @foreach($locations as $loc)
                        <option value="{{ $loc->id }}" @selected((int)$toId === (int)$loc->id)>{{ $loc->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="ta-field" style="grid-column:1/-1;">
                <label for="product_search">Product *</label>
                <div class="product-combobox" id="product-combobox">
                    <input type="hidden" name="product_id" id="product_id" value="{{ optional($product)->id }}" required>
                    <input
                        type="search"
                        id="product_search"
                        class="product-combobox-input"
                        placeholder="Search by name, SKU, or barcode…"
                        autocomplete="off"
                        value="{{ $selectedProduct['label'] ?? '' }}"
                        aria-autocomplete="list"
                        aria-controls="product-combobox-panel"
                        aria-expanded="false"
                    >
                    <div class="product-combobox-panel" id="product-combobox-panel" role="listbox"></div>
                </div>
                <p class="ta-muted" style="margin:6px 0 0;">Type to filter products, then click a result.</p>
            </div>
            <div id="variant-wrap" class="ta-field" style="grid-column:1/-1;{{ ($product && $product->usesVariants()) ? '' : 'display:none;' }}">
                <label>Variant *</label>
                <select name="product_variant_id" id="product_variant_id" class="ta-select">
                    <option value="">Select variant</option>
                    @if($product && $product->usesVariants())
                        @foreach($product->activeVariants as $v)
                            <option value="{{ $v->id }}" @selected((string)$variantId === (string)$v->id)>{{ $v->name }}</option>
                        @endforeach
                    @endif
                </select>
            </div>
            <div class="ta-field">
                <label>Available at From</label>
                <input type="text" id="available_qty" class="ta-input" value="{{ $available ?? '—' }}" readonly>
            </div>
            <div class="ta-field">
                <label>Quantity *</label>
                <input type="number" name="quantity" id="quantity" class="ta-input" min="1" value="{{ old('quantity', 1) }}" required>
            </div>
            <div class="ta-field">
                <label>Reason *</label>
                <select name="reason" class="ta-select" required>
                    @foreach($reasons as $reason)
                        <option value="{{ $reason }}" @selected(old('reason', 'Shop Replenishment') === $reason)>{{ $reason }}</option>
                    @endforeach
                </select>
            </div>
            <div class="ta-field" style="grid-column:1/-1;">
                <label>Notes</label>
                <textarea name="notes" class="ta-input" rows="3">{{ old('notes') }}</textarea>
            </div>
            <div style="grid-column:1/-1;" class="rounded-xl border border-gray-200 bg-gray-50 px-4 py-3 dark:border-gray-700 dark:bg-white/[0.02]">
                <label class="ta-check">
                    <input type="checkbox" name="confirm" value="1" required>
                    <span>I confirm this transfer (total stock will not change)</span>
                </label>
            </div>
            <div id="preview" class="ta-muted" style="grid-column:1/-1;margin:0;padding:12px;background:#f9fafb;border-radius:10px;border:1px solid #e5e7eb;"></div>
            <div style="grid-column:1/-1;">
                <button type="submit" class="ta-btn">Complete Transfer</button>
            </div>
        </form>
    </x-admin.list-card>
</div>
<script>
const availableUrl = @json(route('admin.stock-transfers.available'));
const productCatalog = @json($productOptions);
let selectedHasVariants = @json((bool) optional($product)->has_variants);
let selectedProductLabel = @json($selectedProduct['label'] ?? '');
let activeIndex = -1;

const productIdInput = document.getElementById('product_id');
const productSearch = document.getElementById('product_search');
const productPanel = document.getElementById('product-combobox-panel');

function normalize(str) {
    return String(str || '').toLowerCase().trim();
}

function escapeHtml(str) {
    return String(str || '')
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;')
        .replace(/'/g, '&#39;');
}

function filterProducts(query) {
    const q = normalize(query);
    if (!q) return productCatalog.slice(0, 40);
    return productCatalog.filter((p) => {
        return normalize(p.name).includes(q)
            || normalize(p.sku).includes(q)
            || normalize(p.barcode).includes(q)
            || normalize(p.label).includes(q);
    }).slice(0, 60);
}

function openPanel() {
    productPanel.classList.add('is-open');
    productSearch.setAttribute('aria-expanded', 'true');
}

function closePanel() {
    productPanel.classList.remove('is-open');
    productSearch.setAttribute('aria-expanded', 'false');
    activeIndex = -1;
}

function renderProductPanel(query = productSearch.value) {
    const rows = filterProducts(query);
    if (!rows.length) {
        productPanel.innerHTML = '<div class="product-combobox-empty">No products match your search.</div>';
        openPanel();
        return;
    }
    productPanel.innerHTML = rows.map((p, i) => {
        const meta = [p.sku ? 'SKU '+p.sku : null, p.barcode ? 'Barcode '+p.barcode : null, p.has_variants ? 'Has variants' : null].filter(Boolean).join(' · ') || 'Product';
        return `
        <button type="button" class="product-combobox-option${i === activeIndex ? ' is-active' : ''}"
            role="option"
            data-id="${p.id}"
            data-has-variants="${p.has_variants ? 1 : 0}"
            data-label="${encodeURIComponent(p.label)}"
            data-index="${i}">
            <strong>${escapeHtml(p.name)}</strong>
            <span>${escapeHtml(meta)}</span>
        </button>`;
    }).join('');
    openPanel();
}

function selectProduct(id, label, hasVariants) {
    productIdInput.value = id;
    selectedProductLabel = label;
    selectedHasVariants = !!Number(hasVariants);
    productSearch.value = label;
    document.getElementById('product_variant_id').value = '';
    closePanel();
    refreshAvailable();
}

productSearch.addEventListener('focus', () => renderProductPanel(productSearch.value));
productSearch.addEventListener('input', () => {
    if (productSearch.value !== selectedProductLabel) {
        productIdInput.value = '';
        selectedHasVariants = false;
    }
    activeIndex = 0;
    renderProductPanel(productSearch.value);
    updatePreview();
});
productSearch.addEventListener('keydown', (e) => {
    const options = [...productPanel.querySelectorAll('.product-combobox-option')];
    if (e.key === 'ArrowDown') {
        e.preventDefault();
        if (!productPanel.classList.contains('is-open')) renderProductPanel();
        activeIndex = Math.min(activeIndex + 1, options.length - 1);
        options.forEach((el, i) => el.classList.toggle('is-active', i === activeIndex));
        options[activeIndex]?.scrollIntoView({ block: 'nearest' });
    } else if (e.key === 'ArrowUp') {
        e.preventDefault();
        activeIndex = Math.max(activeIndex - 1, 0);
        options.forEach((el, i) => el.classList.toggle('is-active', i === activeIndex));
        options[activeIndex]?.scrollIntoView({ block: 'nearest' });
    } else if (e.key === 'Enter') {
        const target = options[activeIndex] || options[0];
        if (productPanel.classList.contains('is-open') && target) {
            e.preventDefault();
            selectProduct(target.dataset.id, decodeURIComponent(target.dataset.label || ''), target.dataset.hasVariants);
        }
    } else if (e.key === 'Escape') {
        closePanel();
    }
});
productPanel.addEventListener('mousedown', (e) => {
    const btn = e.target.closest('.product-combobox-option');
    if (!btn) return;
    e.preventDefault();
    selectProduct(btn.dataset.id, decodeURIComponent(btn.dataset.label || ''), btn.dataset.hasVariants);
});
document.addEventListener('click', (e) => {
    if (!e.target.closest('#product-combobox')) closePanel();
});

document.getElementById('transfer-form')?.addEventListener('submit', (e) => {
    if (!productIdInput.value) {
        e.preventDefault();
        productSearch.focus();
        renderProductPanel(productSearch.value);
        alert('Please select a product from the search results.');
    }
});

async function refreshAvailable() {
    const productId = productIdInput.value;
    const locationId = document.getElementById('from_location_id').value;
    const variantSel = document.getElementById('product_variant_id');
    const variantWrap = document.getElementById('variant-wrap');
    const hasVariants = selectedHasVariants;
    variantWrap.style.display = hasVariants ? '' : 'none';
    if (!productId || !locationId) {
        document.getElementById('available_qty').value = '—';
        updatePreview();
        return;
    }
    const params = new URLSearchParams({ product_id: productId, location_id: locationId });
    if (variantSel.value) params.set('product_variant_id', variantSel.value);
    const res = await fetch(availableUrl + '?' + params.toString(), { headers: { 'Accept': 'application/json' }});
    const data = await res.json();
    if (hasVariants && data.variants?.length) {
        const current = variantSel.value;
        variantSel.innerHTML = '<option value="">Select variant</option>' + data.variants.map(v => `<option value="${v.id}">${v.name} (${v.stock})</option>`).join('');
        if (current) variantSel.value = current;
        if (variantSel.value) {
            const match = data.variants.find(v => String(v.id) === String(variantSel.value));
            document.getElementById('available_qty').value = match ? match.stock : data.available;
        } else {
            document.getElementById('available_qty').value = '—';
        }
    } else {
        document.getElementById('available_qty').value = data.available;
    }
    updatePreview();
}
function updatePreview() {
    const from = document.getElementById('from_location_id').selectedOptions[0]?.text || '';
    const to = document.getElementById('to_location_id').selectedOptions[0]?.text || '';
    const qty = Number(document.getElementById('quantity').value || 0);
    const avail = Number(document.getElementById('available_qty').value || 0);
    const product = selectedProductLabel || productSearch.value || 'product';
    const variant = document.getElementById('product_variant_id').selectedOptions[0]?.text || '';
    document.getElementById('preview').textContent = qty > 0 && productIdInput.value
        ? `Transfer ${qty} × ${product}${variant && variant !== 'Select variant' ? ' — ' + variant : ''} from ${from} → ${to}. After: From ${Math.max(avail - qty, 0)} (was ${avail || 0}). Total unchanged.`
        : 'Search and select a product, then enter a quantity to preview the transfer.';
}
['from_location_id','product_variant_id'].forEach(id => document.getElementById(id)?.addEventListener('change', refreshAvailable));
['quantity','to_location_id'].forEach(id => document.getElementById(id)?.addEventListener('input', updatePreview));
document.getElementById('product_variant_id')?.addEventListener('change', updatePreview);
refreshAvailable();
</script>
@endsection
