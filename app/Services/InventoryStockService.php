<?php

namespace App\Services;

use App\Models\InventoryStock;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\Setting;
use App\Models\StockLocation;
use App\Models\StockMovement;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\ValidationException;

class InventoryStockService
{
    public function enabled(): bool
    {
        return Schema::hasTable('inventory_stocks') && Schema::hasTable('stock_locations');
    }

    public function variantKey(?ProductVariant $variant, ?int $variantId = null): int
    {
        if ($variant) {
            return (int) $variant->id;
        }

        return max(0, (int) ($variantId ?? 0));
    }

    public function resolvePosLocation(?Setting $settings = null): ?StockLocation
    {
        $settings ??= Setting::get_settings();
        if ($settings->pos_location_id) {
            return StockLocation::query()->whereKey($settings->pos_location_id)->where('is_active', true)->first()
                ?: StockLocation::shopFloor();
        }

        return StockLocation::shopFloor() ?: StockLocation::orderedActive()->first();
    }

    /**
     * Locations POS may sell from (primary POS location first when multi-location is on).
     */
    public function posEligibleLocations(?Setting $settings = null): Collection
    {
        $settings ??= Setting::get_settings();
        if (! $this->enabled()) {
            return collect();
        }

        $primary = $this->resolvePosLocation($settings);

        if (! ($settings->pos_sell_from_all_locations ?? false)) {
            return $primary ? collect([$primary]) : collect();
        }

        $all = StockLocation::orderedActive()->values();
        if (! $primary) {
            return $all;
        }

        return $all
            ->sortBy(fn (StockLocation $loc) => (int) $loc->id === (int) $primary->id ? 0 : 1)
            ->values();
    }

    public function posAvailableStock(Product $product, ?ProductVariant $variant = null, ?Setting $settings = null): int
    {
        if (! $this->enabled()) {
            return $variant ? max(0, (int) $variant->stock) : max(0, (int) $product->stock);
        }

        $locations = $this->posEligibleLocations($settings);
        if ($locations->isEmpty()) {
            return 0;
        }

        $total = 0;
        foreach ($locations as $location) {
            $total += $this->quantityAt($product, $variant, $location);
        }

        return max(0, $total);
    }

    /**
     * Deduct a POS sale across eligible locations (POS location first, then others).
     *
     * @return array<int, array{location_id:int, quantity:int}>
     */
    public function fulfilPosSale(
        Product $product,
        ?ProductVariant $variant,
        int $quantity,
        Model $order,
        ?Setting $settings = null,
        bool $allowNegative = false
    ): array {
        $quantity = abs($quantity);
        if ($quantity === 0) {
            return [];
        }

        $settings ??= Setting::get_settings();

        if (! $this->enabled()) {
            app(ProductVariantService::class)->deductStock(
                $product,
                $quantity,
                $variant,
                null,
                'SALE',
                'POS sale',
                $order,
                $allowNegative
            );

            return [];
        }

        $locations = $this->posEligibleLocations($settings);
        if ($locations->isEmpty()) {
            throw ValidationException::withMessages([
                'pos' => 'No POS inventory location is configured.',
            ]);
        }

        // Single-location path (existing behaviour)
        if (! ($settings->pos_sell_from_all_locations ?? false) || $locations->count() === 1) {
            $location = $locations->first();
            $available = $this->quantityAt($product, $variant, $location);
            if (! $allowNegative && $available < $quantity) {
                throw ValidationException::withMessages([
                    'pos' => 'Insufficient stock at '.$location->name.' for '.$product->name.'. Available: '.$available.'.',
                ]);
            }
            $this->adjust(
                $product,
                $variant,
                $location,
                -$quantity,
                'SALE',
                'POS sale '.($order->order_number ?? $order->getKey()),
                $order,
                null,
                $allowNegative
            );

            return [['location_id' => (int) $location->id, 'quantity' => $quantity]];
        }

        return DB::transaction(function () use ($product, $variant, $quantity, $order, $locations, $allowNegative, $settings) {
            $available = $this->posAvailableStock($product, $variant, $settings);
            if (! $allowNegative && $available < $quantity) {
                throw ValidationException::withMessages([
                    'pos' => 'Insufficient stock for '.$product->name.' across POS locations. Available: '.$available.'.',
                ]);
            }

            $remaining = $quantity;
            $allocations = [];

            foreach ($locations as $location) {
                if ($remaining <= 0) {
                    break;
                }
                $onHand = $this->quantityAt($product, $variant, $location);
                $take = min($onHand, $remaining);
                if ($take <= 0) {
                    continue;
                }

                $this->adjust(
                    $product,
                    $variant,
                    $location,
                    -$take,
                    'SALE',
                    'POS sale '.($order->order_number ?? $order->getKey()),
                    $order,
                    null,
                    false
                );

                $allocations[] = [
                    'location_id' => (int) $location->id,
                    'quantity' => $take,
                ];
                $remaining -= $take;
            }

            if ($remaining > 0) {
                if (! $allowNegative) {
                    throw ValidationException::withMessages([
                        'pos' => 'Unable to allocate POS stock across locations for '.$product->name.'.',
                    ]);
                }
                $fallback = $locations->first();
                $this->adjust(
                    $product,
                    $variant,
                    $fallback,
                    -$remaining,
                    'SALE',
                    'POS sale '.($order->order_number ?? $order->getKey()).' (backorder)',
                    $order,
                    null,
                    true
                );
                $allocations[] = [
                    'location_id' => (int) $fallback->id,
                    'quantity' => $remaining,
                ];
            }

            return $allocations;
        });
    }

    /**
     * Active stock locations eligible for online availability / fulfilment, ordered by priority.
     */
    public function onlineEligibleLocations(?Setting $settings = null): Collection
    {
        $settings ??= Setting::get_settings();
        $mode = $settings->online_sales_stock_mode ?? 'single';

        if (! $this->enabled()) {
            return collect();
        }

        if ($mode === 'all') {
            return StockLocation::orderedActive()->values();
        }

        if ($mode === 'selected' && Schema::hasTable('online_sales_locations')) {
            $rows = \App\Models\OnlineSalesLocation::query()
                ->with('location')
                ->orderBy('priority')
                ->orderBy('id')
                ->get();

            $locations = $rows->map(fn ($row) => $row->location)
                ->filter(fn ($loc) => $loc && $loc->is_active)
                ->values();

            if ($locations->isNotEmpty()) {
                return $locations;
            }
        }

        // single (default) — and fallback if selected list empty
        $single = null;
        if ($settings->online_sales_location_id) {
            $single = StockLocation::query()
                ->whereKey($settings->online_sales_location_id)
                ->where('is_active', true)
                ->first();
        }
        $single ??= StockLocation::mainStore() ?: StockLocation::orderedActive()->first();

        return $single ? collect([$single]) : collect();
    }

    public function onlineAvailableStock(Product $product, ?ProductVariant $variant = null, ?Setting $settings = null): int
    {
        if (! $this->enabled()) {
            return $variant ? max(0, (int) $variant->stock) : max(0, (int) $product->stock);
        }

        $locations = $this->onlineEligibleLocations($settings);
        if ($locations->isEmpty()) {
            return 0;
        }

        $total = 0;
        foreach ($locations as $location) {
            $total += $this->quantityAt($product, $variant, $location);
        }

        return max(0, $total);
    }

    /**
     * @deprecated Prefer onlineEligibleLocations() / onlineAvailableStock()
     */
    public function resolveOnlineLocation(?Setting $settings = null): ?StockLocation
    {
        return $this->onlineEligibleLocations($settings)->first();
    }

    /**
     * @deprecated Prefer fulfilOnlineSale()
     */
    public function onlineDeductLocation(?Setting $settings = null): ?StockLocation
    {
        return $this->onlineEligibleLocations($settings)->first();
    }

    /**
     * Deduct online sale qty across eligible locations (priority split), write allocations + ledger.
     *
     * @return array<int, array{location_id:int, quantity:int}>
     */
    public function fulfilOnlineSale(
        Product $product,
        ?ProductVariant $variant,
        int $quantity,
        Model $order,
        ?Model $orderItem = null,
        ?Setting $settings = null,
        bool $allowNegative = false
    ): array {
        $quantity = abs($quantity);
        if ($quantity === 0) {
            return [];
        }

        $settings ??= Setting::get_settings();
        $locations = $this->onlineEligibleLocations($settings);

        if ($locations->isEmpty()) {
            throw ValidationException::withMessages([
                'stock' => 'No online sales inventory location is configured.',
            ]);
        }

        if (! $this->enabled()) {
            app(ProductVariantService::class)->deductStock($product, $quantity, $variant, null, 'ONLINE_SALE', 'Online order', $order, $allowNegative);

            return [];
        }

        return DB::transaction(function () use ($product, $variant, $quantity, $order, $orderItem, $locations, $allowNegative, $settings) {
            $available = $this->onlineAvailableStock($product, $variant, $settings);
            if (! $allowNegative && $available < $quantity) {
                $label = $product->name.($variant ? ' — '.$variant->name : '');
                throw ValidationException::withMessages([
                    'stock' => "Only {$available} units of {$label} are currently available online. Requested: {$quantity}.",
                ]);
            }

            $remaining = $quantity;
            $allocations = [];
            $strategy = $settings->online_fulfilment_strategy ?? 'priority';

            // Manual strategy still auto-allocates at checkout to prevent oversell;
            // uses location sort_order among selected (priorities ignored in UI).
            $ordered = $strategy === 'manual'
                ? $locations->sortBy(fn (StockLocation $l) => [(int) $l->sort_order, $l->name])->values()
                : $locations->values();

            foreach ($ordered as $location) {
                if ($remaining <= 0) {
                    break;
                }

                $onHand = $this->quantityAt($product, $variant, $location);
                $take = min($onHand, $remaining);
                if ($take <= 0) {
                    continue;
                }

                $this->adjust(
                    $product,
                    $variant,
                    $location,
                    -$take,
                    'ONLINE_SALE',
                    'Online order '.($order->order_number ?? $order->getKey()),
                    $order,
                    null,
                    false
                );

                if (Schema::hasTable('order_stock_allocations')) {
                    \App\Models\OrderStockAllocation::query()->create([
                        'order_id' => $order->getKey(),
                        'order_item_id' => $orderItem?->getKey(),
                        'product_id' => $product->id,
                        'product_variant_id' => $variant?->id,
                        'stock_location_id' => $location->id,
                        'quantity' => $take,
                    ]);
                }

                $allocations[] = [
                    'location_id' => (int) $location->id,
                    'quantity' => $take,
                ];
                $remaining -= $take;
            }

            if ($remaining > 0) {
                if (! $allowNegative) {
                    throw ValidationException::withMessages([
                        'stock' => 'Unable to allocate online stock across configured locations.',
                    ]);
                }

                // Put remainder on first eligible location (backorders / negative allowed)
                $fallback = $ordered->first();
                $this->adjust(
                    $product,
                    $variant,
                    $fallback,
                    -$remaining,
                    'ONLINE_SALE',
                    'Online order '.($order->order_number ?? $order->getKey()).' (backorder)',
                    $order,
                    null,
                    true
                );
                if (Schema::hasTable('order_stock_allocations')) {
                    \App\Models\OrderStockAllocation::query()->create([
                        'order_id' => $order->getKey(),
                        'order_item_id' => $orderItem?->getKey(),
                        'product_id' => $product->id,
                        'product_variant_id' => $variant?->id,
                        'stock_location_id' => $fallback->id,
                        'quantity' => $remaining,
                    ]);
                }
                $allocations[] = [
                    'location_id' => (int) $fallback->id,
                    'quantity' => $remaining,
                ];
            }

            if (Schema::hasColumn('orders', 'stock_location_id') && ! empty($allocations[0]['location_id'])) {
                if (! $order->stock_location_id) {
                    $order->forceFill(['stock_location_id' => $allocations[0]['location_id']])->save();
                }
            }

            return $allocations;
        });
    }

    public function syncOnlineSalesLocations(array $locationIds, array $priorities = []): void
    {
        if (! Schema::hasTable('online_sales_locations')) {
            return;
        }

        $ids = collect($locationIds)->map(fn ($id) => (int) $id)->filter()->unique()->values();
        \App\Models\OnlineSalesLocation::query()->delete();

        foreach ($ids as $index => $locationId) {
            $priority = (int) ($priorities[$locationId] ?? ($index + 1));
            \App\Models\OnlineSalesLocation::query()->create([
                'stock_location_id' => $locationId,
                'priority' => max(1, $priority),
            ]);
        }
    }

    public function balance(Product $product, ?ProductVariant $variant, StockLocation|int $location): InventoryStock
    {
        $locationId = $location instanceof StockLocation ? (int) $location->id : (int) $location;
        $variantKey = $this->variantKey($variant);

        return InventoryStock::query()->firstOrCreate(
            [
                'product_id' => $product->id,
                'product_variant_id' => $variantKey,
                'stock_location_id' => $locationId,
            ],
            [
                'quantity' => 0,
                'reorder_level' => 0,
                'reorder_quantity' => 0,
            ]
        );
    }

    public function quantityAt(Product $product, ?ProductVariant $variant, StockLocation|int $location): int
    {
        if (! $this->enabled()) {
            return $variant ? max(0, (int) $variant->stock) : max(0, (int) $product->stock);
        }

        $locationId = $location instanceof StockLocation ? (int) $location->id : (int) $location;

        return (int) InventoryStock::query()
            ->where('product_id', $product->id)
            ->where('product_variant_id', $this->variantKey($variant))
            ->where('stock_location_id', $locationId)
            ->value('quantity') ?? 0;
    }

    public function totalQuantity(Product $product, ?ProductVariant $variant = null, bool $activeLocationsOnly = true): int
    {
        if (! $this->enabled()) {
            return $variant ? max(0, (int) $variant->stock) : max(0, (int) $product->totalAvailableStock());
        }

        $query = InventoryStock::query()->where('product_id', $product->id);
        if ($variant) {
            $query->where('product_variant_id', (int) $variant->id);
        } elseif ($product->usesVariants()) {
            // product total across all variants
        } else {
            $query->where('product_variant_id', 0);
        }

        if ($activeLocationsOnly && Schema::hasTable('stock_locations')) {
            $query->whereIn('stock_location_id', StockLocation::query()->where('is_active', true)->pluck('id'));
        }

        return (int) $query->sum('quantity');
    }

    public function quantitiesByLocation(Product $product, ?ProductVariant $variant = null): Collection
    {
        if (! $this->enabled()) {
            return collect();
        }

        $locations = StockLocation::orderedActive();
        $variantKey = $variant ? (int) $variant->id : ($product->usesVariants() ? null : 0);

        $rows = InventoryStock::query()
            ->where('product_id', $product->id)
            ->when($variantKey !== null, fn ($q) => $q->where('product_variant_id', $variantKey))
            ->get()
            ->groupBy('stock_location_id');

        return $locations->mapWithKeys(function (StockLocation $location) use ($rows, $variantKey) {
            $qty = 0;
            foreach ($rows->get($location->id, collect()) as $row) {
                if ($variantKey !== null && (int) $row->product_variant_id !== (int) $variantKey) {
                    continue;
                }
                $qty += (int) $row->quantity;
            }

            return [$location->id => $qty];
        });
    }

    /**
     * Apply a signed quantity change at a single location and write a ledger row.
     * For sales: from_location = location, to_location = null, quantity negative.
     * For purchases/returns: to_location = location, from_location = null, quantity positive.
     */
    public function adjust(
        Product $product,
        ?ProductVariant $variant,
        StockLocation|int $location,
        int $delta,
        string $type,
        ?string $reason = null,
        ?Model $reference = null,
        ?string $notes = null,
        bool $allowNegative = false
    ): ?StockMovement {
        if ($delta === 0) {
            return null;
        }

        if (! $this->enabled()) {
            return app(StockMovementService::class)->moveLegacy($product, $type, $delta, $reason, $reference, $variant);
        }

        return DB::transaction(function () use ($product, $variant, $location, $delta, $type, $reason, $reference, $notes, $allowNegative) {
            $locationModel = $location instanceof StockLocation
                ? StockLocation::query()->lockForUpdate()->findOrFail($location->id)
                : StockLocation::query()->lockForUpdate()->findOrFail($location);

            $balance = InventoryStock::query()
                ->where('product_id', $product->id)
                ->where('product_variant_id', $this->variantKey($variant))
                ->where('stock_location_id', $locationModel->id)
                ->lockForUpdate()
                ->first();

            if (! $balance) {
                $balance = InventoryStock::query()->create([
                    'product_id' => $product->id,
                    'product_variant_id' => $this->variantKey($variant),
                    'stock_location_id' => $locationModel->id,
                    'quantity' => 0,
                    'reorder_level' => 0,
                    'reorder_quantity' => 0,
                ]);
                $balance = InventoryStock::query()->whereKey($balance->id)->lockForUpdate()->firstOrFail();
            }

            $before = (int) $balance->quantity;
            $after = $before + $delta;

            if (! $allowNegative && $after < 0) {
                $label = $product->name.($variant ? ' — '.$variant->name : '');
                throw ValidationException::withMessages([
                    'stock' => "Insufficient stock for {$label} at {$locationModel->name}. Available: {$before}, requested: ".abs($delta).'.',
                ]);
            }

            $balance->forceFill(['quantity' => $after])->save();
            $this->syncCachedTotals($product, $variant);

            $fromId = $delta < 0 ? $locationModel->id : null;
            $toId = $delta > 0 ? $locationModel->id : null;

            return $this->recordMovement(
                product: $product,
                variant: $variant,
                type: $type,
                quantity: $delta,
                stockBefore: $before,
                stockAfter: $after,
                fromLocationId: $fromId,
                toLocationId: $toId,
                locationId: $locationModel->id,
                reason: $reason,
                notes: $notes,
                reference: $reference
            );
        });
    }

    public function transferQuantity(
        Product $product,
        ?ProductVariant $variant,
        StockLocation|int $from,
        StockLocation|int $to,
        int $quantity,
        string $type = 'TRANSFER',
        ?string $reason = null,
        ?Model $reference = null,
        ?string $notes = null
    ): void {
        if ($quantity <= 0) {
            throw ValidationException::withMessages(['quantity' => 'Transfer quantity must be greater than zero.']);
        }

        $fromId = $from instanceof StockLocation ? (int) $from->id : (int) $from;
        $toId = $to instanceof StockLocation ? (int) $to->id : (int) $to;

        if ($fromId === $toId) {
            throw ValidationException::withMessages(['to_location_id' => 'From and To locations must be different.']);
        }

        DB::transaction(function () use ($product, $variant, $fromId, $toId, $quantity, $type, $reason, $reference, $notes) {
            $fromLocation = StockLocation::query()->lockForUpdate()->findOrFail($fromId);
            $toLocation = StockLocation::query()->lockForUpdate()->findOrFail($toId);

            $fromBalance = $this->lockedBalance($product, $variant, $fromLocation->id);
            $toBalance = $this->lockedBalance($product, $variant, $toLocation->id);

            $fromBefore = (int) $fromBalance->quantity;
            if ($fromBefore < $quantity) {
                $label = $product->name.($variant ? ' — '.$variant->name : '');
                throw ValidationException::withMessages([
                    'quantity' => "Insufficient stock. Available in {$fromLocation->name}: {$fromBefore}. Requested: {$quantity}.",
                ]);
            }

            $toBefore = (int) $toBalance->quantity;
            $fromAfter = $fromBefore - $quantity;
            $toAfter = $toBefore + $quantity;

            $fromBalance->forceFill(['quantity' => $fromAfter])->save();
            $toBalance->forceFill(['quantity' => $toAfter])->save();
            $this->syncCachedTotals($product, $variant);

            $this->recordMovement(
                product: $product,
                variant: $variant,
                type: $type,
                quantity: $quantity,
                stockBefore: $fromBefore,
                stockAfter: $fromAfter,
                fromLocationId: $fromLocation->id,
                toLocationId: $toLocation->id,
                locationId: $fromLocation->id,
                reason: $reason,
                notes: $notes,
                reference: $reference
            );
        });
    }

    /**
     * Set absolute quantities per location (opening stock / product form).
     * Creates OPENING_STOCK or ADJUSTMENT movements for differences.
     */
    public function syncLocationQuantities(
        Product $product,
        ?ProductVariant $variant,
        array $locationQuantities,
        string $type = 'OPENING_STOCK',
        ?string $reason = null,
        bool $isCreate = false
    ): void {
        if (! $this->enabled()) {
            $total = collect($locationQuantities)->sum(fn ($q) => max(0, (int) $q));
            if ($variant) {
                $variant->forceFill(['stock' => $total])->save();
            }
            $this->syncCachedTotals($product, $variant);

            return;
        }

        DB::transaction(function () use ($product, $variant, $locationQuantities, $type, $reason, $isCreate) {
            foreach ($locationQuantities as $locationId => $qty) {
                $locationId = (int) $locationId;
                if ($locationId <= 0) {
                    continue;
                }
                $desired = max(0, (int) $qty);
                $balance = $this->lockedBalance($product, $variant, $locationId);
                $before = (int) $balance->quantity;
                $delta = $desired - $before;
                if ($delta === 0 && ! $isCreate) {
                    continue;
                }

                $balance->forceFill(['quantity' => $desired])->save();

                if ($delta !== 0) {
                    $movementType = $isCreate || $before === 0 && $type === 'OPENING_STOCK' ? 'OPENING_STOCK' : ($type === 'OPENING_STOCK' ? 'ADJUSTMENT' : $type);
                    $this->recordMovement(
                        product: $product,
                        variant: $variant,
                        type: $movementType,
                        quantity: $delta,
                        stockBefore: $before,
                        stockAfter: $desired,
                        fromLocationId: $delta < 0 ? $locationId : null,
                        toLocationId: $delta > 0 ? $locationId : null,
                        locationId: $locationId,
                        reason: $reason ?: ($isCreate ? 'Opening stock' : 'Stock update'),
                        notes: null,
                        reference: $product
                    );
                }
            }

            $this->syncCachedTotals($product, $variant);
        });
    }

    public function syncLocationReorderLevels(Product $product, ?ProductVariant $variant, array $levels): void
    {
        if (! $this->enabled()) {
            return;
        }

        foreach ($levels as $locationId => $row) {
            $locationId = (int) $locationId;
            if ($locationId <= 0) {
                continue;
            }
            $balance = $this->balance($product, $variant, $locationId);
            $balance->forceFill([
                'reorder_level' => max(0, (int) (is_array($row) ? ($row['reorder_level'] ?? 0) : $row)),
                'reorder_quantity' => max(0, (int) (is_array($row) ? ($row['reorder_quantity'] ?? 0) : 0)),
            ])->save();
        }
    }

    public function ensureLocationRows(Product $product, ?ProductVariant $variant = null): void
    {
        if (! $this->enabled()) {
            return;
        }

        foreach (StockLocation::orderedActive() as $location) {
            $this->balance($product, $variant, $location);
        }
    }

    public function syncCachedTotals(Product $product, ?ProductVariant $variant = null): void
    {
        if (! $this->enabled()) {
            return;
        }

        if ($variant) {
            $variantTotal = (int) InventoryStock::query()
                ->where('product_id', $product->id)
                ->where('product_variant_id', (int) $variant->id)
                ->sum('quantity');
            ProductVariant::query()->whereKey($variant->id)->update(['stock' => $variantTotal]);
        }

        $product = Product::query()->find($product->id);
        if (! $product) {
            return;
        }

        if ($product->usesVariants() && Schema::hasTable('product_variants')) {
            $total = (int) InventoryStock::query()
                ->where('product_id', $product->id)
                ->where('product_variant_id', '>', 0)
                ->sum('quantity');
        } else {
            $total = (int) InventoryStock::query()
                ->where('product_id', $product->id)
                ->where('product_variant_id', 0)
                ->sum('quantity');
        }

        $product->forceFill(['stock' => $total])->save();
    }

    public function locationTotals(): array
    {
        if (! $this->enabled()) {
            return [];
        }

        $activeIds = StockLocation::query()->where('is_active', true)->pluck('id');

        return InventoryStock::query()
            ->selectRaw('stock_location_id, SUM(quantity) as total')
            ->whereIn('stock_location_id', $activeIds)
            ->groupBy('stock_location_id')
            ->pluck('total', 'stock_location_id')
            ->map(fn ($v) => (int) $v)
            ->all();
    }

    private function lockedBalance(Product $product, ?ProductVariant $variant, int $locationId): InventoryStock
    {
        $balance = InventoryStock::query()
            ->where('product_id', $product->id)
            ->where('product_variant_id', $this->variantKey($variant))
            ->where('stock_location_id', $locationId)
            ->lockForUpdate()
            ->first();

        if ($balance) {
            return $balance;
        }

        $created = InventoryStock::query()->create([
            'product_id' => $product->id,
            'product_variant_id' => $this->variantKey($variant),
            'stock_location_id' => $locationId,
            'quantity' => 0,
            'reorder_level' => 0,
            'reorder_quantity' => 0,
        ]);

        return InventoryStock::query()->whereKey($created->id)->lockForUpdate()->firstOrFail();
    }

    private function recordMovement(
        Product $product,
        ?ProductVariant $variant,
        string $type,
        int $quantity,
        int $stockBefore,
        int $stockAfter,
        ?int $fromLocationId,
        ?int $toLocationId,
        ?int $locationId,
        ?string $reason,
        ?string $notes,
        ?Model $reference
    ): ?StockMovement {
        if (! Schema::hasTable('stock_movements')) {
            return null;
        }

        $payload = [
            'product_id' => $product->id,
            'user_id' => auth()->id(),
            'type' => strtoupper($type),
            'quantity' => $quantity,
            'stock_before' => $stockBefore,
            'stock_after' => $stockAfter,
            'reason' => $reason,
            'reference_type' => $reference ? $reference::class : null,
            'reference_id' => $reference?->getKey(),
        ];

        if (Schema::hasColumn('stock_movements', 'product_variant_id')) {
            $payload['product_variant_id'] = $variant?->id;
        }
        if (Schema::hasColumn('stock_movements', 'from_location_id')) {
            $payload['from_location_id'] = $fromLocationId;
        }
        if (Schema::hasColumn('stock_movements', 'to_location_id')) {
            $payload['to_location_id'] = $toLocationId;
        }
        if (Schema::hasColumn('stock_movements', 'stock_location_id')) {
            $payload['stock_location_id'] = $locationId;
        }
        if (Schema::hasColumn('stock_movements', 'notes')) {
            $payload['notes'] = $notes;
        }

        return StockMovement::query()->create($payload);
    }
}
