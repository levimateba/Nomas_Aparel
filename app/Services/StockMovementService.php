<?php

namespace App\Services;

use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\StockLocation;
use App\Models\StockMovement;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\ValidationException;

/**
 * Backward-compatible facade. New location-aware ops go through InventoryStockService.
 */
class StockMovementService
{
    public function __construct(private readonly InventoryStockService $inventory)
    {
    }

    public function move(
        Product $product,
        string $type,
        int $quantity,
        ?string $reason = null,
        ?Model $reference = null,
        ?ProductVariant $variant = null,
        StockLocation|int|null $location = null,
        bool $allowNegative = false
    ): ?StockMovement {
        if ($quantity === 0) {
            return null;
        }

        if ($this->inventory->enabled()) {
            $location ??= StockLocation::mainStore() ?: $this->inventory->resolvePosLocation();
            if (! $location) {
                throw ValidationException::withMessages(['stock' => 'No stock location configured.']);
            }

            return $this->inventory->adjust(
                $product,
                $variant,
                $location,
                $quantity,
                strtoupper($type),
                $reason,
                $reference,
                null,
                $allowNegative
            );
        }

        return $this->moveLegacy($product, $type, $quantity, $reason, $reference, $variant);
    }

    public function moveLegacy(
        Product $product,
        string $type,
        int $quantity,
        ?string $reason = null,
        ?Model $reference = null,
        ?ProductVariant $variant = null
    ): ?StockMovement {
        if ($quantity === 0) {
            return null;
        }

        return DB::transaction(function () use ($product, $type, $quantity, $reason, $reference, $variant) {
            if ($variant) {
                $variant = ProductVariant::query()->lockForUpdate()->findOrFail($variant->id);
                $before = (int) $variant->stock;
                $after = $before + $quantity;
                if ($after < 0) {
                    throw ValidationException::withMessages([
                        'stock' => "Insufficient stock for {$product->name} — {$variant->name}.",
                    ]);
                }
                $variant->forceFill(['stock' => $after])->save();
                app(ProductVariantService::class)->deductStockLegacySync($product);
            } else {
                $product = Product::query()->lockForUpdate()->findOrFail($product->id);
                $before = (int) $product->stock;
                $after = $before + $quantity;
                if ($after < 0) {
                    throw ValidationException::withMessages([
                        'stock' => "Insufficient stock for {$product->name}.",
                    ]);
                }
                $product->forceFill(['stock' => $after])->save();
            }

            if (! Schema::hasTable('stock_movements')) {
                return null;
            }

            $payload = [
                'product_id' => $product->id,
                'user_id' => auth()->id(),
                'type' => $type,
                'quantity' => $quantity,
                'stock_before' => $before,
                'stock_after' => $after,
                'reason' => $reason,
                'reference_type' => $reference ? $reference::class : null,
                'reference_id' => $reference?->getKey(),
            ];
            if (Schema::hasColumn('stock_movements', 'product_variant_id')) {
                $payload['product_variant_id'] = $variant?->id;
            }

            return StockMovement::query()->create($payload);
        });
    }
}
