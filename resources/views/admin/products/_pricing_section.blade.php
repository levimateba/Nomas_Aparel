@php
    $p = $product ?? null;
    $buyingId = $p ? 'product-buying-price' : 'pf-cost';
@endphp
<div class="pf-card" id="section-pricing">
    <div class="pf-card-head">
        <div class="pf-card-num">3</div>
        <div>
            <h3 class="pf-card-title">Pricing</h3>
            <p class="pf-card-sub">Default buying / selling / wholesale. Fill this before generating variants — Generate copies these into every row.</p>
        </div>
    </div>
    <div class="pf-card-body" style="display:grid;gap:16px;">
        <div class="pf-grid-4">
            <div class="pf-field">
                <label class="pf-label" for="{{ $buyingId }}">Buying Price</label>
                <input id="{{ $buyingId }}" type="number" name="buying_price" class="pf-input" step="0.01" min="0" value="{{ old('buying_price', $p->buying_price ?? 0) }}">
            </div>
            <div class="pf-field">
                <label class="pf-label" for="product-price">Selling Price <span class="req">*</span></label>
                <input id="product-price" type="number" name="price" class="pf-input @error('price') is-invalid @enderror" step="0.01" min="0" value="{{ old('price', $p->price ?? 0) }}" required>
                @error('price')<span class="pf-error">{{ $message }}</span>@enderror
            </div>
            <div class="pf-field">
                <label class="pf-label" for="product-wholesale">Wholesale Price</label>
                <input id="product-wholesale" type="number" name="wholesale_price" class="pf-input" step="0.01" min="0" value="{{ old('wholesale_price', $p->wholesale_price ?? '') }}">
                <span class="pf-help">Used when checkout is set to Wholesale.</span>
            </div>
            <div class="pf-field">
                <label class="pf-label" for="product-tax">Tax Rate %</label>
                <input id="product-tax" type="number" name="tax_rate" class="pf-input" step="0.01" min="0" max="100" value="{{ old('tax_rate', $p->tax_rate ?? 16) }}">
                <span class="pf-help">Shared for all variants. Leave 0 for shop default.</span>
            </div>
        </div>
        <div class="pf-grid-2">
            <div class="pf-field">
                <label class="pf-label" for="product-discount-percent">Discount (%)</label>
                <div class="pf-input-wrap">
                    <input id="product-discount-percent" type="number" name="discount_percent" class="pf-input" step="1" min="1" max="99" value="{{ old('discount_percent', ($p && $p->hasSale()) ? $p->discountPercent() : '') }}" placeholder="e.g. 10">
                    <span class="pf-input-suffix">%</span>
                </div>
            </div>
            <div class="pf-field">
                <label class="pf-label" for="product-sale-price">Sale Price (KES)</label>
                <input id="product-sale-price" type="number" name="sale_price" class="pf-input @error('sale_price') is-invalid @enderror" step="0.01" min="0" value="{{ old('sale_price', $p->sale_price ?? '') }}" placeholder="0.00" @if(! $p) readonly @endif>
                @error('sale_price')<span class="pf-error">{{ $message }}</span>@enderror
            </div>
        </div>
        @unless($p)
            <div class="pf-sale-notice" id="pf-sale-notice">
                <span>Sale Price is calculated automatically: <strong>Selling Price − Discount</strong></span>
                <span>You save: <strong id="pf-you-save">KES 0.00</strong></span>
            </div>
        @endunless
    </div>
</div>
