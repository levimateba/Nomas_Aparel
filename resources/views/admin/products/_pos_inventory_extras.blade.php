@php
    $p = $product ?? null;
    $optionRows = old('option_names')
        ? collect(old('option_names', []))->map(fn ($n, $i) => ['name' => $n, 'value' => old('option_values.'.$i)])
        : collect($p?->product_options ?? []);
    if ($optionRows->isEmpty()) {
        $optionRows = collect([['name' => '', 'value' => '']]);
    }
    $extraBarcodes = old('additional_barcodes', $p?->additionalBarcodes?->pluck('barcode')->all() ?? ['']);
    if (! is_array($extraBarcodes) || count($extraBarcodes) === 0) {
        $extraBarcodes = [''];
    }
@endphp

<div class="pf-field">
    <label class="pf-label">Additional Barcodes (optional)</label>
    <div id="extra-barcodes" style="display:grid;gap:8px;">
        @foreach($extraBarcodes as $code)
            <input type="text" name="additional_barcodes[]" class="pf-input" value="{{ $code }}" placeholder="Click here, then scan or type barcode" maxlength="64">
        @endforeach
    </div>
    <button type="button" class="pf-btn-cancel" style="margin-top:8px;padding:8px 12px;" onclick="addExtraBarcode()">+ Add barcode</button>
    <span class="pf-help">Click a barcode box, then scan with your scanner (or type). Press Enter / scan again to add another.</span>
</div>

<div class="pf-grid-2">
    <div class="pf-field">
        <label class="pf-label" for="product-type">Product Type</label>
        <select id="product-type" name="product_type" class="pf-input">
            <option value="product" @selected(old('product_type', $p->product_type ?? 'product') === 'product')>Product</option>
            <option value="service" @selected(old('product_type', $p->product_type ?? 'product') === 'service')>Service</option>
        </select>
        <span class="pf-help">Services can be sold without affecting physical stock.</span>
    </div>
    <div class="pf-field">
        <label class="pf-label" for="product-keywords">Product Alias / Search Keywords</label>
        <input id="product-keywords" type="text" name="search_keywords" class="pf-input" value="{{ old('search_keywords', $p->search_keywords ?? '') }}" placeholder="e.g. Coke, Coca Cola, Coke 500, Soda">
        <span class="pf-help">Comma-separated alternate names cashiers may search for.</span>
    </div>
</div>

<div class="pf-field">
    <label class="pf-label">Product Options <span class="pf-help" style="font-weight:500;">(optional)</span></label>
    <span class="pf-help" style="margin-bottom:8px;">Use for size, colour, storage, etc. Does not change stock, SKU, or pricing.</span>
    <div id="product-options" style="display:grid;gap:8px;">
        @foreach($optionRows as $row)
            <div class="pf-grid-2" style="gap:8px;">
                <input type="text" name="option_names[]" class="pf-input" value="{{ is_array($row) ? ($row['name'] ?? '') : '' }}" placeholder="Option name (e.g. Size)">
                <input type="text" name="option_values[]" class="pf-input" value="{{ is_array($row) ? ($row['value'] ?? '') : '' }}" placeholder="Value (e.g. 38)">
            </div>
        @endforeach
    </div>
    <button type="button" class="pf-btn-cancel" style="margin-top:8px;padding:8px 12px;" onclick="addProductOption()">+ Add option</button>
</div>

<div class="pf-grid-3">
    <div class="pf-field">
        <label class="pf-label" for="pf-unit">Selling unit</label>
        <select id="pf-unit" name="unit" class="pf-input">
            @foreach(['pcs' => 'Piece (pcs)', 'pair' => 'Pair', 'set' => 'Set', 'kg' => 'KG', 'litre' => 'Litre', 'pack' => 'Pack'] as $val => $label)
                <option value="{{ $val }}" @selected(old('unit', $p->unit ?? 'pcs') === $val)>{{ $label }}</option>
            @endforeach
        </select>
    </div>
    <div class="pf-field">
        <label class="pf-label" for="pf-purchase-unit">Purchase unit</label>
        <select id="pf-purchase-unit" name="purchase_unit" class="pf-input">
            @foreach(['pcs' => 'Piece (pcs)', 'pair' => 'Pair', 'set' => 'Set', 'kg' => 'KG', 'litre' => 'Litre', 'carton' => 'Carton', 'pack' => 'Pack'] as $val => $label)
                <option value="{{ $val }}" @selected(old('purchase_unit', $p->purchase_unit ?? ($p->unit ?? 'pcs')) === $val)>{{ $label }}</option>
            @endforeach
        </select>
    </div>
    <div class="pf-field">
        <label class="pf-label" for="pf-purchase-unit-qty">Units per purchase pack</label>
        <input id="pf-purchase-unit-qty" type="number" name="purchase_unit_qty" class="pf-input" min="1" value="{{ old('purchase_unit_qty', $p->purchase_unit_qty ?? 1) }}">
        <span class="pf-help">How many selling units in one purchase unit (e.g. 12 pcs per carton).</span>
    </div>
</div>

<div class="pf-grid-3">
    <div class="pf-field">
        <label class="pf-label" for="pf-reorder">Reorder level</label>
        <input id="pf-reorder" type="number" name="reorder_level" class="pf-input" min="0" value="{{ old('reorder_level', $p->reorder_level ?? 0) }}">
    </div>
    <div class="pf-field">
        <label class="pf-label" for="pf-reorder-qty">Reorder quantity</label>
        <input id="pf-reorder-qty" type="number" name="reorder_quantity" class="pf-input" min="0" value="{{ old('reorder_quantity', $p->reorder_quantity ?? 0) }}">
        <span class="pf-help">Suggested qty when creating POs from low stock.</span>
    </div>
    <div class="pf-field">
        <label class="pf-label" for="pf-shelf">Shelf / Location</label>
        <input id="pf-shelf" type="text" name="shelf_location" class="pf-input" value="{{ old('shelf_location', $p->shelf_location ?? '') }}" placeholder="e.g. Shelf A3">
    </div>
</div>

@php
    $tracksExpiry = (bool) old('tracks_expiry', $p->tracks_expiry ?? false);
    $expiryValue = old('expiry_date', optional($p?->expiry_date)->format('Y-m-d'));
@endphp
<div class="pf-grid-2">
    <div class="pf-field">
        <label class="pf-label" for="pf-batch">Batch / Lot Number</label>
        <input id="pf-batch" type="text" name="batch_lot" class="pf-input" value="{{ old('batch_lot', $p->batch_lot ?? '') }}" placeholder="e.g. LOT-2026-0815">
    </div>
    <div class="pf-field">
        <label class="pf-track" style="margin-top:0;margin-bottom:0;">
            <input type="hidden" name="tracks_expiry" value="0">
            <input type="checkbox" id="pf-tracks-expiry" name="tracks_expiry" value="1" @checked($tracksExpiry) onchange="toggleExpiryDate(this)">
            <span>This product has expiry dates</span>
        </label>
        <span class="pf-help" id="pf-expiry-hint" style="{{ $tracksExpiry ? 'display:none;' : '' }}">Leave unchecked for apparel and non-perishable items.</span>

        <div id="pf-expiry-wrap" style="{{ $tracksExpiry ? '' : 'display:none;' }}margin-top:12px;">
            <label class="pf-label" for="pf-expiry">Expiry date <span class="req">*</span></label>
            <input
                id="pf-expiry"
                type="date"
                name="expiry_date"
                class="pf-input"
                value="{{ $expiryValue }}"
                @if($tracksExpiry) required @else disabled @endif
            >
            <span class="pf-help">Use the calendar icon or type the date.</span>
        </div>
    </div>
</div>

<script>
function toggleExpiryDate(checkbox) {
    const wrap = document.getElementById('pf-expiry-wrap');
    const input = document.getElementById('pf-expiry');
    const hint = document.getElementById('pf-expiry-hint');
    const on = !!(checkbox && checkbox.checked);
    if (wrap) wrap.style.display = on ? 'block' : 'none';
    if (hint) hint.style.display = on ? 'none' : '';
    if (input) {
        input.disabled = !on;
        input.required = on;
        if (!on) input.value = '';
        if (on) {
            // Focus so the calendar is ready to use
            setTimeout(() => input.focus(), 0);
        }
    }
}
function addExtraBarcode() {
    const wrap = document.getElementById('extra-barcodes');
    if (!wrap) return;
    const input = document.createElement('input');
    input.type = 'text';
    input.name = 'additional_barcodes[]';
    input.className = 'pf-input';
    input.placeholder = 'Click here, then scan or type barcode';
    input.maxLength = 64;
    wrap.appendChild(input);
    input.focus();
}
function addProductOption() {
    const wrap = document.getElementById('product-options');
    if (!wrap) return;
    const row = document.createElement('div');
    row.className = 'pf-grid-2';
    row.style.gap = '8px';
    row.innerHTML = '<input type="text" name="option_names[]" class="pf-input" placeholder="Option name (e.g. Size)"><input type="text" name="option_values[]" class="pf-input" placeholder="Value (e.g. 38)">';
    wrap.appendChild(row);
}
</script>
