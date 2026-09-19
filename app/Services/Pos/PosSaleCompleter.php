<?php

namespace App\Services\Pos;

use App\Models\Coupon;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\Setting;
use App\Models\ShopCustomer;
use App\Services\CashierShiftService;
use App\Services\LoyaltyService;
use App\Services\ProductVariantService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\ValidationException;

/**
 * Shared POS sale completion used by cash/card charge and M-PESA callback.
 * Marks the order paid and deducts stock in one DB transaction.
 */
class PosSaleCompleter
{
    /**
     * @param  array{
     *     cart: array,
     *     totals: array,
     *     payment_method: string,
     *     customer_name?: ?string,
     *     customer_phone?: ?string,
     *     customer_email?: ?string,
     *     notes?: ?string,
     *     user_id: int,
     *     shop_customer_id?: ?int,
     *     payment_reference?: ?string,
     * }  $context
     */
    public function complete(array $context): Order
    {
        $cart = $context['cart'] ?? [];
        if (empty($cart)) {
            throw ValidationException::withMessages(['pos' => 'Add at least one product to complete a sale.']);
        }

        $settings = Setting::get_settings();
        $loyalty = app(LoyaltyService::class);
        $loyaltySettings = $loyalty->settings();

        $totals = $context['totals'];
        $total = (float) ($totals['total'] ?? 0);
        $orderNumber = 'POS-'.now()->format('YmdHis').'-'.random_int(100, 999);

        $shopCustomer = null;
        $shopCustomerId = $context['shop_customer_id'] ?? null;
        if ($loyaltySettings->enabled && $shopCustomerId) {
            $shopCustomer = ShopCustomer::query()->with('loyaltyCard')->find((int) $shopCustomerId);
        }

        $customerName = $context['customer_name'] ?? null;
        $customerPhone = $context['customer_phone'] ?? null;
        $customerEmail = $context['customer_email'] ?? null;
        if ($shopCustomer) {
            $customerName = $shopCustomer->name;
            $customerPhone = $shopCustomer->phone;
            $customerEmail = $shopCustomer->email ?: $customerEmail;
        }

        $notes = trim((string) ($context['notes'] ?? ''));
        $paymentReference = $context['payment_reference'] ?? null;
        if ($paymentReference) {
            $notes = trim($notes."\n".'M-PESA receipt: '.$paymentReference);
        }

        $redeemPoints = (int) ($totals['loyalty_points_redeemed'] ?? 0);
        $loyaltyDiscount = (float) ($totals['loyalty_discount'] ?? 0);
        $userId = (int) $context['user_id'];
        $paymentMethod = (string) $context['payment_method'];

        return DB::transaction(function () use (
            $cart,
            $total,
            $totals,
            $orderNumber,
            $notes,
            $settings,
            $shopCustomer,
            $loyalty,
            $loyaltySettings,
            $redeemPoints,
            $loyaltyDiscount,
            $userId,
            $paymentMethod,
            $paymentReference,
            $customerName,
            $customerPhone,
            $customerEmail
        ) {
            $payload = [
                'order_number' => $orderNumber,
                'user_id' => $userId,
                'customer_name' => $customerName ?: 'Walk-in Customer',
                'customer_email' => $customerEmail ?: 'walkin@pos.local',
                'customer_phone' => $customerPhone,
                'shipping_address' => 'In-store sale',
                'notes' => $notes ?: null,
                'status' => 'paid',
                'payment_method' => $paymentMethod,
                'payment_status' => 'paid',
                'total_amount' => $total,
            ];

            if (Schema::hasColumn('orders', 'payment_reference') && $paymentReference) {
                $payload['payment_reference'] = $paymentReference;
            }
            if (Schema::hasColumn('orders', 'cashier_shift_id')) {
                $payload['cashier_shift_id'] = app(CashierShiftService::class)->currentOpen($userId)?->id;
            }
            if (Schema::hasColumn('orders', 'source')) {
                $payload['source'] = 'pos';
            }
            if (Schema::hasColumn('orders', 'discount_amount')) {
                $payload['discount_amount'] = round(
                    (float) ($totals['coupon_discount'] ?? 0) + (float) ($totals['sale_discount'] ?? 0),
                    2
                );
            }
            if (Schema::hasColumn('orders', 'tax_amount')) {
                $payload['tax_amount'] = $totals['tax'] ?? 0;
            }
            if (Schema::hasColumn('orders', 'coupon_code')) {
                $payload['coupon_code'] = $totals['coupon_code'] ?? null;
            }
            if (Schema::hasColumn('orders', 'shop_customer_id') && $shopCustomer) {
                $payload['shop_customer_id'] = $shopCustomer->id;
            }
            if (Schema::hasColumn('orders', 'loyalty_discount_amount')) {
                $payload['loyalty_discount_amount'] = $loyaltyDiscount;
            }
            if (Schema::hasColumn('orders', 'loyalty_points_redeemed')) {
                $payload['loyalty_points_redeemed'] = $redeemPoints;
            }

            $created = Order::create($payload);

            $inventory = app(\App\Services\InventoryStockService::class);
            $posLocation = $inventory->resolvePosLocation($settings);
            if ($posLocation && Schema::hasColumn('orders', 'stock_location_id')) {
                $created->forceFill(['stock_location_id' => $posLocation->id])->save();
            }

            foreach ($cart as $item) {
                $product = Product::query()->lockForUpdate()->find($item['product_id']);
                if (! $product || ! $product->is_active) {
                    throw ValidationException::withMessages(['pos' => 'One or more products are no longer available.']);
                }

                $variant = null;
                if (! empty($item['product_variant_id']) && Schema::hasTable('product_variants')) {
                    $variant = ProductVariant::query()->lockForUpdate()->find($item['product_variant_id']);
                    if (! $variant || ! $variant->is_active || (int) $variant->product_id !== (int) $product->id) {
                        throw ValidationException::withMessages(['pos' => 'One or more variants are no longer available.']);
                    }
                } elseif ($product->usesVariants()) {
                    throw ValidationException::withMessages(['pos' => $product->name.' requires a specific variant. Scan the variant barcode.']);
                }

                $stockService = app(ProductVariantService::class);
                $available = $inventory->enabled()
                    ? $inventory->posAvailableStock($product, $variant, $settings)
                    : $stockService->availableStock($product, $variant, $posLocation);
                if (! $settings->allow_negative_stock && $available < $item['qty']) {
                    $locName = ($settings->pos_sell_from_all_locations ?? false)
                        ? 'POS locations'
                        : ($posLocation?->name ?? 'POS location');
                    $totalStock = $inventory->enabled() ? $inventory->totalQuantity($product, $variant) : $available;
                    $msg = 'Insufficient stock at '.$locName.' for '.($item['name'] ?? $product->name).'. Available: '.$available.'.';
                    if ($totalStock > $available) {
                        $msg .= ' Total across locations: '.$totalStock.'.';
                    }
                    throw ValidationException::withMessages(['pos' => $msg]);
                }

                if ($inventory->enabled()) {
                    $inventory->fulfilPosSale(
                        $product,
                        $variant,
                        (int) $item['qty'],
                        $created,
                        $settings,
                        (bool) $settings->allow_negative_stock
                    );
                } else {
                    $stockService->deductStock(
                        $product,
                        (int) $item['qty'],
                        $variant,
                        $posLocation,
                        'SALE',
                        'POS sale '.$orderNumber,
                        $created,
                        (bool) $settings->allow_negative_stock
                    );
                }

                $itemPayload = [
                    'order_id' => $created->id,
                    'product_id' => $product->id,
                    'product_name' => $item['name'],
                    'unit_price' => $item['price'],
                    'quantity' => $item['qty'],
                    'line_total' => $item['price'] * $item['qty'],
                ];
                if (Schema::hasColumn('order_items', 'product_variant_id')) {
                    $itemPayload['product_variant_id'] = $variant?->id;
                }
                if (Schema::hasColumn('order_items', 'variant_name')) {
                    $itemPayload['variant_name'] = $variant?->name;
                }
                if (Schema::hasColumn('order_items', 'sku')) {
                    $itemPayload['sku'] = $item['sku'] ?? $variant?->sku ?? $product->sku;
                }

                OrderItem::create($itemPayload);
            }

            if (! empty($totals['coupon_code'])) {
                Coupon::query()->whereRaw('UPPER(code) = ?', [strtoupper($totals['coupon_code'])])->increment('used_count');
            }

            if ($loyaltySettings->enabled && $shopCustomer) {
                if (! $shopCustomer->loyaltyCard) {
                    $loyalty->createLoyaltyCard($shopCustomer);
                    $shopCustomer->load('loyaltyCard');
                }

                if ($redeemPoints > 0 && $loyaltySettings->redemption_enabled && $loyaltySettings->allow_redemption_at_pos) {
                    $loyalty->redeemPoints($shopCustomer, $created, $redeemPoints, $loyaltyDiscount, $userId);
                }

                $eligible = $loyalty->getEligibleAmount(
                    (float) $totals['subtotal'],
                    (float) ($totals['coupon_discount'] ?? 0) + (float) ($totals['sale_discount'] ?? 0),
                    $loyaltyDiscount
                );
                $earned = $loyalty->calculateEarnedPoints($eligible);
                if ($earned > 0) {
                    $loyalty->earnPoints($shopCustomer, $created, $earned, $userId);
                    if (Schema::hasColumn('orders', 'loyalty_points_earned')) {
                        $created->forceFill(['loyalty_points_earned' => $earned])->save();
                    }
                }
            }

            return $created->fresh();
        });
    }
}
