@php
    $stockLocations = $stockLocations ?? \App\Models\StockLocation::orderedActive();
    $locationStockOld = old('location_stock', []);
    $isEdit = ! empty($product?->id);
    $inventory = app(\App\Services\InventoryStockService::class);
    $mainStore = \App\Models\StockLocation::mainStore();
    $shopFloor = \App\Models\StockLocation::shopFloor();
    $mainQty = 0;
    $shopQty = 0;
    $rows = [];
    foreach ($stockLocations as $loc) {
        $current = $isEdit && $inventory->enabled() && ! ($product->usesVariants() ?? false)
            ? $inventory->quantityAt($product, null, $loc)
            : 0;
        $val = $locationStockOld[$loc->id] ?? ($isEdit ? $current : 0);
        $reorder = old('location_reorder.'.$loc->id.'.reorder_level');
        if ($reorder === null && $isEdit && $inventory->enabled()) {
            $bal = $inventory->balance($product, null, $loc);
            $reorder = $bal->reorder_level;
        }
        $reorder = (int) ($reorder ?? 0);
        $rows[] = compact('loc', 'current', 'val', 'reorder');
        if ($mainStore && (int) $loc->id === (int) $mainStore->id) {
            $mainQty = (int) $val;
        }
        if ($shopFloor && (int) $loc->id === (int) $shopFloor->id) {
            $shopQty = (int) $val;
        }
    }
    $needsShopTransfer = $isEdit && $shopFloor && $mainStore && $shopQty <= 0 && $mainQty > 0 && ! ($product->usesVariants() ?? false);
@endphp
<div id="location-stock-simple" style="display:grid;gap:12px;">
    <div class="pf-help">Stock by location. Total is calculated automatically — do not enter a separate total. Prefer <strong>Transfer</strong> for Store → Shop moves (keeps an audit trail).</div>

    @if($needsShopTransfer)
        <div style="display:flex;flex-wrap:wrap;align-items:center;justify-content:space-between;gap:12px;padding:12px 14px;border-radius:12px;border:1px solid #fcd34d;background:#fffbeb;">
            <div style="min-width:0;">
                <div style="font-size:13px;font-weight:700;color:#92400e;">{{ $shopFloor->name }} is out of stock</div>
                <div style="font-size:12px;color:#a16207;margin-top:2px;">
                    {{ $mainStore->name }} has {{ number_format($mainQty) }} units. Transfer to {{ $shopFloor->name }} before selling on POS.
                </div>
            </div>
            <a
                class="pf-btn-cancel"
                style="padding:8px 14px;text-decoration:none;background:#a58112;color:#111;border-color:#a58112;font-weight:700;"
                href="{{ route('admin.stock-transfers.create', [
                    'from_location_id' => $mainStore->id,
                    'to_location_id' => $shopFloor->id,
                    'product_id' => $product->id,
                ]) }}"
            >Transfer to {{ $shopFloor->name }}</a>
        </div>
    @endif

    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th>Location</th>
                    <th style="text-align:right;">{{ $isEdit ? 'Current / Set Stock' : 'Opening Stock' }}</th>
                    <th style="text-align:right;">Reorder level</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
            @foreach($rows as $row)
                @php
                    $loc = $row['loc'];
                    $val = $row['val'];
                    $reorder = $row['reorder'];
                    $isShopEmpty = $shopFloor && (int) $loc->id === (int) $shopFloor->id && (int) $val <= 0;
                @endphp
                <tr style="{{ $isShopEmpty && $mainQty > 0 ? 'background:#fffbeb;' : '' }}">
                    <td>
                        <strong>{{ $loc->name }}</strong>
                        <div class="muted" style="font-size:11px;">{{ strtoupper($loc->type) }}</div>
                        @if($isShopEmpty && $mainQty > 0)
                            <div style="font-size:11px;color:#b45309;margin-top:2px;">Empty — transfer from store to sell on POS</div>
                        @endif
                    </td>
                    <td style="text-align:right;">
                        <input class="pf-input loc-stock-input" type="number" min="0" name="location_stock[{{ $loc->id }}]" value="{{ $val }}" data-loc="{{ $loc->id }}" style="width:100px;text-align:right;margin-left:auto;">
                    </td>
                    <td style="text-align:right;">
                        <input class="pf-input" type="number" min="0" name="location_reorder[{{ $loc->id }}][reorder_level]" value="{{ $reorder }}" style="width:100px;text-align:right;margin-left:auto;">
                    </td>
                    <td style="text-align:right;white-space:nowrap;">
                        @if($isEdit && $mainStore && $shopFloor && (int) $loc->id === (int) $shopFloor->id && $mainQty > 0)
                            <a href="{{ route('admin.stock-transfers.create', ['from_location_id' => $mainStore->id, 'to_location_id' => $shopFloor->id, 'product_id' => $product->id]) }}" style="font-size:12px;font-weight:700;color:#a58112;text-decoration:none;">Transfer →</a>
                        @endif
                    </td>
                </tr>
            @endforeach
            <tr>
                <td><strong>Total Stock</strong></td>
                <td style="text-align:right;"><strong id="location-stock-total">0</strong></td>
                <td></td>
                <td></td>
            </tr>
            </tbody>
        </table>
    </div>
    <input type="hidden" name="stock" id="product-stock" value="0">
</div>
<script>
function recalcLocationStockTotal() {
    let total = 0;
    document.querySelectorAll('.loc-stock-input').forEach(el => { total += Number(el.value || 0); });
    const out = document.getElementById('location-stock-total');
    if (out) out.textContent = total;
    const hidden = document.getElementById('product-stock');
    if (hidden) hidden.value = total;
}
document.querySelectorAll('.loc-stock-input').forEach(el => el.addEventListener('input', recalcLocationStockTotal));
document.addEventListener('DOMContentLoaded', recalcLocationStockTotal);
recalcLocationStockTotal();
</script>
