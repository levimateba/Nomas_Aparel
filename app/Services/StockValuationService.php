<?php

namespace App\Services;

use App\Models\Product;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class StockValuationService
{
    /**
     * @param  array{q?:string,category?:string|int,brand?:string|int,stock?:string}  $filters
     */
    public function inventoryQuery(array $filters = []): Builder
    {
        $query = Product::query();
        if (Schema::hasColumn('products', 'product_type')) {
            $query->where(function ($q) {
                $q->where('product_type', 'product')->orWhereNull('product_type');
            });
        }

        $q = trim((string) ($filters['q'] ?? ''));
        if ($q !== '') {
            $query->where(function ($builder) use ($q) {
                $builder->where('name', 'like', "%{$q}%")
                    ->orWhere('sku', 'like', "%{$q}%");
                if (Schema::hasColumn('products', 'barcode')) {
                    $builder->orWhere('barcode', 'like', "%{$q}%");
                }
            });
        }

        $categoryId = $filters['category'] ?? null;
        if ($categoryId !== null && $categoryId !== '' && Schema::hasColumn('products', 'category_id')) {
            $query->where('category_id', (int) $categoryId);
        }

        $brandId = $filters['brand'] ?? null;
        if ($brandId !== null && $brandId !== '' && Schema::hasColumn('products', 'brand_id')) {
            $query->where('brand_id', (int) $brandId);
        }

        $stock = (string) ($filters['stock'] ?? '');
        if ($stock === 'out') {
            $query->where('stock', '<=', 0);
        } elseif ($stock === 'low') {
            $query->where('stock', '>', 0);
            if (Schema::hasColumn('products', 'reorder_level')) {
                $query->where(function ($builder) {
                    $builder->where(function ($inner) {
                        $inner->where('reorder_level', '>', 0)
                            ->whereColumn('stock', '<=', 'reorder_level');
                    })->orWhere(function ($inner) {
                        $inner->where(function ($r) {
                            $r->whereNull('reorder_level')->orWhere('reorder_level', '<=', 0);
                        })->where('stock', '<=', 5);
                    });
                });
            } else {
                $query->where('stock', '<=', 5);
            }
        } elseif ($stock === 'in') {
            $query->where('stock', '>', 0);
            if (Schema::hasColumn('products', 'reorder_level')) {
                $query->where(function ($builder) {
                    $builder->where(function ($inner) {
                        $inner->where('reorder_level', '>', 0)
                            ->whereColumn('stock', '>', 'reorder_level');
                    })->orWhere(function ($inner) {
                        $inner->where(function ($r) {
                            $r->whereNull('reorder_level')->orWhere('reorder_level', '<=', 0);
                        })->where('stock', '>', 5);
                    });
                });
            } else {
                $query->where('stock', '>', 5);
            }
        }

        return $query;
    }

    public function totalStockCost(array $filters = []): float
    {
        $buying = Schema::hasColumn('products', 'buying_price') ? 'COALESCE(buying_price, 0)' : '0';

        return (float) $this->inventoryQuery($filters)->sum(DB::raw("stock * {$buying}"));
    }

    public function expectedGrossSales(array $filters = []): float
    {
        return (float) $this->inventoryQuery($filters)->sum(DB::raw('stock * COALESCE(price, 0)'));
    }

    public function expectedGrossProfit(array $filters = []): float
    {
        return $this->expectedGrossSales($filters) - $this->totalStockCost($filters);
    }

    public function expectedProfitMargin(array $filters = []): float
    {
        $sales = $this->expectedGrossSales($filters);
        if ($sales <= 0) {
            return 0.0;
        }

        return round(($this->expectedGrossProfit($filters) / $sales) * 100, 2);
    }

    public function summary(array $filters = []): array
    {
        $totalStockCost = $this->totalStockCost($filters);
        $expectedGrossSales = $this->expectedGrossSales($filters);
        $expectedGrossProfit = $expectedGrossSales - $totalStockCost;

        return [
            'total_stock_cost' => $totalStockCost,
            'total_stock_value' => $totalStockCost,
            'expected_gross_sales' => $expectedGrossSales,
            'expected_gross_profit' => $expectedGrossProfit,
            'expected_profit_margin' => $this->expectedProfitMargin($filters),
        ];
    }

    public function lineMetrics(Product $product): array
    {
        $qty = (float) $product->stock;
        $buying = (float) ($product->buying_price ?? 0);
        $selling = (float) ($product->price ?? 0);
        $stockCost = $qty * $buying;
        $expectedSales = $qty * $selling;

        return [
            'stock_cost' => $stockCost,
            'expected_sales' => $expectedSales,
            'expected_profit' => $expectedSales - $stockCost,
        ];
    }

    public function stockStatus(Product $product): string
    {
        $qty = (int) $product->stock;
        $reorder = (int) ($product->reorder_level ?? 0);
        if ($qty <= 0) {
            return 'Out of stock';
        }
        if ($reorder > 0 && $qty <= $reorder) {
            return 'Low stock';
        }
        if ($reorder === 0 && $qty <= 5) {
            return 'Low stock';
        }

        return 'In stock';
    }

    public function tableTotals(array $filters = []): array
    {
        $buying = Schema::hasColumn('products', 'buying_price') ? 'COALESCE(buying_price, 0)' : '0';
        $stockCost = (float) $this->inventoryQuery($filters)->sum(DB::raw("stock * {$buying}"));
        $expectedSales = (float) $this->inventoryQuery($filters)->sum(DB::raw('stock * COALESCE(price, 0)'));

        return [
            'stock_cost' => $stockCost,
            'expected_sales' => $expectedSales,
            'expected_profit' => $expectedSales - $stockCost,
        ];
    }
}
