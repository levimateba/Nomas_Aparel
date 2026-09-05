<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\StockLocation;
use App\Services\InventoryStockService;
use App\Support\Audit;
use Illuminate\Http\Request;

class StockAdjustmentController extends Controller
{
    public function create()
    {
        return view('admin.inventory.adjustments.create', [
            'locations' => StockLocation::orderedActive(),
            'products' => Product::query()->where('is_active', true)->orderBy('name')->limit(500)->get(['id', 'name', 'sku', 'barcode', 'has_variants']),
            'selectedProductId' => request('product_id'),
            'selectedLocationId' => request('stock_location_id'),
            'selectedVariantId' => request('product_variant_id'),
            'reasons' => [
                'Damaged',
                'Missing item',
                'Stock count correction',
                'Found stock',
                'Opening stock correction',
                'Physical Stock Count',
                'Other',
            ],
        ]);
    }

    public function store(Request $request, InventoryStockService $inventory)
    {
        $data = $request->validate([
            'stock_location_id' => 'required|exists:stock_locations,id',
            'product_id' => 'required|exists:products,id',
            'product_variant_id' => 'nullable|exists:product_variants,id',
            'adjustment' => 'required|integer|not_in:0',
            'reason' => 'required|string|max:120',
            'notes' => 'nullable|string|max:2000',
            'mode' => 'nullable|in:delta,set',
        ]);

        $product = Product::query()->findOrFail($data['product_id']);
        $variant = null;
        if (! empty($data['product_variant_id'])) {
            $variant = ProductVariant::query()->findOrFail($data['product_variant_id']);
        } elseif ($product->usesVariants()) {
            return back()->withErrors(['product_variant_id' => 'Select a variant.'])->withInput();
        }

        $location = StockLocation::query()->findOrFail($data['stock_location_id']);
        $mode = $data['mode'] ?? 'delta';
        $type = str_contains(strtolower($data['reason']), 'damage') ? 'DAMAGE' : (str_contains(strtolower($data['reason']), 'count') ? 'STOCK_COUNT' : 'ADJUSTMENT');

        if ($mode === 'set') {
            $current = $inventory->quantityAt($product, $variant, $location);
            $delta = (int) $data['adjustment'] - $current;
            if ($delta === 0) {
                return back()->with('success', 'No change required.');
            }
            $inventory->adjust($product, $variant, $location, $delta, $type, $data['reason'], null, $data['notes'] ?? null, false);
            $change = $delta;
        } else {
            $inventory->adjust($product, $variant, $location, (int) $data['adjustment'], $type, $data['reason'], null, $data['notes'] ?? null, false);
            $change = (int) $data['adjustment'];
        }

        $label = $variant ? $variant->displayName() : $product->name;
        Audit::log(
            'stock_adjusted',
            'Stock adjustment for '.$label.' at '.$location->name.': '.($change > 0 ? '+' : '').$change.' ('.$data['reason'].')',
            $product,
            [
                'location' => $location->name,
                'variant_id' => $variant?->id,
                'change' => $change,
                'reason' => $data['reason'],
                'type' => $type,
            ],
            'inventory'
        );

        return redirect()->route('admin.stock-movements.index', [
            'product_id' => $product->id,
        ])->with('success', 'Stock adjustment recorded.');
    }
}
