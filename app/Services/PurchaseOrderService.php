<?php

namespace App\Services;

use App\Models\Product;
use App\Models\PurchaseOrder;
use App\Support\Audit;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class PurchaseOrderService
{
    public function __construct(private readonly PurchaseService $purchases)
    {
    }

    public function create(array $payload, string $status = PurchaseOrder::STATUS_DRAFT): PurchaseOrder
    {
        return DB::transaction(function () use ($payload, $status) {
            $items = collect($payload['items'] ?? [])->filter(fn ($item) => (int) ($item['quantity'] ?? 0) > 0);
            if ($items->isEmpty()) {
                throw ValidationException::withMessages(['items' => 'Add at least one product to the purchase order.']);
            }

            $order = PurchaseOrder::create([
                'po_number' => $this->nextNumber(),
                'supplier_id' => $payload['supplier_id'] ?? null,
                'user_id' => auth()->id(),
                'order_date' => $payload['order_date'] ?? today()->toDateString(),
                'expected_date' => $payload['expected_date'] ?? null,
                'discount' => (float) ($payload['discount'] ?? 0),
                'tax' => (float) ($payload['tax'] ?? 0),
                'status' => $status,
                'notes' => $payload['notes'] ?? null,
            ]);

            foreach ($items as $row) {
                $product = Product::query()->findOrFail($row['product_id']);
                $qty = (int) $row['quantity'];
                $price = (float) ($row['buying_price'] ?? $product->buying_price ?? 0);
                $line = round($qty * $price, 2);
                $order->items()->create([
                    'product_id' => $product->id,
                    'ordered_qty' => $qty,
                    'received_qty' => 0,
                    'buying_price' => $price,
                    'subtotal' => $line,
                    'total' => $line,
                ]);
            }

            $order->refreshTotalsAndStatus();
            if ($status === PurchaseOrder::STATUS_DRAFT) {
                $order->forceFill(['status' => PurchaseOrder::STATUS_DRAFT])->save();
            }

            Audit::log('purchase_order_created', 'Created PO '.$order->po_number, $order, [], 'purchasing');

            return $order->fresh(['items.product', 'supplier']);
        });
    }

    public function submit(PurchaseOrder $order): PurchaseOrder
    {
        if ($order->status !== PurchaseOrder::STATUS_DRAFT) {
            throw ValidationException::withMessages(['status' => 'Only draft purchase orders can be submitted.']);
        }

        $order->forceFill([
            'status' => PurchaseOrder::STATUS_SUBMITTED,
            'submitted_at' => now(),
        ])->save();

        Audit::log('purchase_order_submitted', 'Submitted PO '.$order->po_number, $order, [], 'purchasing');

        return $order;
    }

    public function approve(PurchaseOrder $order): PurchaseOrder
    {
        if (! in_array($order->status, [PurchaseOrder::STATUS_SUBMITTED, PurchaseOrder::STATUS_DRAFT], true)) {
            throw ValidationException::withMessages(['status' => 'This purchase order cannot be approved.']);
        }

        $order->forceFill([
            'status' => PurchaseOrder::STATUS_APPROVED,
            'approved_by' => auth()->id(),
            'approved_at' => now(),
            'submitted_at' => $order->submitted_at ?? now(),
        ])->save();

        Audit::log('purchase_order_approved', 'Approved PO '.$order->po_number, $order, [], 'purchasing');

        return $order;
    }

    public function cancel(PurchaseOrder $order): PurchaseOrder
    {
        if (in_array($order->status, [PurchaseOrder::STATUS_RECEIVED, PurchaseOrder::STATUS_CLOSED, PurchaseOrder::STATUS_CANCELLED], true)) {
            throw ValidationException::withMessages(['status' => 'This purchase order cannot be cancelled.']);
        }
        if ((int) $order->items()->sum('received_qty') > 0) {
            throw ValidationException::withMessages(['status' => 'Cancel is not allowed after stock has been received.']);
        }

        $order->forceFill([
            'status' => PurchaseOrder::STATUS_CANCELLED,
            'cancelled_at' => now(),
        ])->save();

        Audit::log('purchase_order_cancelled', 'Cancelled PO '.$order->po_number, $order, [], 'purchasing');

        return $order;
    }

    public function receive(PurchaseOrder $order, array $payload): PurchaseOrder
    {
        if (! in_array($order->status, [
            PurchaseOrder::STATUS_APPROVED,
            PurchaseOrder::STATUS_PARTIALLY_RECEIVED,
        ], true)) {
            throw ValidationException::withMessages(['status' => 'Approve the purchase order before receiving stock.']);
        }

        return DB::transaction(function () use ($order, $payload) {
            $order = PurchaseOrder::query()->with('items')->lockForUpdate()->findOrFail($order->id);
            $receiveRows = collect($payload['items'] ?? []);
            $items = [];

            foreach ($order->items as $poItem) {
                $row = $receiveRows->firstWhere('purchase_order_item_id', $poItem->id)
                    ?? $receiveRows->firstWhere('product_id', $poItem->product_id);
                $qty = (int) ($row['quantity'] ?? 0);
                if ($qty <= 0) {
                    continue;
                }
                $qty = min($qty, $poItem->outstandingQty());
                if ($qty <= 0) {
                    continue;
                }
                $items[] = [
                    'product_id' => $poItem->product_id,
                    'quantity' => $qty,
                    'buying_price' => (float) ($row['buying_price'] ?? $poItem->buying_price),
                    '_po_item' => $poItem,
                ];
            }

            if ($items === []) {
                throw ValidationException::withMessages(['items' => 'Enter quantities to receive.']);
            }

            $purchase = $this->purchases->create([
                'supplier_id' => $order->supplier_id,
                'purchase_order_id' => $order->id,
                'purchase_date' => $payload['purchase_date'] ?? today()->toDateString(),
                'invoice_reference' => $payload['invoice_reference'] ?? null,
                'notes' => $payload['notes'] ?? ('Received against '.$order->po_number),
                'payment_status' => $payload['payment_status'] ?? 'unpaid',
                'amount_paid' => $payload['amount_paid'] ?? 0,
                'payment_method' => $payload['payment_method'] ?? 'Cash',
                'items' => collect($items)->map(fn ($i) => [
                    'product_id' => $i['product_id'],
                    'quantity' => $i['quantity'],
                    'buying_price' => $i['buying_price'],
                ])->all(),
            ]);

            foreach ($items as $line) {
                /** @var \App\Models\PurchaseOrderItem $poItem */
                $poItem = $line['_po_item'];
                $poItem->forceFill([
                    'received_qty' => (int) $poItem->received_qty + (int) $line['quantity'],
                ])->save();
            }

            $order->refreshTotalsAndStatus();
            Audit::log('purchase_order_received', 'Received stock for '.$order->po_number.' via '.$purchase->purchase_number, $order, [], 'purchasing');

            return $order->fresh(['items.product', 'supplier', 'receipts']);
        });
    }

    public function nextNumber(): string
    {
        $prefix = 'PO-'.now()->format('Ymd').'-';
        $latest = PurchaseOrder::query()->where('po_number', 'like', $prefix.'%')->orderByDesc('id')->value('po_number');
        $seq = 1;
        if ($latest && preg_match('/-(\d+)$/', $latest, $m)) {
            $seq = ((int) $m[1]) + 1;
        }

        return $prefix.str_pad((string) $seq, 4, '0', STR_PAD_LEFT);
    }
}
