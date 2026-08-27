@extends('layouts.admin')
@section('title', 'Edit Product')
@section('heading', 'Edit Product')
@section('subheading', 'Dashboard › Products › ' . $product->name)

@push('styles')
<style>
/* Reuse the same pf- design tokens from create page */
:root { --pf-gold:#d4af37; --pf-gold-dark:#b8942d; --pf-line:#e5e7eb; --pf-bg:#f9fafb; --pf-text:#111827; --pf-muted:#6b7280; --pf-radius:12px; }
.pf-page { display:grid; grid-template-columns:minmax(0,1.7fr) 300px; gap:20px; align-items:start; }
@media(max-width:960px) { .pf-page { grid-template-columns:1fr; } }
.pf-card { background:#fff; border:1px solid var(--pf-line); border-radius:16px; overflow:hidden; box-shadow:0 1px 4px rgba(0,0,0,.05); margin-bottom:16px; }
.pf-card-head { display:flex; align-items:center; gap:12px; padding:16px 20px; border-bottom:1px solid var(--pf-line); background:#fafafa; }
.pf-card-num { width:28px; height:28px; border-radius:50%; background:var(--pf-gold); color:#1a1300; font-size:13px; font-weight:800; display:flex; align-items:center; justify-content:center; flex-shrink:0; }
.pf-card-title { font-size:15px; font-weight:700; color:var(--pf-text); margin:0; }
.pf-card-body { padding:20px; }
.pf-grid-2 { display:grid; grid-template-columns:1fr 1fr; gap:16px; }
.pf-grid-4 { display:grid; grid-template-columns:repeat(4,1fr); gap:16px; }
.pf-grid-3 { display:grid; grid-template-columns:1fr 1fr 1fr; gap:16px; }
@media(max-width:700px) { .pf-grid-2,.pf-grid-3,.pf-grid-4 { grid-template-columns:1fr; } }
.pf-field { display:flex; flex-direction:column; gap:6px; }
.pf-label { font-size:13px; font-weight:700; color:#374151; display:flex; align-items:center; gap:4px; }
.pf-label .req { color:#ef4444; }
.pf-input { padding:10px 12px; border:1px solid #d1d5db; border-radius:var(--pf-radius); font-size:14px; color:var(--pf-text); background:#fff; width:100%; outline:none; transition:border-color .15s,box-shadow .15s; margin:0 !important; }
.pf-input:focus { border-color:var(--pf-gold); box-shadow:0 0 0 3px rgba(212,175,55,.15); }
.pf-input.is-invalid { border-color:#ef4444; }
textarea.pf-input { resize:vertical; min-height:90px; }
.pf-help { font-size:11px; color:var(--pf-muted); }
.pf-error { font-size:12px; color:#dc2626; font-weight:600; }
.pf-input-wrap { position:relative; display:flex; align-items:stretch; }
.pf-input-wrap .pf-input { flex:1; border-radius:var(--pf-radius) 0 0 var(--pf-radius); }
.pf-input-suffix { padding:10px 12px; background:#f3f4f6; border:1px solid #d1d5db; border-left:none; border-radius:0 var(--pf-radius) var(--pf-radius) 0; font-size:13px; font-weight:700; color:var(--pf-muted); display:flex; align-items:center; }
.pf-sale-notice { display:flex; justify-content:space-between; align-items:center; padding:10px 14px; background:#fffbeb; border:1px solid #fde68a; border-radius:10px; font-size:12.5px; color:#92400e; flex-wrap:wrap; gap:8px; }
.pf-upload-zone { border:2px dashed #d1d5db; border-radius:var(--pf-radius); padding:24px 20px; text-align:center; cursor:pointer; background:#fafafa; transition:border-color .15s; }
.pf-upload-zone:hover { border-color:var(--pf-gold); background:#fffdf0; }
.pf-stepper { display:flex; align-items:center; border:1px solid #d1d5db; border-radius:var(--pf-radius); overflow:hidden; }
.pf-stepper button { width:38px; height:38px; background:#f3f4f6 !important; border:none !important; font-size:18px; font-weight:700; color:var(--pf-text); cursor:pointer; border-radius:0 !important; box-shadow:none !important; display:flex; align-items:center; justify-content:center; flex-shrink:0; padding:0 !important; }
.pf-stepper button:hover { background:#e5e7eb !important; }
.pf-stepper input { flex:1; border:none !important; border-radius:0 !important; text-align:center; font-weight:700; font-size:14px; box-shadow:none !important; padding:0 !important; margin:0 !important; }
.pf-track { display:flex; align-items:center; gap:8px; margin-top:10px; }
.pf-track input { width:16px; height:16px; accent-color:var(--pf-gold); }
.pf-track label { font-size:13px; font-weight:600; color:#374151; margin:0; }
.pf-panel { position:sticky; top:88px; }
@media(max-width:960px) { .pf-panel { position:static; } }
.pf-preview { background:#fff; border:1px solid var(--pf-line); border-radius:16px; overflow:hidden; box-shadow:0 1px 4px rgba(0,0,0,.05); }
.pf-preview-head { display:flex; align-items:center; justify-content:space-between; padding:14px 18px; border-bottom:1px solid var(--pf-line); background:#fafafa; }
.pf-preview-head span { font-size:14px; font-weight:700; color:var(--pf-text); }
.pf-preview-badge { border-radius:999px; padding:4px 12px; font-size:11px; font-weight:800; text-transform:uppercase; letter-spacing:.05em; }
.pf-preview-badge.active { background:#dcfce7; color:#166534; }
.pf-preview-badge.inactive { background:#f3f4f6; color:#4b5563; }
.pf-preview-body { padding:16px; }
.pf-img-box { background:#f3f4f6; border:1px dashed #d1d5db; border-radius:12px; min-height:160px; display:flex; align-items:center; justify-content:center; overflow:hidden; margin-bottom:14px; }
.pf-img-box img { width:100%; height:160px; object-fit:cover; border-radius:12px; }
.pf-img-placeholder { text-align:center; color:#9ca3af; font-size:12px; }
.pf-preview-name { font-size:17px; font-weight:800; color:var(--pf-text); margin:0 0 4px; }
.pf-preview-price { font-size:16px; font-weight:800; color:var(--pf-text); margin:0 0 10px; }
.pf-meta-chips { display:flex; flex-wrap:wrap; gap:6px; margin-bottom:14px; }
.pf-meta-chip { background:#f3f4f6; border:1px solid #e5e7eb; border-radius:999px; padding:3px 10px; font-size:11px; color:#374151; }
.pf-specs { border-top:1px solid var(--pf-line); padding-top:12px; display:grid; gap:8px; }
.pf-spec { display:flex; justify-content:space-between; font-size:12.5px; }
.pf-spec-label { color:var(--pf-muted); display:flex; align-items:center; gap:6px; }
.pf-spec-value { font-weight:700; color:var(--pf-text); }
.pf-save-tip { margin:14px 16px 16px; padding:10px 12px; background:#fffbeb; border:1px solid #fde68a; border-radius:10px; font-size:12px; color:#92400e; display:flex; gap:8px; align-items:flex-start; }
.pf-bottom { background:#fff; border:1px solid var(--pf-line); border-radius:16px; padding:16px 20px; display:flex; align-items:center; justify-content:space-between; flex-wrap:wrap; gap:12px; box-shadow:0 1px 4px rgba(0,0,0,.05); }
.pf-btn-cancel { background:#fff; border:1px solid #d1d5db; border-radius:10px; padding:10px 20px; font-size:14px; font-weight:700; color:#374151; cursor:pointer; text-decoration:none; display:inline-flex; align-items:center; gap:6px; }
.pf-btn-cancel:hover { background:#f9fafb; }
.pf-btn-group { display:flex; gap:10px; align-items:center; }
.pf-btn-danger { background:#fff; border:1px solid #fee2e2; border-radius:10px; padding:10px 16px; font-size:14px; font-weight:700; color:#dc2626; cursor:pointer; display:inline-flex; align-items:center; gap:6px; }
.pf-btn-danger:hover { background:#fee2e2; }
.pf-btn-primary { background:linear-gradient(135deg,#d4af37,#b8942d); border:none; border-radius:10px; padding:10px 22px; font-size:14px; font-weight:800; color:#1a1300; cursor:pointer; display:inline-flex; align-items:center; gap:8px; box-shadow:0 4px 12px rgba(212,175,55,.3); }
.pf-btn-primary:hover { opacity:.92; }
</style>
@endpush

@section('content')
<form method="POST" action="{{ route('admin.products.update', $product) }}" enctype="multipart/form-data" id="pf-form">
@csrf @method('PUT')

<div class="pf-page">
    {{-- Left --}}
    <div>
        {{-- 1. Basic Information --}}
        <div class="pf-card">
            <div class="pf-card-head">
                <div class="pf-card-num">1</div>
                <h3 class="pf-card-title">Basic Information</h3>
            </div>
            <div class="pf-card-body" style="display:grid;gap:16px;">
                <div class="pf-grid-2">
                    <div class="pf-field">
                        <label class="pf-label" for="product-category">Category</label>
                        <select id="product-category" name="category_id" class="pf-input">
                            <option value="">Uncategorized</option>
                            @foreach($categories as $cat)
                                <option value="{{ $cat->id }}" @selected((string)old('category_id',$product->category_id) === (string)$cat->id)>{{ $cat->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="pf-field">
                        <label class="pf-label" for="product-vendor">Vendor</label>
                        <select id="product-vendor" name="vendor_id" class="pf-input">
                            <option value="">Platform / In-house</option>
                            @foreach($vendors as $vendor)
                                <option value="{{ $vendor->id }}" @selected((string)old('vendor_id',$product->vendor_id) === (string)$vendor->id)>{{ $vendor->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="pf-grid-2">
                    <div class="pf-field">
                        <label class="pf-label" for="product-name">Product Name <span class="req">*</span></label>
                        <input id="product-name" type="text" name="name" class="pf-input @error('name') is-invalid @enderror" value="{{ old('name',$product->name) }}" required maxlength="255">
                        @error('name')<span class="pf-error">{{ $message }}</span>@enderror
                    </div>
                    <div class="pf-field">
                        <label class="pf-label" for="product-slug">Slug (URL)</label>
                        <input id="product-slug" type="text" name="slug" class="pf-input" value="{{ old('slug',$product->slug) }}">
                    </div>
                </div>
                <div class="pf-field">
                    <label class="pf-label" for="product-description">Description</label>
                    <textarea id="product-description" name="description" class="pf-input" style="min-height:110px;">{{ old('description',$product->description) }}</textarea>
                </div>
                <div class="pf-field">
                    <label class="pf-label" for="product-active-sel">Status</label>
                    <select id="product-active-sel" name="is_active" class="pf-input" style="max-width:200px;">
                        <option value="1" @selected(old('is_active',$product->is_active ? '1':'0') === '1')>Active</option>
                        <option value="0" @selected(old('is_active',$product->is_active ? '1':'0') === '0')>Inactive</option>
                    </select>
                </div>
            </div>
        </div>

        {{-- 2. Pricing --}}
        <div class="pf-card">
            <div class="pf-card-head">
                <div class="pf-card-num">2</div>
                <h3 class="pf-card-title">Pricing</h3>
            </div>
            <div class="pf-card-body" style="display:grid;gap:16px;">
                <div class="pf-grid-3">
                    <div class="pf-field">
                        <label class="pf-label" for="product-price">Regular Price (KES) <span class="req">*</span></label>
                        <input id="product-price" type="number" name="price" class="pf-input @error('price') is-invalid @enderror" step="0.01" min="0" value="{{ old('price',$product->price) }}" required>
                        @error('price')<span class="pf-error">{{ $message }}</span>@enderror
                    </div>
                    <div class="pf-field">
                        <label class="pf-label" for="product-discount-percent">Discount (%)</label>
                        <div class="pf-input-wrap">
                            <input id="product-discount-percent" type="number" name="discount_percent" class="pf-input" step="1" min="1" max="99" value="{{ old('discount_percent', $product->hasSale() ? $product->discountPercent() : '') }}">
                            <span class="pf-input-suffix">%</span>
                        </div>
                    </div>
                    <div class="pf-field">
                        <label class="pf-label" for="product-sale-price">Sale Price (KES)</label>
                        <input id="product-sale-price" type="number" name="sale_price" class="pf-input @error('sale_price') is-invalid @enderror" step="0.01" min="0" value="{{ old('sale_price',$product->sale_price) }}">
                        @error('sale_price')<span class="pf-error">{{ $message }}</span>@enderror
                    </div>
                </div>
                <div class="pf-sale-notice">
                    <span>Sale Price = Regular Price − Discount %</span>
                    <span>You save: <strong id="pf-you-save">KES {{ number_format((float)$product->price - (float)($product->sale_price ?? $product->price), 2) }}</strong></span>
                </div>
            </div>
        </div>

        {{-- 3. Inventory --}}
        <div class="pf-card">
            <div class="pf-card-head">
                <div class="pf-card-num">3</div>
                <h3 class="pf-card-title">Inventory</h3>
            </div>
            <div class="pf-card-body" style="display:grid;gap:16px;">
                <div class="pf-grid-2">
                    <div class="pf-field">
                        <label class="pf-label" for="product-sku">SKU</label>
                        <input id="product-sku" type="text" name="sku" class="pf-input @error('sku') is-invalid @enderror" value="{{ old('sku',$product->sku) }}">
                        @error('sku')<span class="pf-error">{{ $message }}</span>@enderror
                    </div>
                    <div class="pf-field">
                        <label class="pf-label" for="product-barcode">Barcode</label>
                        <input id="product-barcode" type="text" name="barcode" class="pf-input @error('barcode') is-invalid @enderror" value="{{ old('barcode',$product->barcode) }}" maxlength="64" inputmode="numeric">
                        @error('barcode')<span class="pf-error">{{ $message }}</span>@enderror
                    </div>
                </div>
                <div class="pf-field">
                    <label class="pf-label" for="product-stock">Stock Quantity <span class="req">*</span></label>
                    <div class="pf-stepper" style="max-width:180px;">
                        <button type="button" onclick="stepStock(-1)">−</button>
                        <input id="product-stock" type="number" name="stock" value="{{ old('stock',$product->stock) }}" min="0" required>
                        <button type="button" onclick="stepStock(1)">+</button>
                    </div>
                    @error('stock')<span class="pf-error">{{ $message }}</span>@enderror
                </div>
            </div>
        </div>

        {{-- 4. Image --}}
        <div class="pf-card">
            <div class="pf-card-head">
                <div class="pf-card-num">4</div>
                <h3 class="pf-card-title">Product Image</h3>
            </div>
            <div class="pf-card-body">
                <div class="pf-grid-2">
                    <label class="pf-upload-zone" for="product-image-file">
                        <svg width="36" height="36" fill="none" stroke="#d4af37" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5m-13.5-9L12 3m0 0l4.5 4.5M12 3v13.5"/></svg>
                        <div style="font-size:13.5px;font-weight:700;margin-top:8px;">Drag &amp; drop or <span style="color:#d4af37;text-decoration:underline;">click to browse</span></div>
                        <div style="font-size:11px;color:#9ca3af;margin-top:4px;">PNG, JPG, WEBP up to 5MB</div>
                        <input id="product-image-file" type="file" name="image_file" accept="image/*" style="display:none;">
                    </label>
                    <div>
                        <div class="pf-label" style="margin-bottom:8px;">Current Image</div>
                        <div class="pf-img-box" id="product-image-preview" style="height:160px;">
                            @if($product->image_url)
                                <img src="{{ $product->image_url }}" alt="{{ $product->name }}" style="width:100%;height:160px;object-fit:cover;border-radius:12px;">
                            @else
                                <div class="pf-img-placeholder">
                                    <svg width="36" height="36" fill="none" stroke="#d1d5db" stroke-width="1.5" viewBox="0 0 24 24"><rect x="3" y="3" width="18" height="18" rx="3"/><circle cx="8.5" cy="8.5" r="1.5"/><path stroke-linecap="round" d="M21 15l-5-5L5 21"/></svg>
                                    <div style="margin-top:6px;">No image</div>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
                <div class="pf-field" style="margin-top:12px;">
                    <label class="pf-label" for="product-image-url">Or paste image URL</label>
                    <input id="product-image-url" type="url" name="image_url" class="pf-input @error('image_url') is-invalid @enderror" value="{{ old('image_url',$product->image_url) }}" placeholder="https://...">
                    @error('image_url')<span class="pf-error">{{ $message }}</span>@enderror
                </div>
            </div>
        </div>
    </div>

    {{-- Right panel --}}
    <div class="pf-panel">
        <div class="pf-preview">
            <div class="pf-preview-head">
                <span>Live Preview</span>
                <span id="product-status" class="pf-preview-badge {{ $product->is_active ? 'active' : 'inactive' }}">{{ $product->is_active ? 'ACTIVE' : 'INACTIVE' }}</span>
            </div>
            <div class="pf-preview-body">
                <div class="pf-img-box" id="product-image-preview-panel">
                    @if($product->image_url)
                        <img src="{{ $product->image_url }}" alt="{{ $product->name }}" style="width:100%;height:160px;object-fit:cover;border-radius:12px;">
                    @else
                        <div class="pf-img-placeholder">
                            <svg width="48" height="48" fill="none" stroke="#d1d5db" stroke-width="1.2" viewBox="0 0 24 24"><rect x="3" y="3" width="18" height="18" rx="3"/><circle cx="8.5" cy="8.5" r="1.5"/><path stroke-linecap="round" d="M21 15l-5-5L5 21"/></svg>
                        </div>
                    @endif
                </div>
                <p id="product-name-preview" class="pf-preview-name">{{ $product->name }}</p>
                <p id="product-price-preview" class="pf-preview-price">KES {{ number_format($product->currentPrice(), 2) }}</p>
                <div class="pf-meta-chips">
                    <span id="product-meta-category" class="pf-meta-chip">{{ $product->category?->name ?? 'Uncategorized' }}</span>
                    <span id="product-meta-vendor" class="pf-meta-chip">{{ $product->vendor?->name ?? 'Platform / In-house' }}</span>
                    <span id="product-meta-stock" class="pf-meta-chip">Stock: {{ $product->stock }}</span>
                </div>
                <div class="pf-specs">
                    <div class="pf-spec"><span class="pf-spec-label">SKU</span><span id="pf-prev-sku" class="pf-spec-value">{{ $product->sku ?: '—' }}</span></div>
                    <div class="pf-spec"><span class="pf-spec-label">Barcode</span><span id="pf-prev-barcode" class="pf-spec-value">{{ $product->barcode ?: '—' }}</span></div>
                    <div class="pf-spec"><span class="pf-spec-label">Stock</span><span id="pf-prev-stock-val" class="pf-spec-value">{{ $product->stock }}</span></div>
                </div>
            </div>
            <div class="pf-save-tip">
                <svg width="16" height="16" fill="none" stroke="#d97706" stroke-width="2" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><path stroke-linecap="round" d="M12 8v4m0 4h.01"/></svg>
                <span>Changes will be reflected in the store and POS after saving.</span>
            </div>
        </div>
    </div>
</div>

{{-- Bottom bar --}}
<div class="pf-bottom">
    <a href="{{ route('admin.products.index') }}" class="pf-btn-cancel">
        <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
        Cancel
    </a>
    <div class="pf-btn-group">
        <form method="POST" action="{{ route('admin.products.destroy', $product) }}" style="margin:0;" onsubmit="return confirm('Delete this product permanently?')">
            @csrf @method('DELETE')
            <button type="submit" class="pf-btn-danger">
                <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                Delete Product
            </button>
        </form>
        <button type="submit" class="pf-btn-primary">
            <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
            Update Product
        </button>
    </div>
</div>

</form>

<script>
(function(){
    const nameInput = document.getElementById('product-name');
    const priceInput = document.getElementById('product-price');
    const saleInput = document.getElementById('product-sale-price');
    const stockInput = document.getElementById('product-stock');
    const categoryInput = document.getElementById('product-category');
    const vendorInput = document.getElementById('product-vendor');
    const statusInput = document.getElementById('product-active-sel');
    const urlInput = document.getElementById('product-image-url');
    const fileInput = document.getElementById('product-image-file');
    const skuInput = document.getElementById('product-sku');
    const barcodeInput = document.getElementById('product-barcode');
    const pctInput = document.getElementById('product-discount-percent');

    function fmt(v){ const n=parseFloat(v); return isNaN(n)?'KES 0.00':'KES '+n.toFixed(2); }
    function selText(el,fb){ if(!el)return fb; const o=el.options[el.selectedIndex]; return o&&o.text?o.text:fb; }
    function setImg(src){
        [document.getElementById('product-image-preview'), document.getElementById('product-image-preview-panel')].forEach(box=>{
            if(!box)return; box.innerHTML='';
            if(src){ const img=document.createElement('img'); img.src=src; img.style.cssText='width:100%;height:160px;object-fit:cover;border-radius:12px;'; box.appendChild(img); }
            else { box.innerHTML='<div class="pf-img-placeholder"><svg width="40" height="40" fill="none" stroke="#d1d5db" stroke-width="1.5" viewBox="0 0 24 24"><rect x="3" y="3" width="18" height="18" rx="3"/></svg><div>No image</div></div>'; }
        });
    }
    function update(){
        const name=nameInput?.value.trim()||'';
        const price=parseFloat(priceInput?.value)||0;
        const sale=parseFloat(saleInput?.value)||0;
        const stock=stockInput?.value||'0';
        const active=statusInput?.value!=='0';
        document.getElementById('product-name-preview').textContent=name||'Product Name Preview';
        document.getElementById('product-price-preview').textContent=fmt(sale>0&&sale<price?sale:price);
        const youSave=document.getElementById('pf-you-save');
        if(youSave)youSave.textContent='KES '+(sale>0&&sale<price?(price-sale):0).toFixed(2);
        const badge=document.getElementById('product-status');
        badge.className='pf-preview-badge '+(active?'active':'inactive');
        badge.textContent=active?'ACTIVE':'INACTIVE';
        document.getElementById('product-meta-category').textContent=selText(categoryInput,'Uncategorized');
        document.getElementById('product-meta-vendor').textContent=selText(vendorInput,'Platform / In-house');
        document.getElementById('product-meta-stock').textContent='Stock: '+stock;
        const prevSku=document.getElementById('pf-prev-sku');
        if(prevSku)prevSku.textContent=skuInput?.value.trim()||'—';
        const prevBarcode=document.getElementById('pf-prev-barcode');
        if(prevBarcode)prevBarcode.textContent=barcodeInput?.value.trim()||'—';
        const prevStock=document.getElementById('pf-prev-stock-val');
        if(prevStock)prevStock.textContent=stock;
        const url=urlInput?.value.trim();
        if(url)setImg(url);
    }
    let syncing=false;
    pctInput?.addEventListener('input',()=>{ if(syncing)return; syncing=true; const p=parseFloat(priceInput.value),d=parseFloat(pctInput.value); if(p>0&&d>0&&d<100)saleInput.value=(p*(1-d/100)).toFixed(2); else if(!pctInput.value)saleInput.value=''; syncing=false; update(); });
    saleInput?.addEventListener('input',()=>{ if(syncing)return; syncing=true; const p=parseFloat(priceInput.value),s=parseFloat(saleInput.value); if(p>0&&s>0&&s<p)pctInput.value=String(Math.round((1-s/p)*100)); else if(!saleInput.value)pctInput.value=''; syncing=false; update(); });
    priceInput?.addEventListener('input',()=>{ if(pctInput.value)pctInput.dispatchEvent(new Event('input')); update(); });
    fileInput?.addEventListener('change',()=>{ if(fileInput.files?.[0]){ const r=new FileReader(); r.onload=e=>setImg(e.target.result); r.readAsDataURL(fileInput.files[0]); } });
    [nameInput,stockInput,categoryInput,vendorInput,statusInput,urlInput,skuInput,barcodeInput].forEach(el=>el?.addEventListener('input',update));
    [categoryInput,vendorInput,statusInput].forEach(el=>el?.addEventListener('change',update));
    update();
})();
function stepStock(d){ const el=document.getElementById('product-stock'); if(!el)return; el.value=Math.max(0,(parseInt(el.value)||0)+d); el.dispatchEvent(new Event('input')); }
</script>
@endsection
