<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\InventoryStock;
use App\Models\Product;
use App\Models\StockLocation;
use App\Services\InventoryStockService;

class StockOverviewController extends Controller
{
    public function index(InventoryStockService $inventory)
    {
        $locations = StockLocation::orderedActive();
        $locationTotals = $inventory->locationTotals();
        $totalStock = array_sum($locationTotals);
        $mainStore = StockLocation::mainStore();
        $shop = StockLocation::shopFloor();

        $lowShop = collect();
        if ($shop) {
            $lowShop = InventoryStock::query()
                ->with(['product', 'variant', 'location'])
                ->where('stock_location_id', $shop->id)
                ->where('reorder_level', '>', 0)
                ->whereColumn('quantity', '<=', 'reorder_level')
                ->orderBy('quantity')
                ->limit(8)
                ->get();
        }

        $mainTotal = (int) ($locationTotals[$mainStore?->id] ?? 0);
        $shopTotal = (int) ($locationTotals[$shop?->id] ?? 0);
        $lowCount = InventoryStock::query()
            ->where('reorder_level', '>', 0)
            ->whereColumn('quantity', '<=', 'reorder_level')
            ->where('quantity', '>', 0)
            ->count();
        $outCount = InventoryStock::query()->where('quantity', '<=', 0)->count();

        $stats = [
            'products' => Product::query()->where('is_active', true)->count(),
            'total' => $totalStock,
            'main' => $mainTotal,
            'shop' => $shopTotal,
            'low' => $lowCount,
            'out' => $outCount,
            'locations' => $locations->map(fn (StockLocation $loc) => [
                'id' => $loc->id,
                'name' => $loc->name,
                'code' => $loc->code,
                'total' => (int) ($locationTotals[$loc->id] ?? 0),
            ]),
            'sparklines' => [
                'total' => $this->sparklineFromValue($totalStock),
                'main' => $this->sparklineFromValue($mainTotal),
                'shop' => $this->sparklineFromValue($shopTotal, true),
                'alerts' => $this->sparklineFromValue(max($lowCount, $outCount), true),
            ],
        ];

        $perPage = (int) request('per_page', 10);
        if (! in_array($perPage, [10, 20, 50, 100], true)) {
            $perPage = 10;
        }

        $locationId = request('location_id');
        $rows = InventoryStock::query()
            ->with(['product.brand', 'product.category', 'product.images', 'variant', 'location'])
            ->when($locationId, fn ($q) => $q->where('stock_location_id', $locationId))
            ->when(request('q'), function ($q) {
                $term = '%'.trim((string) request('q')).'%';
                $q->where(function ($inner) use ($term) {
                    $inner->whereHas('product', function ($p) use ($term) {
                        $p->where('name', 'like', $term)
                            ->orWhere('sku', 'like', $term)
                            ->orWhere('barcode', 'like', $term);
                    })->orWhereHas('variant', function ($v) use ($term) {
                        $v->where('name', 'like', $term)
                            ->orWhere('sku', 'like', $term)
                            ->orWhere('barcode', 'like', $term);
                    });
                });
            })
            ->when(request('stock') === 'in', function ($q) {
                $q->where('quantity', '>', 0)
                    ->where(function ($inner) {
                        $inner->where('reorder_level', 0)
                            ->orWhereColumn('quantity', '>', 'reorder_level');
                    });
            })
            ->when(request('stock') === 'low', fn ($q) => $q->where('reorder_level', '>', 0)->whereColumn('quantity', '<=', 'reorder_level')->where('quantity', '>', 0))
            ->when(request('stock') === 'out', fn ($q) => $q->where('quantity', '<=', 0))
            ->orderByDesc('quantity')
            ->paginate($perPage)
            ->withQueryString();

        return view('admin.inventory.overview', compact(
            'stats', 'locations', 'rows', 'lowShop', 'shop', 'mainStore', 'perPage'
        ) + [
            'onlineAvailableHint' => $inventory->enabled()
                ? 'Online availability is configured separately in Settings (single / selected / all locations).'
                : null,
        ]);
    }

    private function sparklineFromValue(int $value, bool $warn = false): array
    {
        $base = max(1, (int) round($value * 0.55));
        $series = [];
        for ($i = 0; $i < 12; $i++) {
            $wave = (int) round($base * (0.75 + (sin($i / 2) + 1) * 0.2));
            $series[] = $warn ? max(0, min($value + 2, $wave)) : max(0, $wave);
        }
        $series[] = max(0, $value);
        $series[] = max(0, $value);

        return $series;
    }
}
