<?php

namespace App\Services;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\OrderReturn;
use App\Models\Product;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class OrderReturnService
{
    public function process(Order $order, array $payload): OrderReturn
    {
        return DB::transaction(function () use ($order, $payload) {
            if (! $order->isReturnable()) {
                throw ValidationException::withMessages(['order' => 'Only paid in-store sales can be returned.']);
            }

            $order->load('items.product');
            $lines = collect($payload['items'] ?? [])->filter(fn ($item) => (int) ($item['quantity'] ?? 0) > 0);

            if ($lines->isEmpty()) {
                throw ValidationException::withMessages(['items' => 'Select at least one item to return.']);
            }

            $prepared = [];
            $total = 0.0;

            foreach ($lines as $line) {
                $orderItem = $order->items->firstWhere('id', (int) ($line['order_item_id'] ?? 0));
                if (! $orderItem) {
                    throw ValidationException::withMessages(['items' => 'Invalid sale item selected.']);
                }

                $qty = (int) $line['quantity'];
                $remaining = $orderItem->returnableQuantity();
                if ($qty > $remaining) {
                    throw ValidationException::withMessages([
                        'items' => $orderItem->product_name.' only has '.$remaining.' left to return.',
                    ]);
                }

                $unit = (int) $orderItem->quantity > 0 ? ((float) $orderItem->line_total / (int) $orderItem->quantity) : 0;
                $lineTotal = round($unit * $qty, 2);
                $total += $lineTotal;
                $prepared[] = compact('orderItem', 'qty', 'lineTotal');
            }

            $return = OrderReturn::create([
                'return_number' => 'RET-'.now()->format('YmdHis').'-'.random_int(100, 999),
                'order_id' => $order->id,
                'user_id' => auth()->id(),
                'total' => $total,
                'reason' => $payload['reason'],
                'refund_method' => $payload['refund_method'] ?? 'original',
            ]);

            foreach ($prepared as $line) {
                $return->items()->create([
                    'order_item_id' => $line['orderItem']->id,
                    'product_id' => $line['orderItem']->product_id,
                    'quantity' => $line['qty'],
                    'total' => $line['lineTotal'],
                ]);

                $line['orderItem']->forceFill([
                    'returned_quantity' => (int) $line['orderItem']->returned_quantity + $line['qty'],
                ])->save();

                if ($line['orderItem']->product_id) {
                    $product = Product::query()->lockForUpdate()->find($line['orderItem']->product_id);
                    $product?->increment('stock', $line['qty']);
                }
            }

            return $return->fresh(['items', 'order', 'user']);
        });
    }
}
