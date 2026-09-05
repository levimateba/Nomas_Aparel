<?php

namespace App\Services;

use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\StockLocation;
use App\Models\VariantAttributeValue;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class ProductVariantService
{
    public function __construct(private readonly InventoryStockService $inventory)
    {
    }

    public function syncVariants(Product $product, array $variants, bool $hasVariants, array $locationIds = []): void
    {
        if (! Schema::hasTable('product_variants')) {
            return;
        }

        if (! $hasVariants) {
            $product->variants()->update(['is_active' => false]);
            $product->update(['has_variants' => false]);

            return;
        }

        $keptIds = [];
        foreach (array_values($variants) as $index => $row) {
            if (! is_array($row)) {
                continue;
            }

            $name = trim((string) ($row['name'] ?? ''));
            if ($name === '') {
                continue;
            }

            $valueIds = collect($row['attribute_value_ids'] ?? [])
                ->map(fn ($id) => (int) $id)
                ->filter()
                ->unique()
                ->values();

            $signature = $this->signatureFromValueIds($valueIds->all());
            $sku = trim((string) ($row['sku'] ?? '')) ?: null;
            $barcode = trim((string) ($row['barcode'] ?? '')) ?: null;

            $locationStocks = $this->extractLocationStocks($row, $locationIds);
            $stockTotal = ! empty($locationStocks)
                ? (int) array_sum($locationStocks)
                : max(0, (int) ($row['stock'] ?? 0));

            $payload = [
                'name' => $name,
                'sku' => $sku,
                'barcode' => $barcode,
                'buying_price' => $row['buying_price'] ?? $product->buying_price,
                'price' => $row['price'] ?? $product->price,
                'wholesale_price' => $row['wholesale_price'] ?? $product->wholesale_price,
                'sale_price' => $row['sale_price'] ?? null,
                'tax_rate' => $row['tax_rate'] ?? $product->tax_rate,
                'stock' => $stockTotal,
                'reorder_level' => max(0, (int) ($row['reorder_level'] ?? 0)),
                'reorder_quantity' => max(0, (int) ($row['reorder_quantity'] ?? 0)),
                'shelf_location' => $row['shelf_location'] ?? $product->shelf_location,
                'image_url' => $row['image_url'] ?? null,
                'option_signature' => $signature,
                'is_active' => ! empty($row['is_active']) || ($row['is_active'] ?? '1') === '1' || ($row['is_active'] ?? true) === true,
                'sort_order' => $index,
            ];

            if (array_key_exists('is_active', $row)) {
                $payload['is_active'] = filter_var($row['is_active'], FILTER_VALIDATE_BOOLEAN) || $row['is_active'] === '1' || $row['is_active'] === 1 || $row['is_active'] === true;
            }

            $variant = null;
            if (! empty($row['id'])) {
                $variant = $product->variants()->whereKey((int) $row['id'])->first();
            }
            if (! $variant && $signature) {
                $variant = $product->variants()->where('option_signature', $signature)->first();
            }

            if ($variant) {
                $variant->update($payload);
            } else {
                $variant = $product->variants()->create($payload);
            }

            $keptIds[] = $variant->id;
            $this->syncVariantAttributes($variant, $valueIds->all());

            if ($this->inventory->enabled()) {
                if (empty($locationStocks) && $stockTotal > 0) {
                    $main = StockLocation::mainStore();
                    if ($main) {
                        $locationStocks = [$main->id => $stockTotal];
                        foreach (StockLocation::orderedActive() as $loc) {
                            if ((int) $loc->id !== (int) $main->id) {
                                $locationStocks[$loc->id] = $locationStocks[$loc->id] ?? 0;
                            }
                        }
                    }
                } elseif (empty($locationStocks)) {
                    foreach (StockLocation::orderedActive() as $loc) {
                        $locationStocks[$loc->id] = 0;
                    }
                }

                $this->inventory->syncLocationQuantities(
                    $product,
                    $variant,
                    $locationStocks,
                    empty($row['id']) ? 'OPENING_STOCK' : 'ADJUSTMENT',
                    empty($row['id']) ? 'Opening stock' : 'Variant stock update',
                    empty($row['id'])
                );

                if (! empty($row['location_reorder']) && is_array($row['location_reorder'])) {
                    $this->inventory->syncLocationReorderLevels($product, $variant, $row['location_reorder']);
                }
            }
        }

        if (! empty($keptIds)) {
            $product->variants()->whereNotIn('id', $keptIds)->update(['is_active' => false]);
        }

        $product->update([
            'has_variants' => true,
            'stock' => (int) $product->variants()->where('is_active', true)->sum('stock'),
        ]);
    }

    public function syncVariantAttributes(ProductVariant $variant, array $valueIds): void
    {
        $variant->attributeValues()->detach();
        $values = VariantAttributeValue::query()->whereIn('id', $valueIds)->get();
        foreach ($values as $value) {
            $variant->attributeValues()->attach($value->id, [
                'variant_attribute_id' => $value->variant_attribute_id,
            ]);
        }
    }

    public function signatureFromValueIds(array $valueIds): string
    {
        $ids = collect($valueIds)->map(fn ($id) => (int) $id)->filter()->unique()->sort()->values();

        return $ids->implode('-');
    }

    public function generateVariantSku(string $baseSku, string $variantName): string
    {
        $base = strtoupper(preg_replace('/[^A-Za-z0-9]+/', '', $baseSku) ?: 'SKU');
        $parts = preg_split('/\s*\/\s*/', $variantName) ?: [];
        $codes = collect($parts)->map(function ($part) {
            $part = trim((string) $part);
            if ($part === '') {
                return '';
            }
            if (preg_match('/^\d+$/', $part)) {
                return $part;
            }

            return strtoupper(Str::substr(preg_replace('/[^A-Za-z0-9]+/', '', $part) ?: 'X', 0, 3));
        })->filter()->implode('-');

        return $codes !== '' ? $base.'-'.$codes : $base.'-'.strtoupper(Str::random(4));
    }

    public function deductStock(
        Product $product,
        int $qty,
        ?ProductVariant $variant = null,
        StockLocation|int|null $location = null,
        string $type = 'SALE',
        ?string $reason = null,
        $reference = null,
        bool $allowNegative = false
    ): void {
        if ($this->inventory->enabled()) {
            $location ??= $this->inventory->resolvePosLocation();
            if (! $location) {
                throw new \RuntimeException('No stock location configured for deduction.');
            }
            $this->inventory->adjust(
                $product,
                $variant,
                $location,
                -abs($qty),
                $type,
                $reason,
                $reference,
                null,
                $allowNegative
            );

            return;
        }

        if ($variant) {
            $variant->decrement('stock', $qty);
            $this->deductStockLegacySync($product);

            return;
        }

        $product->decrement('stock', $qty);
    }

    public function availableStock(
        Product $product,
        ?ProductVariant $variant = null,
        StockLocation|int|null $location = null
    ): int {
        if ($this->inventory->enabled()) {
            if ($location) {
                return $this->inventory->quantityAt($product, $variant, $location);
            }

            return $this->inventory->totalQuantity($product, $variant);
        }

        if ($variant) {
            return max(0, (int) $variant->stock);
        }

        return max(0, (int) $product->stock);
    }

    public function deductStockLegacySync(Product $product): void
    {
        if (Schema::hasColumn('products', 'has_variants') && $product->has_variants) {
            $product->update([
                'stock' => (int) $product->variants()->where('is_active', true)->sum('stock'),
            ]);
        }
    }

    private function extractLocationStocks(array $row, array $locationIds = []): array
    {
        $stocks = [];
        $raw = $row['location_stock'] ?? $row['location_stocks'] ?? null;
        if (is_array($raw)) {
            foreach ($raw as $id => $qty) {
                $stocks[(int) $id] = max(0, (int) $qty);
            }
        }

        if (empty($stocks) && ! empty($locationIds)) {
            // leave empty — caller decides fallback
        }

        return $stocks;
    }
}
