@extends('layouts.admin')
@section('title', 'Add Product')

@section('page_header')
<div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
    <div>
        <nav class="mb-2 flex flex-wrap items-center gap-1.5 text-sm text-gray-400">
            <a href="{{ route('admin.dashboard') }}" class="hover:text-brand-500">Dashboard</a>
            <span>/</span>
            <a href="{{ route('admin.products.index') }}" class="hover:text-brand-500">Products</a>
            <span>/</span>
            <span class="text-gray-600 dark:text-gray-300">Add Product</span>
        </nav>
        <h1 class="text-2xl font-bold text-gray-800 dark:text-white/90">Add Product</h1>
        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Create a catalog item with pricing, barcodes, and opening stock.</p>
    </div>
    <div class="flex flex-wrap items-center gap-2">
        <a href="{{ route('admin.products.index') }}" class="ta-btn-outline">Back to products</a>
        <a href="{{ route('admin.products.export.template') }}" class="ta-btn-outline">CSV template</a>
    </div>
</div>
@endsection

@push('styles')
<style>
/* ── Shared form design tokens ─────────────────────────────── */
:root {
    --pf-gold: #a58112;
    --pf-gold-dark: #8a6c0f;
    --pf-line: #e5e7eb;
    --pf-bg: #f9fafb;
    --pf-text: #111827;
    --pf-muted: #6b7280;
    --pf-radius: 12px;
}

.pf-page { display: grid; grid-template-columns: minmax(0,1.7fr) 300px; gap: 20px; align-items: start; }
@media(max-width:960px) { .pf-page { grid-template-columns: 1fr; } }

/* Section card */
.pf-card {
    background: #fff;
    border: 1px solid var(--pf-line);
    border-radius: 16px;
    overflow: hidden;
    box-shadow: 0 1px 4px rgba(0,0,0,.05);
    margin-bottom: 16px;
}
.pf-card-head {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 16px 20px;
    border-bottom: 1px solid var(--pf-line);
    background: #fafafa;
}
.pf-card-num {
    width: 28px; height: 28px;
    border-radius: 50%;
    background: var(--pf-gold);
    color: #fff;
    font-size: 13px;
    font-weight: 800;
    display: flex; align-items: center; justify-content: center;
    flex-shrink: 0;
}
.pf-card-num.is-muted { background: #e5e7eb; color: #6b7280; font-size: 16px; }
.pf-card-title { font-size: 15px; font-weight: 700; color: var(--pf-text); margin: 0; }
.pf-card-sub { font-size: 12px; color: var(--pf-muted); margin: 2px 0 0; }
.pf-optional {
    display: inline-block;
    margin-left: 6px;
    padding: 2px 8px;
    border-radius: 999px;
    background: #f3f4f6;
    color: #6b7280;
    font-size: 10px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: .04em;
    vertical-align: middle;
}
.pf-card-body  { padding: 20px; }

.pf-jump {
    display: flex; flex-wrap: wrap; gap: 8px;
    margin-bottom: 16px;
}
.pf-jump a {
    display: inline-flex; align-items: center;
    padding: 6px 12px;
    border-radius: 999px;
    border: 1px solid #e5e7eb;
    background: #fff;
    color: #374151;
    font-size: 12px;
    font-weight: 600;
    text-decoration: none;
}
.pf-jump a:hover { border-color: var(--pf-gold); color: var(--pf-gold-dark); background: #faf7ef; }

.pf-details { margin-bottom: 16px; }
.pf-details > .pf-summary {
    list-style: none;
    cursor: pointer;
    user-select: none;
}
.pf-details > .pf-summary::-webkit-details-marker { display: none; }
.pf-details:not([open]) > .pf-summary { border-bottom: none; }
.pf-details[open] > .pf-summary .pf-chevron { transform: rotate(180deg); }
.pf-chevron { margin-left: auto; color: #9ca3af; flex-shrink: 0; transition: transform .15s; }
.pf-summary > div:nth-child(2) { flex: 1; min-width: 0; }

/* Field */
.pf-grid-2 { display: grid; grid-template-columns: 1fr 1fr; gap: 16px; }
.pf-grid-4 { display: grid; grid-template-columns: repeat(4,1fr); gap: 16px; }
.pf-grid-3 { display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 16px; }
@media(max-width:700px) { .pf-grid-2,.pf-grid-3,.pf-grid-4 { grid-template-columns: 1fr; } }

.pf-field { display: flex; flex-direction: column; gap: 6px; margin-bottom: 0; }
.pf-field + .pf-field { /* spacing handled by grid */ }
.pf-label {
    font-size: 13px; font-weight: 700; color: #374151;
    display: flex; align-items: center; gap: 4px;
}
.pf-label .req { color: #ef4444; }
.pf-input {
    padding: 10px 12px;
    border: 1px solid #d1d5db;
    border-radius: var(--pf-radius);
    font-size: 14px;
    color: var(--pf-text);
    background: #fff;
    width: 100%;
    outline: none;
    transition: border-color .15s, box-shadow .15s;
    margin: 0 !important;
}
.pf-input:focus { border-color: var(--pf-gold); box-shadow: 0 0 0 3px rgba(165,129,18,.15); }
.pf-input.is-invalid { border-color: #ef4444; box-shadow: 0 0 0 3px rgba(239,68,68,.12); }
textarea.pf-input { resize: vertical; min-height: 90px; }
.pf-help  { font-size: 11px; color: var(--pf-muted); }
.pf-error { font-size: 12px; color: #dc2626; font-weight: 600; }

/* Input with suffix */
.pf-input-wrap { position: relative; display: flex; align-items: stretch; }
.pf-input-wrap .pf-input { flex: 1; border-radius: var(--pf-radius) 0 0 var(--pf-radius); }
.pf-input-suffix {
    padding: 10px 12px;
    background: #f3f4f6;
    border: 1px solid #d1d5db;
    border-left: none;
    border-radius: 0 var(--pf-radius) var(--pf-radius) 0;
    font-size: 13px;
    font-weight: 700;
    color: var(--pf-muted);
    white-space: nowrap;
    display: flex; align-items: center;
}
.pf-input-btn {
    padding: 10px 14px;
    background: #f3f4f6;
    border: 1px solid #d1d5db;
    border-left: none;
    border-radius: 0 var(--pf-radius) var(--pf-radius) 0;
    font-size: 12px;
    font-weight: 700;
    color: var(--pf-text);
    cursor: pointer;
    white-space: nowrap;
}
.pf-input-btn:hover { background: #e5e7eb; }

/* Sale notice */
.pf-sale-notice {
    display: flex; justify-content: space-between; align-items: center;
    padding: 10px 14px;
    background: #fffbeb;
    border: 1px solid #fde68a;
    border-radius: 10px;
    font-size: 12.5px;
    color: #92400e;
    flex-wrap: wrap; gap: 8px;
}
.pf-sale-notice strong { color: #065f46; }

/* Drag-drop image zone */
.pf-upload-zone {
    border: 2px dashed #d1d5db;
    border-radius: var(--pf-radius);
    padding: 28px 20px;
    text-align: center;
    cursor: pointer;
    transition: border-color .15s, background .15s;
    background: #fafafa;
}
.pf-upload-zone:hover { border-color: var(--pf-gold); background: #fffdf0; }
.pf-upload-icon { color: var(--pf-gold); margin-bottom: 8px; }
.pf-upload-primary { font-size: 13.5px; font-weight: 700; color: var(--pf-text); }
.pf-upload-link { color: var(--pf-gold); text-decoration: underline; cursor: pointer; }
.pf-upload-sub { font-size: 11px; color: var(--pf-muted); margin-top: 4px; }

/* Stock stepper */
.pf-stepper { display: flex; align-items: center; border: 1px solid #d1d5db; border-radius: var(--pf-radius); overflow: hidden; }
.pf-stepper button {
    width: 38px; height: 38px;
    background: #f3f4f6 !important;
    border: none !important;
    font-size: 18px; font-weight: 700; color: var(--pf-text);
    cursor: pointer; border-radius: 0 !important;
    box-shadow: none !important;
    display: flex; align-items: center; justify-content: center;
    flex-shrink: 0;
    padding: 0 !important;
}
.pf-stepper button:hover { background: #e5e7eb !important; }
.pf-stepper input {
    flex: 1; border: none !important; border-radius: 0 !important;
    text-align: center; font-weight: 700; font-size: 14px;
    box-shadow: none !important; padding: 0 !important; margin: 0 !important;
}
.pf-stepper input:focus { box-shadow: none !important; border: none !important; }

/* Track checkbox */
.pf-track { display: flex; align-items: center; gap: 8px; margin-top: 10px; }
.pf-track input { width: 16px; height: 16px; accent-color: var(--pf-gold); }
.pf-track label { font-size: 13px; font-weight: 600; color: #374151; margin: 0; }

/* Right panel */
.pf-panel { position: sticky; top: 88px; }
@media(max-width:960px) { .pf-panel { position: static; } }

.pf-preview {
    background: #fff;
    border: 1px solid var(--pf-line);
    border-radius: 16px;
    overflow: hidden;
    box-shadow: 0 1px 4px rgba(0,0,0,.05);
}
.pf-preview-head {
    display: flex; align-items: center; justify-content: space-between;
    padding: 14px 18px;
    border-bottom: 1px solid var(--pf-line);
    background: #fafafa;
}
.pf-preview-head span { font-size: 14px; font-weight: 700; color: var(--pf-text); }
.pf-preview-badge { border-radius: 999px; padding: 4px 12px; font-size: 11px; font-weight: 800; text-transform: uppercase; letter-spacing: .05em; }
.pf-preview-badge.active   { background: #dcfce7; color: #166534; }
.pf-preview-badge.inactive { background: #f3f4f6; color: #4b5563; }
.pf-preview-body { padding: 16px; }

.pf-img-box {
    background: #f3f4f6;
    border: 1px dashed #d1d5db;
    border-radius: 12px;
    min-height: 160px;
    display: flex; align-items: center; justify-content: center;
    overflow: hidden; margin-bottom: 14px;
}
.pf-img-box img { width: 100%; height: 160px; object-fit: cover; border-radius: 12px; }
.pf-img-placeholder { text-align: center; color: #9ca3af; font-size: 12px; }

.pf-preview-name  { font-size: 17px; font-weight: 800; color: var(--pf-text); margin: 0 0 4px; }
.pf-preview-price { font-size: 16px; font-weight: 800; color: var(--pf-text); margin: 0 0 10px; }
.pf-meta-chips { display: flex; flex-wrap: wrap; gap: 6px; margin-bottom: 14px; }
.pf-meta-chip  { background: #f3f4f6; border: 1px solid #e5e7eb; border-radius: 999px; padding: 3px 10px; font-size: 11px; color: #374151; }

.pf-specs { border-top: 1px solid var(--pf-line); padding-top: 12px; display: grid; gap: 8px; }
.pf-spec  { display: flex; justify-content: space-between; font-size: 12.5px; }
.pf-spec-label { color: var(--pf-muted); display: flex; align-items: center; gap: 6px; }
.pf-spec-value { font-weight: 700; color: var(--pf-text); }

.pf-save-tip {
    margin: 14px 16px 16px;
    padding: 10px 12px;
    background: #fffbeb;
    border: 1px solid #fde68a;
    border-radius: 10px;
    font-size: 12px;
    color: #92400e;
    display: flex; gap: 8px; align-items: flex-start;
}

/* Bottom bar */
.pf-bottom {
    position: sticky;
    bottom: 12px;
    z-index: 40;
    background: rgba(255,255,255,.96);
    backdrop-filter: blur(8px);
    border: 1px solid var(--pf-line);
    border-radius: 16px;
    padding: 14px 20px;
    display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 12px;
    box-shadow: 0 8px 24px rgba(0,0,0,.08);
    margin-top: 8px;
}
.pf-btn-cancel {
    background: #fff;
    border: 1px solid #d1d5db;
    border-radius: 10px;
    padding: 10px 20px;
    font-size: 14px;
    font-weight: 700;
    color: #374151;
    cursor: pointer;
    text-decoration: none;
    display: inline-flex; align-items: center; gap: 6px;
}
.pf-btn-cancel:hover { background: #f9fafb; }
.pf-btn-group { display: flex; gap: 10px; align-items: center; flex-wrap: wrap; }
.pf-btn-secondary {
    background: #fff;
    border: 1px solid #d1d5db;
    border-radius: 10px;
    padding: 10px 20px;
    font-size: 14px; font-weight: 700;
    color: #374151;
    cursor: pointer;
    display: inline-flex; align-items: center; gap: 6px;
}
.pf-btn-secondary:hover { background: #f9fafb; }
.pf-btn-primary {
    background: var(--pf-gold);
    border: none;
    border-radius: 10px;
    padding: 10px 22px;
    font-size: 14px; font-weight: 800;
    color: #fff;
    cursor: pointer;
    display: inline-flex; align-items: center; gap: 8px;
    box-shadow: 0 4px 12px rgba(165,129,18,.28);
}
.pf-btn-primary:hover { background: var(--pf-gold-dark); }

.pf-hint {
    display: flex; gap: 10px; align-items: flex-start;
    padding: 12px 14px;
    border-radius: 12px;
    border: 1px solid #e5e7eb;
    background: #f9fafb;
    font-size: 13px;
    color: #4b5563;
    margin-bottom: 16px;
}
.pf-hint strong { color: #111827; }
#section-basics, #section-apparel, #variants-card, #section-pricing, #stock-adjust, #section-details, #section-specs {
    scroll-margin-top: 88px;
}
</style>
@endpush

@section('content')
@include('admin.partials.backup-reminder')
<form method="POST" action="{{ route('admin.products.store') }}" enctype="multipart/form-data" id="pf-form">
@csrf

<div class="pf-jump">
    <a href="#section-basics">Basics</a>
    <a href="#section-apparel">Apparel</a>
    <a href="#variants-card">Variants</a>
    <a href="#section-pricing">Pricing</a>
    <a href="#stock-adjust">Inventory</a>
    <a href="#section-details">Image &amp; details</a>
    <a href="#section-specs">More</a>
</div>

<div class="pf-hint">
    <svg width="18" height="18" fill="none" stroke="#a58112" stroke-width="2" viewBox="0 0 24 24" style="flex-shrink:0;margin-top:1px;"><circle cx="12" cy="12" r="10"/><path stroke-linecap="round" d="M12 8v4m0 4h.01"/></svg>
    <div>
        <strong>Quick start:</strong> Name, selling price, and opening stock are enough for POS.
        Open optional sections only when you need variants, gallery, shipping, or SEO.
    </div>
</div>

<div class="pf-page">
    <div>
        <div class="pf-card" id="section-basics">
            <div class="pf-card-head">
                <div class="pf-card-num">1</div>
                <div>
                    <h3 class="pf-card-title">Basics</h3>
                    <p class="pf-card-sub">Name, codes, and catalog grouping.</p>
                </div>
            </div>
            <div class="pf-card-body" style="display:grid;gap:16px;">
                <div class="pf-field">
                    <label class="pf-label" for="product-name">Name <span class="req">*</span></label>
                    <input id="product-name" type="text" name="name" class="pf-input @error('name') is-invalid @enderror" value="{{ old('name') }}" placeholder="e.g. Chef Kitchen Uniform Jacket" required maxlength="255" autofocus>
                    @error('name')<span class="pf-error">{{ $message }}</span>@enderror
                </div>

                <div class="pf-grid-2">
                    <div class="pf-field">
                        <label class="pf-label" for="product-sku">SKU</label>
                        <div class="pf-input-wrap">
                            <input id="product-sku" type="text" name="sku" class="pf-input @error('sku') is-invalid @enderror" value="{{ old('sku') }}" placeholder="Type manually or click Generate">
                            <button type="button" class="pf-input-btn" onclick="generateSku()">Generate</button>
                        </div>
                        <span class="pf-help">Your own code, or generate one automatically.</span>
                        @error('sku')<span class="pf-error">{{ $message }}</span>@enderror
                    </div>
                    <div class="pf-field">
                        <label class="pf-label" for="product-barcode">Barcode (primary)</label>
                        <input id="product-barcode" type="text" name="barcode" class="pf-input @error('barcode') is-invalid @enderror" value="{{ old('barcode') }}" placeholder="Click here, then scan or type" maxlength="64" inputmode="numeric">
                        @error('barcode')<span class="pf-error">{{ $message }}</span>@enderror
                    </div>
                </div>

                <div class="pf-grid-2">
                    <div class="pf-field">
                        <label class="pf-label" for="product-category">Category</label>
                        <select id="product-category" name="category_id" class="pf-input @error('category_id') is-invalid @enderror">
                            <option value="">Uncategorized</option>
                            @foreach($categories as $category)
                                <option value="{{ $category->id }}" @selected((string)old('category_id') === (string)$category->id)>{{ $category->name }}</option>
                            @endforeach
                        </select>
                        @error('category_id')<span class="pf-error">{{ $message }}</span>@enderror
                    </div>
                    <div class="pf-field">
                        <label class="pf-label" for="product-brand">Brand</label>
                        <select id="product-brand" name="brand_id" class="pf-input">
                            <option value="">— No brand —</option>
                            @foreach(($brands ?? []) as $brand)
                                <option value="{{ $brand->id }}" @selected((string)old('brand_id') === (string)$brand->id)>{{ $brand->name }}</option>
                            @endforeach
                        </select>
                        <a href="{{ route('admin.brands.index') }}" class="pf-help" style="color:#a58112;">+ Add brand</a>
                    </div>
                </div>

                <div class="pf-grid-2">
                    <div class="pf-field">
                        <label class="pf-label" for="product-supplier">Supplier</label>
                        <select id="product-supplier" name="supplier_id" class="pf-input">
                            <option value="">None</option>
                            @foreach(($suppliers ?? []) as $supplier)
                                <option value="{{ $supplier->id }}" @selected((string)old('supplier_id') === (string)$supplier->id)>{{ $supplier->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="pf-field">
                        <label class="pf-label" for="product-vendor">Marketplace vendor</label>
                        <select id="product-vendor" name="vendor_id" class="pf-input @error('vendor_id') is-invalid @enderror">
                            <option value="">Platform / In-house</option>
                            @foreach($vendors as $vendor)
                                <option value="{{ $vendor->id }}" @selected((string)old('vendor_id') === (string)$vendor->id)>{{ $vendor->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="pf-field">
                    <label class="pf-label" for="product-slug">Slug (URL)</label>
                    <input id="product-slug" type="text" name="slug" class="pf-input @error('slug') is-invalid @enderror" value="{{ old('slug') }}" placeholder="Auto-generated from name if blank">
                    @error('slug')<span class="pf-error">{{ $message }}</span>@enderror
                </div>

                <details class="pf-details" style="border:1px solid #e5e7eb;border-radius:12px;overflow:hidden;background:#fafafa;">
                    <summary class="pf-summary" style="display:flex;align-items:center;gap:10px;padding:12px 14px;">
                        <div class="pf-card-num is-muted" style="width:24px;height:24px;font-size:14px;">+</div>
                        <div style="flex:1;">
                            <div style="font-size:13px;font-weight:700;color:#111827;">POS &amp; inventory settings <span class="pf-optional">optional</span></div>
                            <div style="font-size:11px;color:#6b7280;">Extra barcodes, units, reorder, batch, expiry</div>
                        </div>
                        <svg class="pf-chevron" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/></svg>
                    </summary>
                    <div style="padding:14px;display:grid;gap:16px;background:#fff;border-top:1px solid #e5e7eb;">
                        @include('admin.products._pos_inventory_extras')
                    </div>
                </details>
            </div>
        </div>

        @include('admin.products._apparel_sections', ['collapseAdvanced' => true])

        <div class="pf-card" id="section-pricing">
            <div class="pf-card-head">
                <div class="pf-card-num">4</div>
                <div>
                    <h3 class="pf-card-title">Pricing</h3>
                    <p class="pf-card-sub">Buying, selling, wholesale, tax, and sale discount.</p>
                </div>
            </div>
            <div class="pf-card-body" style="display:grid;gap:16px;">
                <div class="pf-grid-4">
                    <div class="pf-field">
                        <label class="pf-label" for="pf-cost">Buying Price</label>
                        <input id="pf-cost" type="number" name="buying_price" class="pf-input" step="0.01" min="0" value="{{ old('buying_price', 0) }}">
                    </div>
                    <div class="pf-field">
                        <label class="pf-label" for="product-price">Selling Price <span class="req">*</span></label>
                        <input id="product-price" type="number" name="price" class="pf-input @error('price') is-invalid @enderror" step="0.01" min="0" value="{{ old('price', 0) }}" required>
                        @error('price')<span class="pf-error">{{ $message }}</span>@enderror
                    </div>
                    <div class="pf-field">
                        <label class="pf-label" for="product-wholesale">Wholesale Price</label>
                        <input id="product-wholesale" type="number" name="wholesale_price" class="pf-input" step="0.01" min="0" value="{{ old('wholesale_price') }}">
                        <span class="pf-help">Used when checkout is set to Wholesale.</span>
                    </div>
                    <div class="pf-field">
                        <label class="pf-label" for="product-tax">Tax Rate %</label>
                        <input id="product-tax" type="number" name="tax_rate" class="pf-input" step="0.01" min="0" max="100" value="{{ old('tax_rate', 16) }}">
                        <span class="pf-help">Leave 0 to use the shop default.</span>
                    </div>
                </div>
                <div class="pf-grid-2">
                    <div class="pf-field">
                        <label class="pf-label" for="product-discount-percent">Discount (%)</label>
                        <div class="pf-input-wrap">
                            <input id="product-discount-percent" type="number" name="discount_percent" class="pf-input" step="1" min="1" max="99" value="{{ old('discount_percent') }}" placeholder="e.g. 10">
                            <span class="pf-input-suffix">%</span>
                        </div>
                    </div>
                    <div class="pf-field">
                        <label class="pf-label" for="product-sale-price">Sale Price (KES)</label>
                        <input id="product-sale-price" type="number" name="sale_price" class="pf-input @error('sale_price') is-invalid @enderror" step="0.01" min="0" value="{{ old('sale_price') }}" placeholder="0.00" readonly>
                        @error('sale_price')<span class="pf-error">{{ $message }}</span>@enderror
                    </div>
                </div>
                <div class="pf-sale-notice" id="pf-sale-notice">
                    <span>Sale Price is calculated automatically: <strong>Selling Price − Discount</strong></span>
                    <span>You save: <strong id="pf-you-save">KES 0.00</strong></span>
                </div>
            </div>
        </div>

        <div class="pf-card" id="stock-adjust">
            <div class="pf-card-head">
                <div class="pf-card-num">5</div>
                <div>
                    <h3 class="pf-card-title">Opening stock</h3>
                    <p class="pf-card-sub">Set quantities per location. POS sells from Shop — put stock there to sell immediately.</p>
                </div>
            </div>
            <div class="pf-card-body" style="display:grid;gap:16px;">
                @include('admin.products._location_stock', ['stockLocations' => $stockLocations ?? \App\Models\StockLocation::orderedActive()])
            </div>
        </div>

        <div class="pf-card" id="section-details">
            <div class="pf-card-head">
                <div class="pf-card-num">6</div>
                <div>
                    <h3 class="pf-card-title">Image &amp; details</h3>
                    <p class="pf-card-sub">Main photo for POS and storefront, plus description.</p>
                </div>
            </div>
            <div class="pf-card-body" style="display:grid;gap:16px;">
                <div class="pf-field">
                    <label class="pf-label">Product image</label>
                    <div class="pf-grid-2">
                        <label class="pf-upload-zone" for="product-image-file" id="pf-drop-zone">
                            <div class="pf-upload-icon">
                                <svg width="40" height="40" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5m-13.5-9L12 3m0 0l4.5 4.5M12 3v13.5"/></svg>
                            </div>
                            <div class="pf-upload-primary">Drag &amp; drop image here</div>
                            <div class="pf-upload-primary">or <span class="pf-upload-link">click to browse</span></div>
                            <div class="pf-upload-sub">JPG, PNG or WebP up to 2MB. Shown on the POS terminal.</div>
                            <input id="product-image-file" type="file" name="image_file" accept="image/*" style="display:none;">
                        </label>
                        <div>
                            <div class="pf-label" style="margin-bottom:8px;">Image Preview</div>
                            <div class="pf-img-box" id="product-image-preview" style="height:160px;">
                                <div class="pf-img-placeholder">
                                    <svg width="36" height="36" fill="none" stroke="#d1d5db" stroke-width="1.5" viewBox="0 0 24 24"><rect x="3" y="3" width="18" height="18" rx="3"/><circle cx="8.5" cy="8.5" r="1.5"/><path stroke-linecap="round" d="M21 15l-5-5L5 21"/></svg>
                                    <div style="margin-top:6px;">No image selected</div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="pf-field" style="margin-top:12px;">
                        <label class="pf-label" for="product-image-url">Or paste image URL</label>
                        <input id="product-image-url" type="url" name="image_url" class="pf-input @error('image_url') is-invalid @enderror" value="{{ old('image_url') }}" placeholder="https://...">
                        @error('image_url')<span class="pf-error">{{ $message }}</span>@enderror
                    </div>
                </div>
                <div class="pf-field">
                    <label class="pf-label" for="product-description">Description</label>
                    <textarea id="product-description" name="description" class="pf-input" style="min-height:120px;" maxlength="1000" placeholder="Optional product description">{{ old('description') }}</textarea>
                </div>
                <div class="pf-field" style="max-width:220px;">
                    <label class="pf-label" for="product-active-sel">Status</label>
                    <select id="product-active-sel" name="is_active" class="pf-input">
                        <option value="1" {{ old('is_active', '1') == '1' ? 'selected' : '' }}>Active</option>
                        <option value="0" {{ old('is_active') == '0' ? 'selected' : '' }}>Inactive</option>
                    </select>
                </div>
            </div>
        </div>
    </div>

    <div class="pf-panel">
        <div class="pf-preview">
            <div class="pf-preview-head">
                <span>Live Preview</span>
                <span id="product-status" class="pf-preview-badge active">ACTIVE</span>
            </div>
            <div class="pf-preview-body">
                <div class="pf-img-box" id="product-image-preview-panel">
                    <div class="pf-img-placeholder">
                        <svg width="48" height="48" fill="none" stroke="#d1d5db" stroke-width="1.2" viewBox="0 0 24 24"><rect x="3" y="3" width="18" height="18" rx="3"/><circle cx="8.5" cy="8.5" r="1.5"/><path stroke-linecap="round" d="M21 15l-5-5L5 21"/></svg>
                    </div>
                </div>
                <p id="product-name-preview" class="pf-preview-name">Product Name Preview</p>
                <p id="product-price-preview" class="pf-preview-price">KES 0.00</p>
                <div class="pf-meta-chips">
                    <span id="product-meta-category" class="pf-meta-chip">Uncategorized</span>
                    <span id="product-meta-vendor" class="pf-meta-chip">Platform / In-house</span>
                    <span id="product-meta-stock" class="pf-meta-chip">Stock: 0</span>
                </div>
                <div class="pf-specs">
                    <div class="pf-spec">
                        <span class="pf-spec-label">SKU</span>
                        <span id="pf-prev-sku" class="pf-spec-value">—</span>
                    </div>
                    <div class="pf-spec">
                        <span class="pf-spec-label">Barcode</span>
                        <span id="pf-prev-barcode" class="pf-spec-value">—</span>
                    </div>
                    <div class="pf-spec">
                        <span class="pf-spec-label">Category</span>
                        <span id="pf-prev-category" class="pf-spec-value">Uncategorized</span>
                    </div>
                    <div class="pf-spec">
                        <span class="pf-spec-label">Vendor</span>
                        <span id="pf-prev-vendor" class="pf-spec-value">Platform / In-house</span>
                    </div>
                    <div class="pf-spec">
                        <span class="pf-spec-label">Status</span>
                        <span id="pf-prev-status" class="pf-spec-value" style="color:#166534;">Active</span>
                    </div>
                    <div class="pf-spec">
                        <span class="pf-spec-label">Stock</span>
                        <span id="pf-prev-stock-val" class="pf-spec-value">0</span>
                    </div>
                    <div class="pf-spec">
                        <span class="pf-spec-label">Min. Stock</span>
                        <span id="pf-prev-min-stock" class="pf-spec-value">5</span>
                    </div>
                </div>
            </div>
            <div class="pf-save-tip">
                <svg width="16" height="16" fill="none" stroke="#d97706" stroke-width="2" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><path stroke-linecap="round" d="M12 8v4m0 4h.01"/></svg>
                <span>Once saved, this product is available in store and POS. Put opening stock on <strong>Shop</strong> to sell right away.</span>
            </div>
        </div>
    </div>
</div>

<div class="pf-bottom">
    <a href="{{ route('admin.products.index') }}" class="pf-btn-cancel">
        <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
        Cancel
    </a>
    <div class="pf-btn-group">
        <button type="submit" name="_add_another" value="1" class="pf-btn-secondary">Save &amp; Add Another</button>
        <button type="submit" class="pf-btn-primary">
            <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
            Save Product
        </button>
    </div>
</div>

</form>

<script>
(function () {
    /* Live preview wiring */
    const nameInput     = document.getElementById('product-name');
    const priceInput    = document.getElementById('product-price');
    const saleInput     = document.getElementById('product-sale-price');
    const stockInput    = document.getElementById('product-stock');
    const minStockInput = document.getElementById('pf-reorder');
    const categoryInput = document.getElementById('product-category');
    const vendorInput   = document.getElementById('product-vendor');
    const statusInput   = document.getElementById('product-active-sel');
    const urlInput      = document.getElementById('product-image-url');
    const fileInput     = document.getElementById('product-image-file');
    const skuInput      = document.getElementById('product-sku');
    const barcodeInput  = document.getElementById('product-barcode');
    const pctInput      = document.getElementById('product-discount-percent');

    const namePreview     = document.getElementById('product-name-preview');
    const pricePreview    = document.getElementById('product-price-preview');
    const statusBadge     = document.getElementById('product-status');
    const catPreview      = document.getElementById('product-meta-category');
    const vendorPreview   = document.getElementById('product-meta-vendor');
    const stockPreview    = document.getElementById('product-meta-stock');
    const prevSku         = document.getElementById('pf-prev-sku');
    const prevBarcode     = document.getElementById('pf-prev-barcode');
    const prevCategory    = document.getElementById('pf-prev-category');
    const prevVendor      = document.getElementById('pf-prev-vendor');
    const prevStatus      = document.getElementById('pf-prev-status');
    const prevStockVal    = document.getElementById('pf-prev-stock-val');
    const prevMinStock    = document.getElementById('pf-prev-min-stock');
    const youSave         = document.getElementById('pf-you-save');
    const imgPreview      = document.getElementById('product-image-preview');
    const imgPanel        = document.getElementById('product-image-preview-panel');

    function fmt(v) {
        const n = parseFloat(v);
        return isNaN(n) ? 'KES 0.00' : 'KES ' + n.toFixed(2);
    }
    function selText(el, fallback) {
        if (!el) return fallback;
        const o = el.options[el.selectedIndex];
        return o && o.text ? o.text : fallback;
    }
    function setImg(src) {
        [imgPreview, imgPanel].forEach(box => {
            if (!box) return;
            box.innerHTML = '';
            if (src) {
                const img = document.createElement('img');
                img.src = src; img.alt = 'Preview';
                img.style.cssText = 'width:100%;height:160px;object-fit:cover;border-radius:12px;';
                box.appendChild(img);
            } else {
                box.innerHTML = '<div class="pf-img-placeholder"><svg width="40" height="40" fill="none" stroke="#d1d5db" stroke-width="1.5" viewBox="0 0 24 24"><rect x="3" y="3" width="18" height="18" rx="3"/><circle cx="8.5" cy="8.5" r="1.5"/><path stroke-linecap="round" d="M21 15l-5-5L5 21"/></svg><div style="margin-top:6px;">No image selected</div></div>';
            }
        });
    }

    function update() {
        const name   = nameInput?.value.trim() || '';
        const price  = parseFloat(priceInput?.value) || 0;
        const sale   = parseFloat(saleInput?.value) || 0;
        const stock  = stockInput?.value || '0';
        const minSt  = minStockInput?.value || '5';
        const active = statusInput?.value !== '0';

        namePreview.textContent = name || 'Product Name Preview';
        pricePreview.textContent = fmt(sale > 0 && sale < price ? sale : price);

        const saving = sale > 0 && sale < price ? (price - sale) : 0;
        if (youSave) youSave.textContent = 'KES ' + saving.toFixed(2);

        const isActive = active;
        statusBadge.className = 'pf-preview-badge ' + (isActive ? 'active' : 'inactive');
        statusBadge.textContent = isActive ? 'ACTIVE' : 'INACTIVE';
        if (prevStatus) { prevStatus.textContent = isActive ? 'Active' : 'Inactive'; prevStatus.style.color = isActive ? '#166534' : '#4b5563'; }

        const catText = selText(categoryInput, 'Uncategorized');
        const venText = selText(vendorInput, 'Platform / In-house');
        catPreview.textContent = catText;
        vendorPreview.textContent = venText;
        stockPreview.textContent = 'Stock: ' + stock;
        if (prevSku) prevSku.textContent = skuInput?.value.trim() || '—';
        if (prevBarcode) prevBarcode.textContent = barcodeInput?.value.trim() || '—';
        if (prevCategory) prevCategory.textContent = catText;
        if (prevVendor) prevVendor.textContent = venText;
        if (prevStockVal) prevStockVal.textContent = stock;
        if (prevMinStock) prevMinStock.textContent = minSt;

        const url = urlInput?.value.trim();
        if (url) setImg(url);
    }

    /* Discount ↔ sale price sync */
    let syncing = false;
    pctInput?.addEventListener('input', () => {
        if (syncing) return; syncing = true;
        const p = parseFloat(priceInput.value), d = parseFloat(pctInput.value);
        if (p > 0 && d > 0 && d < 100) saleInput.value = (p * (1 - d/100)).toFixed(2); else if (!pctInput.value) saleInput.value = '';
        syncing = false; update();
    });
    saleInput?.addEventListener('input', () => {
        saleInput.removeAttribute('readonly');
        if (syncing) return; syncing = true;
        const p = parseFloat(priceInput.value), s = parseFloat(saleInput.value);
        if (p > 0 && s > 0 && s < p) pctInput.value = String(Math.round((1 - s/p)*100)); else if (!saleInput.value) pctInput.value = '';
        syncing = false; update();
    });
    priceInput?.addEventListener('input', () => { if (pctInput.value) pctInput.dispatchEvent(new Event('input')); update(); });

    /* File upload */
    fileInput?.addEventListener('change', () => {
        if (fileInput.files?.[0]) {
            const r = new FileReader();
            r.onload = e => setImg(e.target.result);
            r.readAsDataURL(fileInput.files[0]);
        } else setImg(null);
    });

    /* Drag-drop */
    const dropZone = document.getElementById('pf-drop-zone');
    ['dragenter','dragover'].forEach(e => dropZone?.addEventListener(e, ev => { ev.preventDefault(); dropZone.style.borderColor='#a58112'; }));
    ['dragleave','drop'].forEach(e => dropZone?.addEventListener(e, ev => { ev.preventDefault(); dropZone.style.borderColor=''; }));
    dropZone?.addEventListener('drop', ev => {
        const file = ev.dataTransfer?.files?.[0];
        if (file && fileInput) { const dt = new DataTransfer(); dt.items.add(file); fileInput.files = dt.files; fileInput.dispatchEvent(new Event('change')); }
    });

    /* Short desc counter */
    const sc = document.getElementById('pf-short-desc');
    const scC = document.getElementById('pf-sc-count');
    sc?.addEventListener('input', () => { if(scC) scC.textContent = sc.value.length; });
    const dc = document.getElementById('product-description');
    const dcC = document.getElementById('pf-dc-count');
    dc?.addEventListener('input', () => { if(dcC) dcC.textContent = dc.value.length; });

    [nameInput, stockInput, minStockInput, categoryInput, vendorInput, statusInput, urlInput, skuInput, barcodeInput]
        .forEach(el => el?.addEventListener('input', update));
    [categoryInput, vendorInput, statusInput].forEach(el => el?.addEventListener('change', update));

    update();
})();

function stepStock(delta) {
    const el = document.getElementById('product-stock');
    if (!el) return;
    el.value = Math.max(0, (parseInt(el.value)||0) + delta);
    el.dispatchEvent(new Event('input'));
}
async function generateSku() {
    const el = document.getElementById('product-sku');
    if (!el) return;
    const name = document.getElementById('product-name')?.value || 'PROD';
    try {
        const res = await fetch('{{ route('admin.products.generate-sku') }}?name=' + encodeURIComponent(name), {
            headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
        });
        const data = await res.json();
        if (data.sku) {
            el.value = data.sku;
            el.dispatchEvent(new Event('input'));
        }
    } catch (e) {
        const prefix = (name || 'PROD').replace(/[^A-Za-z0-9]/g, '').slice(0, 8).toUpperCase() || 'PROD';
        el.value = prefix + '-' + new Date().toISOString().slice(2, 10).replace(/-/g, '') + '-' + Math.random().toString(36).slice(2, 6).toUpperCase();
        el.dispatchEvent(new Event('input'));
    }
}
</script>
@endsection