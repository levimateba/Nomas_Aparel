<?php

namespace App\Services;

use App\Models\Product;
use App\Models\StockTake;
use App\Models\StockTakeItem;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class StockTakeService
{
    public function create(array $payload): StockTake
    {
        return DB::transaction(function () use ($payload) {
            $products = $this->filteredProducts($payload);
            if ($products->isEmpty()) {
                throw ValidationException::withMessages([
                    'products' => 'No products match the selected filters.',
                ]);
            }

            $take = StockTake::create([
                'reference' => $this->nextNumber(),
                'user_id' => auth()->id(),
                'type' => 'full',
                'status' => StockTake::STATUS_DRAFT,
                'category_id' => $payload['category_id'] ?? null,
                'vendor_id' => $payload['vendor_id'] ?? null,
                'filter_stock_status' => $payload['stock_status'] ?? 'all',
                'notes' => $payload['notes'] ?? null,
                'stocktake_date' => $payload['stocktake_date'] ?? now()->toDateString(),
            ]);

            foreach ($products as $product) {
                $this->makeItem($take, $product);
            }

            return $take->load('items');
        });
    }

    public function start(StockTake $stockTake): StockTake
    {
        if (! in_array($stockTake->status, [StockTake::STATUS_DRAFT, StockTake::STATUS_COUNTING], true)) {
            throw ValidationException::withMessages(['status' => 'This stock take can no longer be counted.']);
        }

        $stockTake->update([
            'status' => StockTake::STATUS_COUNTING,
            'started_at' => $stockTake->started_at ?? now(),
        ]);

        return $stockTake;
    }

    public function saveCounts(StockTake $stockTake, array $items): StockTake
    {
        if (! $stockTake->isEditable()) {
            throw ValidationException::withMessages(['status' => 'This stock take is locked.']);
        }

        return DB::transaction(function () use ($stockTake, $items) {
            foreach ($items as $row) {
                $item = StockTakeItem::query()
                    ->where('stock_take_id', $stockTake->id)
                    ->where('id', $row['id'] ?? 0)
                    ->first();
                if (! $item) {
                    continue;
                }

                $physical = ($row['counted_qty'] ?? '') === '' || ($row['counted_qty'] ?? null) === null
                    ? null
                    : (int) $row['counted_qty'];

                $item->fill([
                    'counted_qty' => $physical,
                    'reason' => $row['reason'] ?? $item->reason,
                ]);
                $item->refreshVariance();
            }

            if ($stockTake->status === StockTake::STATUS_DRAFT) {
                $stockTake->update([
                    'status' => StockTake::STATUS_COUNTING,
                    'started_at' => $stockTake->started_at ?? now(),
                ]);
            }

            $this->refreshTotals($stockTake);

            return $stockTake->fresh('items');
        });
    }

    public function submitForReview(StockTake $stockTake): StockTake
    {
        if (! in_array($stockTake->status, [StockTake::STATUS_COUNTING, StockTake::STATUS_REVIEW], true)) {
            throw ValidationException::withMessages(['status' => 'Finish counting before submitting for review.']);
        }

        $uncounted = $stockTake->items()->whereNull('counted_qty')->count();
        if ($uncounted > 0) {
            throw ValidationException::withMessages([
                'items' => $uncounted . ' product(s) still need a physical count.',
            ]);
        }

        $stockTake->update(['status' => StockTake::STATUS_REVIEW]);
        $this->refreshTotals($stockTake);

        return $stockTake->fresh('items');
    }

    public function approve(StockTake $stockTake): StockTake
    {
        if (! $stockTake->canApprove()) {
            throw ValidationException::withMessages(['status' => 'Only reviewed stock takes can be approved.']);
        }

        return DB::transaction(function () use ($stockTake) {
            $locked = StockTake::query()->lockForUpdate()->findOrFail($stockTake->id);
            if ($locked->status === StockTake::STATUS_APPROVED) {
                return $locked;
            }

            foreach ($locked->items()->whereNotNull('counted_qty')->get() as $item) {
                $product = Product::query()->lockForUpdate()->find($item->product_id);
                if ($product) {
                    $product->update(['stock' => $item->counted_qty]);
                }
            }

            $locked->update([
                'status' => StockTake::STATUS_APPROVED,
                'completed_by' => auth()->id(),
                'completed_at' => now(),
            ]);
            $this->refreshTotals($locked);

            return $locked->fresh('items');
        });
    }

    public function cancel(StockTake $stockTake): StockTake
    {
        if (in_array($stockTake->status, [StockTake::STATUS_APPROVED, StockTake::STATUS_CANCELLED], true)) {
            throw ValidationException::withMessages(['status' => 'This stock take cannot be cancelled.']);
        }

        $stockTake->update(['status' => StockTake::STATUS_CANCELLED]);

        return $stockTake;
    }

    public function addOrIncrement(StockTake $stockTake, Product $product): StockTakeItem
    {
        if (! $stockTake->isEditable()) {
            throw ValidationException::withMessages(['status' => 'This stock take is locked.']);
        }

        $item = $stockTake->items()->where('product_id', $product->id)->first();
        if ($item) {
            $item->counted_qty = (int) $item->counted_qty + 1;
            $item->refreshVariance();
            $this->refreshTotals($stockTake);

            return $item->refresh();
        }

        $item = $this->makeItem($stockTake, $product, 1);
        $item->refreshVariance();
        $this->refreshTotals($stockTake);

        return $item;
    }

    public function refreshTotals(StockTake $stockTake): void
    {
        $items = $stockTake->items()->get();
        $stockTake->update([
            'positive_variance_value' => round((float) $items->where('variance', '>', 0)->sum('variance_value'), 2),
            'negative_variance_value' => round((float) $items->where('variance', '<', 0)->sum('variance_value'), 2),
        ]);
    }

    private function makeItem(StockTake $stockTake, Product $product, ?int $countedQty = null): StockTakeItem
    {
        $item = StockTakeItem::create([
            'stock_take_id' => $stockTake->id,
            'product_id' => $product->id,
            'product_name' => $product->name,
            'sku' => $product->sku,
            'barcode' => $product->barcode,
            'system_qty' => (int) $product->stock,
            'counted_qty' => $countedQty,
            'unit_cost' => $product->currentPrice(),
            'variance' => 0,
            'variance_value' => 0,
        ]);

        if ($countedQty !== null) {
            $item->refreshVariance();
        }

        return $item;
    }

    private function filteredProducts(array $payload)
    {
        return Product::query()
            ->where('is_active', true)
            ->when($payload['category_id'] ?? null, fn ($query, $id) => $query->where('category_id', $id))
            ->when($payload['vendor_id'] ?? null, fn ($query, $id) => $query->where('vendor_id', $id))
            ->when($payload['product_id'] ?? null, fn ($query, $id) => $query->where('id', $id))
            ->when($payload['stock_status'] ?? null, function ($query, $status) {
                return match ($status) {
                    'low' => $query->where('stock', '<=', 5),
                    'out' => $query->where('stock', 0),
                    'in' => $query->where('stock', '>', 0),
                    default => $query,
                };
            })
            ->orderBy('name')
            ->get();
    }

    private function nextNumber(): string
    {
        $count = StockTake::query()->whereDate('created_at', today())->count() + 1;

        return 'STK-' . now()->format('Ymd') . '-' . str_pad((string) $count, 4, '0', STR_PAD_LEFT);
    }
}
