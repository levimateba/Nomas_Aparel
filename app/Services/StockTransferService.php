<?php

namespace App\Services;

use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\StockLocation;
use App\Models\StockMovement;
use App\Models\StockTransfer;
use App\Models\StockTransferItem;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class StockTransferService
{
    public function __construct(private readonly InventoryStockService $inventory)
    {
    }

    public function create(array $payload, bool $completeImmediately = true): StockTransfer
    {
        return DB::transaction(function () use ($payload, $completeImmediately) {
            $fromId = (int) $payload['from_location_id'];
            $toId = (int) $payload['to_location_id'];

            if ($fromId === $toId) {
                throw ValidationException::withMessages([
                    'to_location_id' => 'From and To locations must be different.',
                ]);
            }

            StockLocation::query()->findOrFail($fromId);
            StockLocation::query()->findOrFail($toId);

            $items = collect($payload['items'] ?? [])->filter(fn ($item) => (int) ($item['quantity'] ?? 0) > 0);
            if ($items->isEmpty()) {
                throw ValidationException::withMessages(['items' => 'Add at least one product to transfer.']);
            }

            $prepared = [];
            foreach ($items as $item) {
                $product = Product::query()->findOrFail($item['product_id']);
                $variant = null;
                if (! empty($item['product_variant_id'])) {
                    $variant = ProductVariant::query()->findOrFail($item['product_variant_id']);
                    if ((int) $variant->product_id !== (int) $product->id) {
                        throw ValidationException::withMessages(['items' => 'Variant does not belong to the selected product.']);
                    }
                } elseif ($product->usesVariants()) {
                    throw ValidationException::withMessages(['items' => $product->name.' requires a variant.']);
                }

                $qty = (int) $item['quantity'];
                $available = $this->inventory->quantityAt($product, $variant, $fromId);
                if ($available < $qty) {
                    $label = $product->name.($variant ? ' — '.$variant->name : '');
                    throw ValidationException::withMessages([
                        'items' => "Insufficient stock for {$label}. Available: {$available}, requested: {$qty}.",
                    ]);
                }

                $prepared[] = compact('product', 'variant', 'qty');
            }

            $transfer = StockTransfer::query()->create([
                'transfer_number' => $this->nextNumber(),
                'from_location_id' => $fromId,
                'to_location_id' => $toId,
                'user_id' => auth()->id(),
                'status' => $completeImmediately ? 'completed' : 'pending',
                'reason' => $payload['reason'] ?? null,
                'notes' => $payload['notes'] ?? null,
                'completed_by' => $completeImmediately ? auth()->id() : null,
                'completed_at' => $completeImmediately ? now() : null,
            ]);

            foreach ($prepared as $line) {
                /** @var StockTransferItem $item */
                $item = $transfer->items()->create([
                    'product_id' => $line['product']->id,
                    'product_variant_id' => $line['variant']?->id,
                    'quantity' => $line['qty'],
                ]);

                if ($completeImmediately) {
                    $this->inventory->transferQuantity(
                        $line['product'],
                        $line['variant'],
                        $fromId,
                        $toId,
                        $line['qty'],
                        'TRANSFER',
                        $transfer->reason,
                        $transfer,
                        $transfer->notes
                    );
                }
            }

            return $transfer->fresh(['items.product', 'items.variant', 'fromLocation', 'toLocation', 'user']);
        });
    }

    public function complete(StockTransfer $transfer): StockTransfer
    {
        return DB::transaction(function () use ($transfer) {
            $transfer = StockTransfer::query()->lockForUpdate()->findOrFail($transfer->id);
            if ($transfer->status === 'completed') {
                return $transfer->fresh(['items.product', 'items.variant', 'fromLocation', 'toLocation', 'user']);
            }
            if (in_array($transfer->status, ['cancelled'], true)) {
                throw ValidationException::withMessages(['status' => 'Cancelled transfers cannot be completed.']);
            }

            $transfer->load('items.product', 'items.variant');

            foreach ($transfer->items as $item) {
                $this->inventory->transferQuantity(
                    $item->product,
                    $item->variant,
                    $transfer->from_location_id,
                    $transfer->to_location_id,
                    (int) $item->quantity,
                    'TRANSFER',
                    $transfer->reason,
                    $transfer,
                    $transfer->notes
                );
            }

            $transfer->forceFill([
                'status' => 'completed',
                'completed_by' => auth()->id(),
                'completed_at' => now(),
                'approved_by' => $transfer->approved_by ?: auth()->id(),
                'approved_at' => $transfer->approved_at ?: now(),
            ])->save();

            return $transfer->fresh(['items.product', 'items.variant', 'fromLocation', 'toLocation', 'user']);
        });
    }

    public function cancel(StockTransfer $transfer): StockTransfer
    {
        if ($transfer->status === 'completed') {
            throw ValidationException::withMessages(['status' => 'Completed transfers cannot be cancelled.']);
        }

        $transfer->forceFill(['status' => 'cancelled'])->save();

        return $transfer->fresh();
    }

    private function nextNumber(): string
    {
        $latest = StockTransfer::query()->orderByDesc('id')->value('transfer_number');
        $seq = 1;
        if ($latest && preg_match('/(\d+)$/', $latest, $m)) {
            $seq = (int) $m[1] + 1;
        }

        return 'TR-'.str_pad((string) $seq, 6, '0', STR_PAD_LEFT);
    }
}
