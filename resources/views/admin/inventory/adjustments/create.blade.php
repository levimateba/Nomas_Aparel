@extends('layouts.admin')
@section('title', 'Stock Adjustment')

@section('page_header')
<div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
    <div>
        <nav class="mb-2 flex flex-wrap items-center gap-1.5 text-sm text-gray-400">
            <a href="{{ route('admin.dashboard') }}" class="hover:text-brand-500">Dashboard</a>
            <span>/</span>
            <span>Stock &amp; Catalog</span>
            <span>/</span>
            <span class="text-gray-600 dark:text-gray-300">Stock Adjustment</span>
        </nav>
        <h1 class="text-2xl font-bold text-gray-800 dark:text-white/90">Stock Adjustment</h1>
        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Correct stock with a full audit trail. Prefer transfers for Store ↔ Shop moves.</p>
    </div>
    <div class="flex flex-wrap items-center gap-2">
        <a href="{{ route('admin.stock-overview.index') }}" class="ta-btn-outline">Back to overview</a>
        <a href="{{ route('admin.stock-transfers.create') }}" class="ta-btn-outline">Transfer instead</a>
    </div>
</div>
@endsection

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
    $selectedProduct = $productOptions->firstWhere('id', (int) ($selectedProductId ?? 0));
@endphp

<div class="ta-page">
    <x-admin.list-card title="Adjustment details" desc="Search a product by name, SKU, or barcode, then record the correction.">
        <form method="POST" action="{{ route('admin.stock-adjustments.store') }}" id="adjustment-form" class="admin-form-grid">
            @csrf
            <div class="ta-field">
                <label>Location *</label>
                <select name="stock_location_id" id="stock_location_id" class="ta-select" required>
                    @foreach($locations as $loc)
                        <option value="{{ $loc->id }}" @selected((string)($selectedLocationId ?? '') === (string)$loc->id)>{{ $loc->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="ta-field">
                <label>Mode *</label>
                <select name="mode" class="ta-select">
                    <option value="delta">Adjust by (+/-)</option>
                    <option value="set">Set counted quantity</option>
                </select>
            </div>
            <div class="ta-field" style="grid-column:1/-1;">
                <label for="product_search">Product *</label>
                <div class="product-combobox" id="product-combobox">
                    <input type="hidden" name="product_id" id="product_id" value="{{ $selectedProduct['id'] ?? '' }}" required>
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
            <div id="adj_variant_wrap" class="ta-field" style="display:none;grid-column:1/-1;">
                <label>Variant</label>
                <select name="product_variant_id" id="adj_variant" class="ta-select">
                    <option value="">Select variant</option>
                </select>
            </div>
            <div class="ta-field">
                <label>Quantity / Counted *</label>
                <input type="number" name="adjustment" class="ta-input" value="{{ old('adjustment') }}" required>
                <p class="ta-muted" style="margin:6px 0 0;">For delta mode use negative for damage/loss (e.g. -1). For set mode enter the physical count.</p>
            </div>
            <div class="ta-field">
                <label>Reason *</label>
                <select name="reason" class="ta-select" required>
                    @foreach($reasons as $reason)
                        <option value="{{ $reason }}">{{ $reason }}</option>
                    @endforeach
                </select>
            </div>
            <div class="ta-field" style="grid-column:1/-1;">
                <label>Notes</label>
                <textarea name="notes" class="ta-input" rows="3">{{ old('notes') }}</textarea>
            </div>
            <div style="grid-column:1/-1;">
                <button type="submit" class="ta-btn">Record adjustment</button>
            </div>
        </form>
    </x-admin.list-card>
</div>

<script>
const availableUrl = @json(route('admin.stock-transfers.available'));
const productCatalog = @json($productOptions);
const preselectedVariantId = @json($selectedVariantId ? (string) $selectedVariantId : '');
let selectedHasVariants = @json((bool) ($selectedProduct['has_variants'] ?? false));
let selectedProductLabel = @json($selectedProduct['label'] ?? '');
let activeIndex = -1;

const productIdInput = document.getElementById('product_id');
const productSearch = document.getElementById('product_search');
const productPanel = document.getElementById('product-combobox-panel');
const variantWrap = document.getElementById('adj_variant_wrap');
const variantSel = document.getElementById('adj_variant');

function normalize(str) {
    return String(str || '').toLowerCase().trim();
}
function escapeHtml(str) {
    return String(str || '')
        .replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;').replace(/'/g, '&#39;');
}
function filterProducts(query) {
    const q = normalize(query);
    if (!q) return productCatalog.slice(0, 40);
    return productCatalog.filter((p) => {
        return normalize(p.name).includes(q)
            || normalize(p.sku).includes(q)
            || normalize(p.barcode).includes(q)
            || normalize(p.label).includes(q);
    }).slice(0, 40);
}
function renderPanel(items) {
    if (!items.length) {
        productPanel.innerHTML = '<div class="product-combobox-empty">No products match.</div>';
        return;
    }
    productPanel.innerHTML = items.map((p, i) => `
        <button type="button" class="product-combobox-option ${i === activeIndex ? 'is-active' : ''}" role="option" data-id="${p.id}" data-index="${i}">
            <strong>${escapeHtml(p.name)}</strong>
            <span>${escapeHtml([p.sku, p.barcode].filter(Boolean).join(' · ') || 'No SKU')}</span>
        </button>
    `).join('');
}
function openPanel() {
    activeIndex = -1;
    renderPanel(filterProducts(productSearch.value));
    productPanel.classList.add('is-open');
    productSearch.setAttribute('aria-expanded', 'true');
}
function closePanel() {
    productPanel.classList.remove('is-open');
    productSearch.setAttribute('aria-expanded', 'false');
    activeIndex = -1;
}
async function loadVariants(productId, preferVariantId = '') {
    variantSel.innerHTML = '<option value="">Select variant</option>';
    if (!selectedHasVariants || !productId) {
        variantWrap.style.display = 'none';
        return;
    }
    variantWrap.style.display = '';
    const loc = document.getElementById('stock_location_id')?.value || '';
    const res = await fetch(availableUrl + '?product_id=' + productId + '&location_id=' + loc);
    const data = await res.json();
    variantSel.innerHTML = '<option value="">Select variant</option>' + (data.variants || []).map(v =>
        `<option value="${v.id}" ${String(v.id) === String(preferVariantId) ? 'selected' : ''}>${escapeHtml(v.name)}</option>`
    ).join('');
}
function selectProduct(product) {
    if (!product) return;
    productIdInput.value = product.id;
    selectedProductLabel = product.label;
    selectedHasVariants = !!product.has_variants;
    productSearch.value = product.label;
    closePanel();
    loadVariants(product.id);
}
productSearch?.addEventListener('focus', openPanel);
productSearch?.addEventListener('input', () => {
    if (productSearch.value !== selectedProductLabel) {
        productIdInput.value = '';
        selectedHasVariants = false;
        variantWrap.style.display = 'none';
        variantSel.innerHTML = '';
    }
    openPanel();
});
productSearch?.addEventListener('keydown', (e) => {
    const items = [...productPanel.querySelectorAll('.product-combobox-option')];
    if (e.key === 'ArrowDown') {
        e.preventDefault();
        activeIndex = Math.min(activeIndex + 1, items.length - 1);
        items.forEach((el, i) => el.classList.toggle('is-active', i === activeIndex));
    } else if (e.key === 'ArrowUp') {
        e.preventDefault();
        activeIndex = Math.max(activeIndex - 1, 0);
        items.forEach((el, i) => el.classList.toggle('is-active', i === activeIndex));
    } else if (e.key === 'Enter') {
        e.preventDefault();
        const active = items[activeIndex] || items[0];
        if (active) {
            const product = productCatalog.find(p => String(p.id) === String(active.dataset.id));
            selectProduct(product);
        }
    } else if (e.key === 'Escape') {
        closePanel();
    }
});
productPanel?.addEventListener('mousedown', (e) => {
    const btn = e.target.closest('.product-combobox-option');
    if (!btn) return;
    e.preventDefault();
    const product = productCatalog.find(p => String(p.id) === String(btn.dataset.id));
    selectProduct(product);
});
document.addEventListener('click', (e) => {
    if (!e.target.closest('#product-combobox')) closePanel();
});
document.getElementById('stock_location_id')?.addEventListener('change', () => {
    if (productIdInput.value && selectedHasVariants) loadVariants(productIdInput.value, variantSel.value);
});
document.getElementById('adjustment-form')?.addEventListener('submit', (e) => {
    if (!productIdInput.value) {
        e.preventDefault();
        productSearch.focus();
        openPanel();
        alert('Please select a product from the search results.');
    }
});
if (productIdInput.value && selectedHasVariants) {
    loadVariants(productIdInput.value, preselectedVariantId);
}
</script>
@endsection
