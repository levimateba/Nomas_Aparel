<?php

namespace App\Services;

use App\Models\Product;
use App\Models\Purchase;
use App\Models\PurchasePayment;
use App\Support\Audit;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\ValidationException;

class PurchaseService
{
    public function __construct(private readonly StockMovementService $stockMovements)
    {
    }

    public function create(array $payload): Purchase
    {
        return DB::transaction(function () use ($payload) {
            $items = collect($payload['items'] ?? [])->filter(fn ($item) => (int) ($item['quantity'] ?? 0) > 0);
            if ($items->isEmpty()) {
                throw ValidationException::withMessages(['items' => 'Add at least one product to receive stock.']);
            }

            $subtotal = 0.0;
            $prepared = [];

            foreach ($items as $item) {
                $product = Product::query()->lockForUpdate()->findOrFail($item['product_id']);
                $qty = (int) $item['quantity'];
                $unitCost = (float) ($item['buying_price'] ?? $product->buying_price ?? 0);
                $line = round($qty * $unitCost, 2);
                $subtotal += $line;
                $prepared[] = compact('product', 'qty', 'unitCost', 'line');
            }

            $discount = (float) ($payload['discount'] ?? 0);
            $tax = (float) ($payload['tax'] ?? 0);
            $total = max($subtotal - $discount, 0) + $tax;
            $status = $payload['payment_status'] ?? 'unpaid';
            $initialPaid = match ($status) {
                'paid' => $total,
                'partial' => (float) ($payload['amount_paid'] ?? 0),
                default => 0.0,
            };

            $purchaseData = [
                'purchase_number' => $this->nextNumber(),
                'supplier_id' => $payload['supplier_id'] ?? null,
                'user_id' => auth()->id(),
                'purchase_order_id' => $payload['purchase_order_id'] ?? null,
                'purchase_date' => $payload['purchase_date'] ?? today()->toDateString(),
                'due_date' => $payload['due_date'] ?? null,
                'invoice_reference' => $payload['invoice_reference'] ?? null,
                'subtotal' => round($subtotal, 2),
                'tax' => $tax,
                'discount' => $discount,
                'total' => round($total, 2),
                'amount_paid' => 0,
                'balance_due' => round($total, 2),
                'payment_status' => 'unpaid',
                'notes' => $payload['notes'] ?? null,
            ];
            if (Schema::hasColumn('purchases', 'stock_location_id')) {
                $purchaseData['stock_location_id'] = $payload['stock_location_id']
                    ?? \App\Models\StockLocation::mainStore()?->id;
            }
            $purchase = Purchase::create($purchaseData);

            foreach ($prepared as $line) {
                $item = $purchase->items()->create([
                    'product_id' => $line['product']->id,
                    'quantity' => $line['qty'],
                    'buying_price' => $line['unitCost'],
                    'subtotal' => $line['line'],
                    'total' => $line['line'],
                ]);

                if (Schema::hasColumn('products', 'buying_price') && $line['unitCost'] > 0) {
                    $line['product']->forceFill(['buying_price' => $line['unitCost']])->save();
                }

                $location = null;
                if (! empty($payload['stock_location_id'])) {
                    $location = \App\Models\StockLocation::query()->find($payload['stock_location_id']);
                }
                $location ??= \App\Models\StockLocation::mainStore();

                $this->stockMovements->move(
                    $line['product']->fresh(),
                    'PURCHASE',
                    $line['qty'],
                    'Purchase '.$purchase->purchase_number,
                    $item,
                    null,
                    $location
                );
            }

            if ($initialPaid > 0) {
                $this->recordPayment($purchase, [
                    'amount' => min($initialPaid, $total),
                    'payment_method' => $payload['payment_method'] ?? 'Cash',
                    'reference' => $payload['payment_reference'] ?? null,
                    'paid_at' => $payload['purchase_date'] ?? now()->toDateTimeString(),
                    'notes' => 'Initial purchase payment',
                ]);
            } else {
                $purchase->refreshPaymentState();
            }

            Audit::log('supplier_purchase', 'Stock received '.$purchase->purchase_number, $purchase, [
                'total' => $purchase->total,
            ], 'purchases');

            return $purchase->fresh(['items.product', 'supplier', 'user', 'payments']);
        });
    }

    public function recordPayment(Purchase $purchase, array $payload): PurchasePayment
    {
        return DB::transaction(function () use ($purchase, $payload) {
            $purchase = Purchase::query()->lockForUpdate()->findOrFail($purchase->id);
            $amount = (float) ($payload['amount'] ?? 0);
            if ($amount <= 0) {
                throw ValidationException::withMessages(['amount' => 'Payment amount must be greater than zero.']);
            }

            $balance = (float) $purchase->balance_due;
            if ($balance <= 0) {
                throw ValidationException::withMessages(['amount' => 'This purchase is already fully paid.']);
            }
            if ($amount > $balance + 0.0001) {
                throw ValidationException::withMessages(['amount' => 'Payment exceeds the outstanding balance.']);
            }

            $payment = $purchase->payments()->create([
                'user_id' => auth()->id(),
                'amount' => round($amount, 2),
                'payment_method' => $payload['payment_method'] ?? 'Cash',
                'reference' => $payload['reference'] ?? null,
                'paid_at' => $payload['paid_at'] ?? now(),
                'notes' => $payload['notes'] ?? null,
            ]);

            $purchase->refreshPaymentState();
            Audit::log('purchase_payment', 'Payment on '.$purchase->purchase_number, $purchase, [
                'amount' => $payment->amount,
            ], 'purchases');

            return $payment;
        });
    }

    public function nextNumber(): string
    {
        $prefix = 'PUR-'.now()->format('Ymd').'-';
        $latest = Purchase::query()->where('purchase_number', 'like', $prefix.'%')->orderByDesc('id')->value('purchase_number');
        $seq = 1;
        if ($latest && preg_match('/-(\d+)$/', $latest, $m)) {
            $seq = ((int) $m[1]) + 1;
        }

        return $prefix.str_pad((string) $seq, 4, '0', STR_PAD_LEFT);
    }
}
