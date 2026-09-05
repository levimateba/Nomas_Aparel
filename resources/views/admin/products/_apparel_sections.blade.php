@php
    $p = $product ?? null;
    $stockLocations = $stockLocations ?? \App\Models\StockLocation::orderedActive();
    $inventoryService = app(\App\Services\InventoryStockService::class);
    $existingVariants = old('variants');
    if (! is_array($existingVariants)) {
        $existingVariants = ($p?->variants ?? collect())->map(function ($v) use ($stockLocations, $inventoryService, $p) {
            $locationStock = [];
            foreach ($stockLocations as $loc) {
                $locationStock[$loc->id] = $p && $inventoryService->enabled()
                    ? $inventoryService->quantityAt($p, $v, $loc)
                    : ((int) $v->stock);
            }
            return [
                'id' => $v->id,
                'name' => $v->name,
                'sku' => $v->sku,
                'barcode' => $v->barcode,
                'buying_price' => $v->buying_price,
                'price' => $v->price,
                'wholesale_price' => $v->wholesale_price,
                'sale_price' => $v->sale_price,
                'stock' => $v->stock,
                'location_stock' => $locationStock,
                'reorder_level' => $v->reorder_level,
                'is_active' => $v->is_active ? '1' : '0',
                'attribute_value_ids' => $v->attributeValues->pluck('id')->all(),
            ];
        })->values()->all();
    }
    $specRows = [];
    if (old('spec_names')) {
        foreach (old('spec_names', []) as $i => $n) {
            $specRows[] = ['name' => $n, 'value' => old('spec_values.'.$i)];
        }
    } else {
        $specRows = ($p?->specifications ?? collect())->map(fn ($s) => ['name' => $s->name, 'value' => $s->value])->all();
    }
    if (count($specRows) === 0) {
        $specRows = [['name' => '', 'value' => '']];
    }
    $variantAttrsForJs = collect($variantAttributes ?? [])->map(function ($a) {
        return [
            'id' => $a->id,
            'name' => $a->name,
            'values' => $a->values->map(function ($v) {
                return ['id' => $v->id, 'value' => $v->value, 'code' => $v->code];
            })->values(),
        ];
    })->values();
    $locationsForJs = $stockLocations->map(fn ($l) => ['id' => $l->id, 'name' => $l->name, 'code' => $l->code])->values();
    $collapseAdvanced = (bool) ($collapseAdvanced ?? false);
@endphp

<div class="pf-card" id="section-apparel">
    <div class="pf-card-head">
        <div class="pf-card-num">2</div>
        <div>
            <h3 class="pf-card-title">Apparel &amp; Style</h3>
            <p class="pf-card-sub">Audience and style for storefront filters.</p>
        </div>
    </div>
    <div class="pf-card-body" style="display:grid;gap:16px;">
        <div class="pf-grid-2">
            <div class="pf-field">
                <label class="pf-label" for="target_audience">Target Audience / Gender</label>
                <select id="target_audience" name="target_audience" class="pf-input">
                    <option value="">— Optional —</option>
                    @foreach(($audiences ?? []) as $key => $label)
                        <option value="{{ $key }}" @selected(old('target_audience', $p->target_audience ?? '') === $key)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div class="pf-field">
                <label class="pf-label" for="product_style_id">Product Type / Style</label>
                <select id="product_style_id" name="product_style_id" class="pf-input">
                    <option value="">— Optional —</option>
                    @foreach(($styles ?? []) as $style)
                        <option value="{{ $style->id }}" @selected((string)old('product_style_id', $p->product_style_id ?? '') === (string)$style->id)>{{ $style->name }}</option>
                    @endforeach
                </select>
            </div>
        </div>
    </div>
</div>

<div class="pf-card" id="variants-card">
    <div class="pf-card-head">
        <div class="pf-card-num">3</div>
        <div>
            <h3 class="pf-card-title">Variants</h3>
            <p class="pf-card-sub">Size, colour, and per-variant stock. Leave off for a single SKU.</p>
        </div>
    </div>
    <div class="pf-card-body" style="display:grid;gap:16px;">
        <div class="pf-field" style="max-width:280px;">
            <label class="pf-label" for="has_variants">Has Variants?</label>
            <select id="has_variants" name="has_variants" class="pf-input" onchange="toggleVariantBuilder()">
                <option value="0" @selected(! old('has_variants', $p->has_variants ?? false))>No — single product</option>
                <option value="1" @selected(old('has_variants', $p->has_variants ?? false))>Yes — size / colour / etc.</option>
            </select>
            <span class="pf-help">Each variant has its own SKU, barcode, price, and stock.</span>
        </div>

        <div id="variant-builder" style="display:none;display:grid;gap:14px;">
            <p class="pf-help" style="margin:0;">Select attribute values, then generate combinations. Edit the table before saving.</p>
            <div id="variant-attr-pickers" style="display:grid;gap:12px;">
                @foreach(($variantAttributes ?? []) as $attr)
                    <div class="pf-field" data-attr-id="{{ $attr->id }}" data-attr-name="{{ $attr->name }}">
                        <label class="pf-label">{{ $attr->name }}</label>
                        <div style="display:flex;flex-wrap:wrap;gap:8px;">
                            @foreach($attr->values as $val)
                                <label style="display:inline-flex;align-items:center;gap:6px;border:1px solid #e5e7eb;border-radius:8px;padding:6px 10px;font-size:12px;background:#fff;">
                                    <input type="checkbox" class="attr-value-check" value="{{ $val->id }}" data-label="{{ $val->value }}" data-code="{{ $val->code }}">
                                    @if($val->hex_color)<span style="width:12px;height:12px;border-radius:50%;background:{{ $val->hex_color }};border:1px solid #d1d5db;display:inline-block;"></span>@endif
                                    {{ $val->value }}
                                </label>
                            @endforeach
                        </div>
                    </div>
                @endforeach
            </div>
            <div style="display:flex;gap:8px;flex-wrap:wrap;">
                <button type="button" class="pf-btn-cancel" style="padding:8px 12px;" onclick="generateVariants()">Generate variants</button>
                <button type="button" class="pf-btn-cancel" style="padding:8px 12px;" onclick="applyPriceToAllVariants()">Apply base price to all</button>
            </div>
            <div style="overflow-x:auto;">
                <table class="admin-table" id="variants-table" style="width:100%;border-collapse:collapse;font-size:13px;">
                    <thead>
                        <tr id="variants-thead-row">
                            <th style="padding:8px;text-align:left;">Variant</th>
                            <th style="padding:8px;text-align:left;">SKU</th>
                            <th style="padding:8px;text-align:left;">Barcode</th>
                            <th style="padding:8px;text-align:right;">Buying</th>
                            <th style="padding:8px;text-align:right;">Selling</th>
                            <th style="padding:8px;text-align:right;">Wholesale</th>
                            @foreach($stockLocations as $loc)
                                <th style="padding:8px;text-align:right;">{{ $loc->name }}</th>
                            @endforeach
                            <th style="padding:8px;text-align:right;">Total</th>
                            <th style="padding:8px;text-align:right;">Reorder</th>
                            <th style="padding:8px;text-align:left;">Active</th>
                        </tr>
                    </thead>
                    <tbody id="variants-tbody"></tbody>
                </table>
            </div>
            <div id="variants-hidden-inputs"></div>
        </div>
    </div>
</div>

<details class="pf-card pf-details" id="section-specs" @if(! $collapseAdvanced) open @endif>
    <summary class="pf-card-head pf-summary">
        <div class="pf-card-num is-muted">+</div>
        <div>
            <h3 class="pf-card-title">Specifications <span class="pf-optional">optional</span></h3>
            <p class="pf-card-sub">Material, fit, and care instructions.</p>
        </div>
        <svg class="pf-chevron" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/></svg>
    </summary>
    <div class="pf-card-body" style="display:grid;gap:10px;">
        <div id="spec-rows" style="display:grid;gap:8px;">
            @foreach($specRows as $row)
                <div class="pf-grid-2" style="gap:8px;">
                    <input type="text" name="spec_names[]" class="pf-input" value="{{ $row['name'] ?? '' }}" placeholder="Specification (e.g. Material)">
                    <input type="text" name="spec_values[]" class="pf-input" value="{{ $row['value'] ?? '' }}" placeholder="Value (e.g. 100% Cotton)">
                </div>
            @endforeach
        </div>
        <button type="button" class="pf-btn-cancel" style="padding:8px 12px;width:fit-content;" onclick="addSpecRow()">+ Add Specification</button>
        <div class="pf-field">
            <label class="pf-label" for="care_instructions">Care Instructions</label>
            <textarea id="care_instructions" name="care_instructions" class="pf-input" style="min-height:90px;" placeholder="Machine wash cold. Do not bleach. Iron low.">{{ old('care_instructions', $p->care_instructions ?? '') }}</textarea>
        </div>
    </div>
</details>

<details class="pf-card pf-details" id="section-gallery" @if(! $collapseAdvanced) open @endif>
    <summary class="pf-card-head pf-summary">
        <div class="pf-card-num is-muted">+</div>
        <div>
            <h3 class="pf-card-title">Image Gallery <span class="pf-optional">optional</span></h3>
            <p class="pf-card-sub">Extra photos beyond the main product image.</p>
        </div>
        <svg class="pf-chevron" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/></svg>
    </summary>
    <div class="pf-card-body" style="display:grid;gap:12px;">
        <div class="pf-field">
            <label class="pf-label">Additional images</label>
            <input type="file" name="gallery_files[]" class="pf-input" accept="image/png,image/jpeg,image/webp" multiple>
            <span class="pf-help">JPG, PNG or WebP up to 2MB each. Main image is set in Product Details.</span>
        </div>
        @if($p && $p->images && $p->images->count())
            <div style="display:flex;flex-wrap:wrap;gap:8px;">
                @foreach($p->images as $img)
                    <div style="width:72px;height:72px;border:1px solid #e5e7eb;border-radius:8px;overflow:hidden;background:#f9fafb;">
                        <img src="{{ $img->image_url }}" alt="" style="width:100%;height:100%;object-fit:cover;">
                        <input type="hidden" name="gallery_urls[]" value="{{ $img->image_url }}">
                    </div>
                @endforeach
            </div>
        @endif
    </div>
</details>

<details class="pf-card pf-details" id="section-shipping" @if(! $collapseAdvanced) open @endif>
    <summary class="pf-card-head pf-summary">
        <div class="pf-card-num is-muted">+</div>
        <div>
            <h3 class="pf-card-title">Shipping <span class="pf-optional">optional</span></h3>
            <p class="pf-card-sub">Weight, dimensions, and shipping flags.</p>
        </div>
        <svg class="pf-chevron" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/></svg>
    </summary>
    <div class="pf-card-body" style="display:grid;gap:16px;">
        <div class="pf-grid-4">
            <div class="pf-field"><label class="pf-label">Weight (kg)</label><input type="number" step="0.001" min="0" name="weight" class="pf-input" value="{{ old('weight', $p->weight ?? '') }}"></div>
            <div class="pf-field"><label class="pf-label">Length</label><input type="number" step="0.01" min="0" name="length" class="pf-input" value="{{ old('length', $p->length ?? '') }}"></div>
            <div class="pf-field"><label class="pf-label">Width</label><input type="number" step="0.01" min="0" name="width" class="pf-input" value="{{ old('width', $p->width ?? '') }}"></div>
            <div class="pf-field"><label class="pf-label">Height</label><input type="number" step="0.01" min="0" name="height" class="pf-input" value="{{ old('height', $p->height ?? '') }}"></div>
        </div>
        <div class="pf-grid-3">
            <div class="pf-field"><label class="pf-label">Shipping class</label><input type="text" name="shipping_class" class="pf-input" value="{{ old('shipping_class', $p->shipping_class ?? '') }}"></div>
            <label class="pf-track" style="margin-top:22px;"><input type="hidden" name="requires_shipping" value="0"><input type="checkbox" name="requires_shipping" value="1" @checked(old('requires_shipping', $p->requires_shipping ?? true))> Requires shipping</label>
            <label class="pf-track" style="margin-top:22px;"><input type="hidden" name="free_shipping" value="0"><input type="checkbox" name="free_shipping" value="1" @checked(old('free_shipping', $p->free_shipping ?? false))> Free shipping eligible</label>
        </div>
    </div>
</details>

<details class="pf-card pf-details" id="section-seo" @if(! $collapseAdvanced) open @endif>
    <summary class="pf-card-head pf-summary">
        <div class="pf-card-num is-muted">+</div>
        <div>
            <h3 class="pf-card-title">Online Store &amp; SEO <span class="pf-optional">optional</span></h3>
            <p class="pf-card-sub">Visibility, badges, and search meta.</p>
        </div>
        <svg class="pf-chevron" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/></svg>
    </summary>
    <div class="pf-card-body" style="display:grid;gap:16px;">
        <div class="pf-grid-3">
            <div class="pf-field">
                <label class="pf-label">Store visibility</label>
                <select name="store_visibility" class="pf-input">
                    <option value="visible" @selected(old('store_visibility', $p->store_visibility ?? 'visible') === 'visible')>Visible</option>
                    <option value="hidden" @selected(old('store_visibility', $p->store_visibility ?? '') === 'hidden')>Hidden</option>
                </select>
            </div>
            <label class="pf-track" style="margin-top:28px;"><input type="hidden" name="is_featured" value="0"><input type="checkbox" name="is_featured" value="1" @checked(old('is_featured', $p->is_featured ?? false))> Featured</label>
            <label class="pf-track" style="margin-top:28px;"><input type="hidden" name="is_new_arrival" value="0"><input type="checkbox" name="is_new_arrival" value="1" @checked(old('is_new_arrival', $p->is_new_arrival ?? false))> New arrival</label>
            <label class="pf-track"><input type="hidden" name="is_best_seller" value="0"><input type="checkbox" name="is_best_seller" value="1" @checked(old('is_best_seller', $p->is_best_seller ?? false))> Best seller</label>
            <label class="pf-track"><input type="hidden" name="allow_online_purchase" value="0"><input type="checkbox" name="allow_online_purchase" value="1" @checked(old('allow_online_purchase', $p->allow_online_purchase ?? true))> Allow online purchase</label>
            <label class="pf-track"><input type="hidden" name="display_stock" value="0"><input type="checkbox" name="display_stock" value="1" @checked(old('display_stock', $p->display_stock ?? true))> Display stock</label>
            <label class="pf-track"><input type="hidden" name="allow_backorders" value="0"><input type="checkbox" name="allow_backorders" value="1" @checked(old('allow_backorders', $p->allow_backorders ?? false))> Allow backorders</label>
            <label class="pf-track"><input type="hidden" name="seo_noindex" value="0"><input type="checkbox" name="seo_noindex" value="1" @checked(old('seo_noindex', $p->seo_noindex ?? false))> No index (SEO)</label>
        </div>
        <div class="pf-field"><label class="pf-label">Meta title</label><input type="text" name="meta_title" class="pf-input" value="{{ old('meta_title', $p->meta_title ?? '') }}"></div>
        <div class="pf-field"><label class="pf-label">Meta description</label><textarea name="meta_description" class="pf-input" style="min-height:70px;">{{ old('meta_description', $p->meta_description ?? '') }}</textarea></div>
        <div class="pf-field"><label class="pf-label">SEO keywords</label><input type="text" name="meta_keywords" class="pf-input" value="{{ old('meta_keywords', $p->meta_keywords ?? '') }}"></div>
    </div>
</details>

<script>
window.__existingVariants = @json($existingVariants);
window.__variantAttrs = @json($variantAttrsForJs);
window.__stockLocations = @json($locationsForJs);

function toggleVariantBuilder() {
    const on = document.getElementById('has_variants')?.value === '1';
    const box = document.getElementById('variant-builder');
    if (box) box.style.display = on ? 'grid' : 'none';
    const simple = document.getElementById('location-stock-simple');
    if (simple) simple.style.display = on ? 'none' : 'grid';
}
function cartesian(groups) {
    return groups.reduce((acc, curr) => {
        if (!acc.length) return curr.map(v => [v]);
        const out = [];
        acc.forEach(a => curr.forEach(c => out.push([...a, c])));
        return out;
    }, []);
}
function skuCodeFromLabel(label) {
    if (/^\d+$/.test(label)) return label;
    return (label || 'X').replace(/[^A-Za-z0-9]/g, '').slice(0, 3).toUpperCase() || 'X';
}
function emptyLocationStock() {
    const map = {};
    (window.__stockLocations || []).forEach(l => { map[l.id] = 0; });
    return map;
}
function generateVariants() {
    const groups = [];
    document.querySelectorAll('#variant-attr-pickers .pf-field').forEach(field => {
        const selected = [];
        field.querySelectorAll('.attr-value-check:checked').forEach(cb => {
            selected.push({ id: parseInt(cb.value, 10), label: cb.dataset.label, code: cb.dataset.code || skuCodeFromLabel(cb.dataset.label) });
        });
        if (selected.length) groups.push(selected);
    });
    if (!groups.length) {
        alert('Select at least one attribute value (e.g. Size and Colour).');
        return;
    }
    const combos = cartesian(groups);
    const baseSku = (document.getElementById('product-sku')?.value || 'SKU').replace(/[^A-Za-z0-9]+/g, '').toUpperCase() || 'SKU';
    const buy = document.getElementById('pf-cost')?.value || document.getElementById('product-buying-price')?.value || 0;
    const price = document.getElementById('product-price')?.value || 0;
    const wholesale = document.getElementById('product-wholesale')?.value || '';
    const rows = combos.map(combo => ({
        id: '',
        name: combo.map(c => c.label).join(' / '),
        sku: baseSku + '-' + combo.map(c => c.code || skuCodeFromLabel(c.label)).join('-'),
        barcode: '',
        buying_price: buy,
        price: price,
        wholesale_price: wholesale,
        sale_price: '',
        stock: 0,
        location_stock: emptyLocationStock(),
        reorder_level: 0,
        is_active: '1',
        attribute_value_ids: combo.map(c => c.id),
    }));
    renderVariantRows(rows);
}
function applyPriceToAllVariants() {
    const price = document.getElementById('product-price')?.value || 0;
    const buy = document.getElementById('pf-cost')?.value || document.getElementById('product-buying-price')?.value || 0;
    const wholesale = document.getElementById('product-wholesale')?.value || '';
    document.querySelectorAll('#variants-tbody tr').forEach(tr => {
        tr.querySelector('[data-f=price]') && (tr.querySelector('[data-f=price]').value = price);
        tr.querySelector('[data-f=buying_price]') && (tr.querySelector('[data-f=buying_price]').value = buy);
        tr.querySelector('[data-f=wholesale_price]') && (tr.querySelector('[data-f=wholesale_price]').value = wholesale);
    });
    syncVariantHiddenInputs();
}
function variantLocationTotal(tr) {
    let total = 0;
    tr.querySelectorAll('[data-loc-stock]').forEach(el => { total += Number(el.value || 0); });
    const out = tr.querySelector('[data-f=stock_total]');
    if (out) out.textContent = total;
    const hidden = tr.querySelector('[data-f=stock]');
    if (hidden) hidden.value = total;
}
function renderVariantRows(rows) {
    const tbody = document.getElementById('variants-tbody');
    if (!tbody) return;
    tbody.innerHTML = '';
    const locs = window.__stockLocations || [];
    rows.forEach((row) => {
        const locStock = row.location_stock || emptyLocationStock();
        const locInputs = locs.map(l => {
            const val = locStock[l.id] ?? locStock[String(l.id)] ?? 0;
            return `<td style="padding:6px;"><input class="pf-input" data-loc-stock="${l.id}" type="number" min="0" value="${val}" style="width:70px;text-align:right;"></td>`;
        }).join('');
        const tr = document.createElement('tr');
        tr.innerHTML = `
            <td style="padding:6px;"><strong>${row.name}</strong><input type="hidden" data-f="name" value="${row.name}"><input type="hidden" data-f="id" value="${row.id || ''}"><input type="hidden" data-f="attribute_value_ids" value='${JSON.stringify(row.attribute_value_ids || [])}'><input type="hidden" data-f="stock" value="${row.stock ?? 0}"></td>
            <td style="padding:6px;"><input class="pf-input" data-f="sku" value="${row.sku || ''}" style="min-width:120px;"></td>
            <td style="padding:6px;"><input class="pf-input" data-f="barcode" value="${row.barcode || ''}" style="min-width:110px;"></td>
            <td style="padding:6px;"><input class="pf-input" data-f="buying_price" type="number" step="0.01" min="0" value="${row.buying_price ?? ''}" style="width:90px;text-align:right;"></td>
            <td style="padding:6px;"><input class="pf-input" data-f="price" type="number" step="0.01" min="0" value="${row.price ?? 0}" style="width:90px;text-align:right;"></td>
            <td style="padding:6px;"><input class="pf-input" data-f="wholesale_price" type="number" step="0.01" min="0" value="${row.wholesale_price ?? ''}" style="width:90px;text-align:right;"></td>
            ${locInputs}
            <td style="padding:6px;text-align:right;font-weight:700;" data-f="stock_total">0</td>
            <td style="padding:6px;"><input class="pf-input" data-f="reorder_level" type="number" min="0" value="${row.reorder_level ?? 0}" style="width:70px;text-align:right;"></td>
            <td style="padding:6px;"><select class="pf-input" data-f="is_active"><option value="1" ${String(row.is_active) !== '0' ? 'selected' : ''}>Active</option><option value="0" ${String(row.is_active) === '0' ? 'selected' : ''}>Inactive</option></select></td>
        `;
        tbody.appendChild(tr);
        tr.querySelectorAll('input,select').forEach(el => {
            el.addEventListener('input', () => { variantLocationTotal(tr); syncVariantHiddenInputs(); });
            el.addEventListener('change', () => { variantLocationTotal(tr); syncVariantHiddenInputs(); });
        });
        variantLocationTotal(tr);
    });
    syncVariantHiddenInputs();
}
function syncVariantHiddenInputs() {
    const wrap = document.getElementById('variants-hidden-inputs');
    if (!wrap) return;
    wrap.innerHTML = '';
    document.querySelectorAll('#variants-tbody tr').forEach((tr, i) => {
        const get = (f) => tr.querySelector(`[data-f="${f}"]`)?.value ?? '';
        variantLocationTotal(tr);
        ['id','name','sku','barcode','buying_price','price','wholesale_price','sale_price','stock','reorder_level','is_active'].forEach(f => {
            const input = document.createElement('input');
            input.type = 'hidden';
            input.name = `variants[${i}][${f}]`;
            input.value = get(f);
            wrap.appendChild(input);
        });
        tr.querySelectorAll('[data-loc-stock]').forEach(el => {
            const input = document.createElement('input');
            input.type = 'hidden';
            input.name = `variants[${i}][location_stock][${el.dataset.locStock}]`;
            input.value = el.value || 0;
            wrap.appendChild(input);
        });
        let ids = [];
        try { ids = JSON.parse(get('attribute_value_ids') || '[]'); } catch (e) { ids = []; }
        ids.forEach((id, j) => {
            const input = document.createElement('input');
            input.type = 'hidden';
            input.name = `variants[${i}][attribute_value_ids][${j}]`;
            input.value = id;
            wrap.appendChild(input);
        });
    });
}
function addSpecRow() {
    const wrap = document.getElementById('spec-rows');
    if (!wrap) return;
    const row = document.createElement('div');
    row.className = 'pf-grid-2';
    row.style.gap = '8px';
    row.innerHTML = '<input type="text" name="spec_names[]" class="pf-input" placeholder="Specification"><input type="text" name="spec_values[]" class="pf-input" placeholder="Value">';
    wrap.appendChild(row);
}
document.addEventListener('DOMContentLoaded', function () {
    toggleVariantBuilder();
    if (Array.isArray(window.__existingVariants) && window.__existingVariants.length) {
        renderVariantRows(window.__existingVariants);
        if (document.getElementById('has_variants')) {
            document.getElementById('has_variants').value = '1';
            toggleVariantBuilder();
        }
    }
});
</script>
