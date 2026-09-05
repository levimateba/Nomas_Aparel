<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
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
use App\Support\Audit;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class PosController extends Controller
{
    public function index(Request $request): View|RedirectResponse
    {
        $settings = Setting::get_settings();
        if ($settings->require_open_shift) {
            $open = app(CashierShiftService::class)->currentOpen(auth()->id());
            if (! $open) {
                return redirect()
                    ->route('admin.shifts.index')
                    ->withErrors(['shift' => 'Open a cashier shift before using the POS.']);
            }
        }

        $query = trim((string) $request->input('q', ''));
        $scanMode = $request->boolean('mode') || $request->input('mode') === 'scan'
            || $request->routeIs('admin.pos.scan');
        $posRoute = $scanMode ? 'admin.pos.scan' : 'admin.pos.index';
        $redirectParams = $request->only('category_id');

        if ($query !== '') {
            $resolved = Product::resolveActiveScan($query);

            if ($resolved) {
                $error = $this->addProductToCart($resolved['product'], 1, $resolved['variant']);
                if ($error) {
                    return redirect()
                        ->route($posRoute, $redirectParams)
                        ->withErrors(['pos' => $error]);
                }

                $label = $resolved['variant']
                    ? $resolved['variant']->displayName()
                    : $resolved['product']->name;

                return redirect()
                    ->route($posRoute, $redirectParams)
                    ->with('success', $label.' added.');
            }
        }

        $products = Product::query()
            ->with(['category', 'activeVariants'])
            ->where('is_active', true)
            ->when($request->filled('category_id'), fn ($builder) => $builder->where('category_id', $request->integer('category_id')))
            ->when($query !== '', function ($builder) use ($query) {
                $builder->where(function ($search) use ($query) {
                    $search->where('name', 'like', '%' . $query . '%')
                        ->orWhere('sku', 'like', '%' . $query . '%');
                    if (Schema::hasColumn('products', 'barcode')) {
                        $search->orWhere('barcode', 'like', '%' . $query . '%');
                    }
                    if (Schema::hasTable('product_variants')) {
                        $search->orWhereHas('variants', function ($v) use ($query) {
                            $v->where('is_active', true)
                                ->where(function ($inner) use ($query) {
                                    $inner->where('sku', 'like', '%'.$query.'%')
                                        ->orWhere('barcode', 'like', '%'.$query.'%')
                                        ->orWhere('name', 'like', '%'.$query.'%');
                                });
                        });
                    }
                });
            })
            ->orderBy('name')
            ->paginate($scanMode ? 12 : 18)
            ->withQueryString();

        $categories = Category::query()->where('is_active', true)->orderBy('name')->get();
        $cart = session('pos_cart', []);
        $totals = $this->totals($cart);
        $todayQuery = Order::query()->whereDate('created_at', now()->toDateString());
        if (Schema::hasColumn('orders', 'source')) {
            $todayQuery->where('source', 'pos');
        }

        $loyaltyService = app(LoyaltyService::class);
        $loyaltySettings = $loyaltyService->settings();
        $posCustomer = null;
        $loyaltyCard = null;
        $estimatedPoints = 0;
        if ($loyaltySettings->enabled && session('pos_shop_customer_id')) {
            $posCustomer = ShopCustomer::query()
                ->with('loyaltyCard')
                ->find((int) session('pos_shop_customer_id'));
            $loyaltyCard = $posCustomer?->loyaltyCard;
            if ($posCustomer && $loyaltyCard && $loyaltySettings->show_estimated_points_on_pos) {
                $eligible = $loyaltyService->getEligibleAmount(
                    (float) $totals['subtotal'],
                    (float) $totals['coupon_discount'],
                    (float) $totals['loyalty_discount']
                );
                $estimatedPoints = $loyaltyService->calculateEarnedPoints($eligible);
            }
        }

        $customerSearch = trim((string) $request->input('customer_q', ''));
        $customerResults = collect();
        if ($loyaltySettings->enabled && $customerSearch !== '') {
            $customerResults = ShopCustomer::query()
                ->with('loyaltyCard')
                ->where('is_active', true)
                ->where(function ($q) use ($customerSearch) {
                    $q->where('name', 'like', "%{$customerSearch}%")
                        ->orWhere('phone', 'like', "%{$customerSearch}%")
                        ->orWhere('email', 'like', "%{$customerSearch}%");
                })
                ->orderBy('name')
                ->limit(8)
                ->get();
        }

        return view('admin.pos.index', [
            'products' => $products,
            'categories' => $categories,
            'cart' => $cart,
            'totals' => $totals,
            'scanMode' => $scanMode,
            'todayCount' => (clone $todayQuery)->count(),
            'todayRevenue' => (float) (clone $todayQuery)->where('status', '!=', 'cancelled')->sum('total_amount'),
            'loyaltySettings' => $loyaltySettings,
            'posCustomer' => $posCustomer,
            'loyaltyCard' => $loyaltyCard,
            'estimatedPoints' => $estimatedPoints,
            'customerSearch' => $customerSearch,
            'customerResults' => $customerResults,
        ]);
    }

    public function scan(Request $request): View|RedirectResponse
    {
        $request->merge(['mode' => 'scan']);

        return $this->index($request);
    }

    public function add(Request $request, Product $product): RedirectResponse
    {
        $variant = null;
        if ($request->filled('variant_id') && Schema::hasTable('product_variants')) {
            $variant = ProductVariant::query()
                ->where('product_id', $product->id)
                ->whereKey((int) $request->input('variant_id'))
                ->where('is_active', true)
                ->first();
        }

        $error = $this->addProductToCart($product, 1, $variant);
        if ($error) {
            return back()->withErrors(['pos' => $error]);
        }

        $label = $variant ? $variant->displayName() : $product->name;

        return back()->with('success', $label.' added.');
    }

    public function update(Request $request, string $lineKey): RedirectResponse
    {
        $qty = max(0, (int) $request->input('qty', 1));
        $cart = session('pos_cart', []);

        if ($qty < 1) {
            unset($cart[$lineKey]);
            session(['pos_cart' => $cart]);

            return back()->with('success', 'Item removed.');
        }

        if (! isset($cart[$lineKey])) {
            return back();
        }

        $item = $cart[$lineKey];
        $product = Product::query()->find($item['product_id'] ?? 0);
        $variant = ! empty($item['product_variant_id'])
            ? ProductVariant::query()->find($item['product_variant_id'])
            : null;
        $settings = Setting::get_settings();
        $inventory = app(\App\Services\InventoryStockService::class);
        $posLocation = $inventory->resolvePosLocation($settings);
        $stock = $inventory->enabled()
            ? $inventory->posAvailableStock($product ?? new Product(['stock' => 0]), $variant, $settings)
            : app(ProductVariantService::class)->availableStock($product ?? new Product(['stock' => 0]), $variant, $posLocation);

        if (! $settings->allow_negative_stock && $stock < $qty) {
            $locName = ($settings->pos_sell_from_all_locations ?? false)
                ? 'POS locations'
                : ($posLocation?->name ?? 'POS location');

            return back()->withErrors(['pos' => 'Only '.$stock.' left at '.$locName.' for '.($item['name'] ?? 'item').'.']);
        }

        $cart[$lineKey]['qty'] = $qty;
        session(['pos_cart' => $cart]);

        return back();
    }

    public function remove(string $lineKey): RedirectResponse
    {
        $cart = session('pos_cart', []);
        unset($cart[$lineKey]);
        session(['pos_cart' => $cart]);

        return back()->with('success', 'Item removed.');
    }

    public function clear(): RedirectResponse
    {
        session()->forget(['pos_cart', 'pos_coupon', 'pos_shop_customer_id', 'pos_loyalty_redeem_points']);

        return back()->with('success', 'Sale cleared.');
    }

    public function selectCustomer(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'shop_customer_id' => ['required', 'integer', 'exists:shop_customers,id'],
        ]);

        $customer = ShopCustomer::query()->findOrFail($data['shop_customer_id']);
        if (! $customer->is_active) {
            return back()->withErrors(['pos' => 'This customer is inactive.']);
        }

        session([
            'pos_shop_customer_id' => $customer->id,
            'pos_loyalty_redeem_points' => 0,
        ]);

        return back()->with('success', 'Customer selected: '.$customer->name);
    }

    public function clearCustomer(): RedirectResponse
    {
        session()->forget(['pos_shop_customer_id', 'pos_loyalty_redeem_points']);

        return back()->with('success', 'Customer cleared.');
    }

    public function redeemLoyalty(Request $request): RedirectResponse
    {
        $loyalty = app(LoyaltyService::class);
        $settings = $loyalty->settings();

        if (! $settings->enabled || ! $settings->redemption_enabled || ! $settings->allow_redemption_at_pos) {
            return back()->withErrors(['loyalty' => 'Loyalty redemption is not available.']);
        }

        $customerId = (int) session('pos_shop_customer_id');
        $customer = ShopCustomer::query()->with('loyaltyCard')->find($customerId);
        if (! $customer || ! $customer->loyaltyCard) {
            return back()->withErrors(['loyalty' => 'Select a loyalty customer first.']);
        }

        $data = $request->validate([
            'points' => ['required', 'integer', 'min:1'],
        ]);

        $points = (int) $data['points'];
        $min = (int) $settings->redemption_points;
        if ($points < $min) {
            return back()->withErrors(['loyalty' => 'Minimum redemption is '.$min.' points.']);
        }
        if ($points % $min !== 0) {
            return back()->withErrors(['loyalty' => 'Redeem in multiples of '.$min.' points.']);
        }

        $balance = (int) $customer->loyaltyCard->points_balance;
        if ($points > $balance) {
            return back()->withErrors(['loyalty' => 'Only '.$balance.' points available.']);
        }

        $cart = session('pos_cart', []);
        $base = $this->totals($cart, ignoreLoyaltyRedeem: true);
        $value = $loyalty->getRedemptionValue($points);
        if ($value > (float) $base['total']) {
            return back()->withErrors(['loyalty' => 'Loyalty discount cannot exceed sale total.']);
        }

        session(['pos_loyalty_redeem_points' => $points]);

        return back()->with('success', 'Redeeming '.$points.' points (−KES '.number_format($value, 2).').');
    }

    public function clearLoyaltyRedeem(): RedirectResponse
    {
        session()->forget('pos_loyalty_redeem_points');

        return back()->with('success', 'Loyalty redemption cleared.');
    }

    public function applyCoupon(Request $request): RedirectResponse
    {
        $code = strtoupper(trim((string) $request->input('coupon_code', '')));
        if ($code === '') {
            return back()->withErrors(['coupon_code' => 'Enter a coupon code.']);
        }

        $coupon = Coupon::query()->whereRaw('UPPER(code) = ?', [$code])->first();
        $subtotal = $this->totals(session('pos_cart', []))['subtotal'];

        if (! $coupon || ! $coupon->is_active) {
            return back()->withErrors(['coupon_code' => 'Invalid coupon.']);
        }
        if ($coupon->starts_at && $coupon->starts_at->isFuture()) {
            return back()->withErrors(['coupon_code' => 'Coupon is not active yet.']);
        }
        if ($coupon->expires_at && $coupon->expires_at->isPast()) {
            return back()->withErrors(['coupon_code' => 'Coupon has expired.']);
        }
        if ($coupon->usage_limit !== null && $coupon->used_count >= $coupon->usage_limit) {
            return back()->withErrors(['coupon_code' => 'Coupon usage limit reached.']);
        }
        if ($subtotal < (float) $coupon->min_order_amount) {
            return back()->withErrors(['coupon_code' => 'Minimum order is KES ' . number_format((float) $coupon->min_order_amount, 2)]);
        }

        session(['pos_coupon' => $coupon->code]);

        return back()->with('success', 'Coupon applied.');
    }

    public function removeCoupon(): RedirectResponse
    {
        session()->forget('pos_coupon');

        return back()->with('success', 'Coupon removed.');
    }

    public function charge(Request $request): RedirectResponse
    {
        $settings = Setting::get_settings();
        if ($settings->require_open_shift && ! app(CashierShiftService::class)->currentOpen(auth()->id())) {
            return redirect()
                ->route('admin.shifts.index')
                ->withErrors(['shift' => 'Open a cashier shift before completing a sale.']);
        }

        $cart = session('pos_cart', []);
        if (empty($cart)) {
            return back()->withErrors(['pos' => 'Add at least one product to complete a sale.']);
        }

        $data = $request->validate([
            'customer_name' => 'nullable|string|max:255',
            'customer_phone' => 'nullable|string|max:80',
            'customer_email' => 'nullable|email|max:255',
            'payment_method' => 'required|in:cash,mobile_money,card,bank_transfer',
            'amount_tendered' => 'nullable|numeric|min:0',
            'notes' => 'nullable|string|max:500',
        ]);

        $loyalty = app(LoyaltyService::class);
        $loyaltySettings = $loyalty->settings();
        $shopCustomer = null;
        if ($loyaltySettings->enabled && session('pos_shop_customer_id')) {
            $shopCustomer = ShopCustomer::query()->with('loyaltyCard')->find((int) session('pos_shop_customer_id'));
        }

        $totals = $this->totals($cart);
        $total = $totals['total'];
        $tendered = (float) ($data['amount_tendered'] ?? 0);

        if ($data['payment_method'] === 'cash' && $tendered < $total) {
            return back()->withErrors(['amount_tendered' => 'Cash received must cover KES ' . number_format($total, 2)])->withInput();
        }

        $change = $data['payment_method'] === 'cash' ? round($tendered - $total, 2) : 0;
        $orderNumber = 'POS-' . now()->format('YmdHis') . '-' . random_int(100, 999);

        $notes = trim((string) ($data['notes'] ?? ''));
        if ($data['payment_method'] === 'cash') {
            $cashNote = 'Cash received: KES ' . number_format($tendered, 2) . '. Change: KES ' . number_format($change, 2) . '.';
            $notes = trim($notes . "\n" . $cashNote);
        }

        if ($shopCustomer) {
            $data['customer_name'] = $shopCustomer->name;
            $data['customer_phone'] = $shopCustomer->phone;
            $data['customer_email'] = $shopCustomer->email ?: ($data['customer_email'] ?? null);
        }

        $redeemPoints = (int) ($totals['loyalty_points_redeemed'] ?? 0);
        $loyaltyDiscount = (float) ($totals['loyalty_discount'] ?? 0);

        $order = DB::transaction(function () use ($data, $cart, $total, $totals, $orderNumber, $notes, $settings, $shopCustomer, $loyalty, $loyaltySettings, $redeemPoints, $loyaltyDiscount) {
            $payload = [
                'order_number' => $orderNumber,
                'user_id' => auth()->id(),
                'customer_name' => ($data['customer_name'] ?? null) ?: 'Walk-in Customer',
                'customer_email' => ($data['customer_email'] ?? null) ?: 'walkin@pos.local',
                'customer_phone' => $data['customer_phone'] ?? null,
                'shipping_address' => 'In-store sale',
                'notes' => $notes ?: null,
                'status' => 'paid',
                'payment_method' => $data['payment_method'],
                'payment_status' => 'paid',
                'total_amount' => $total,
            ];

            if (Schema::hasColumn('orders', 'cashier_shift_id')) {
                $payload['cashier_shift_id'] = app(CashierShiftService::class)->currentOpen(auth()->id())?->id;
            }
            if (Schema::hasColumn('orders', 'source')) {
                $payload['source'] = 'pos';
            }
            if (Schema::hasColumn('orders', 'discount_amount')) {
                $payload['discount_amount'] = $totals['coupon_discount'];
            }
            if (Schema::hasColumn('orders', 'tax_amount')) {
                $payload['tax_amount'] = $totals['tax'] ?? 0;
            }
            if (Schema::hasColumn('orders', 'coupon_code')) {
                $payload['coupon_code'] = $totals['coupon_code'];
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

            // Loyalty: redeem then earn (server-side authoritative)
            if ($loyaltySettings->enabled && $shopCustomer) {
                if (! $shopCustomer->loyaltyCard) {
                    // Auto-issue card when program enabled and customer selected
                    $loyalty->createLoyaltyCard($shopCustomer);
                    $shopCustomer->load('loyaltyCard');
                }

                if ($redeemPoints > 0 && $loyaltySettings->redemption_enabled && $loyaltySettings->allow_redemption_at_pos) {
                    $loyalty->redeemPoints($shopCustomer, $created, $redeemPoints, $loyaltyDiscount, auth()->id());
                }

                $eligible = $loyalty->getEligibleAmount(
                    (float) $totals['subtotal'],
                    (float) $totals['coupon_discount'],
                    $loyaltyDiscount
                );
                $earned = $loyalty->calculateEarnedPoints($eligible);
                if ($earned > 0) {
                    $loyalty->earnPoints($shopCustomer, $created, $earned, auth()->id());
                    if (Schema::hasColumn('orders', 'loyalty_points_earned')) {
                        $created->forceFill(['loyalty_points_earned' => $earned])->save();
                    }
                }
            }

            return $created->fresh();
        });

        session()->forget(['pos_cart', 'pos_coupon', 'pos_shop_customer_id', 'pos_loyalty_redeem_points']);

        Audit::log(
            'sale_created',
            'Sale '.$order->order_number.' completed ('.str_replace('_', ' ', $data['payment_method']).') for KES '.number_format((float) $order->total_amount, 2),
            $order,
            [
                'reference' => $order->order_number,
                'payment_method' => $data['payment_method'],
                'total' => (float) $order->total_amount,
                'tax' => (float) ($totals['tax'] ?? 0),
                'discount' => (float) ($totals['discount'] ?? 0),
                'items' => (int) ($totals['count'] ?? 0),
                'customer' => $order->customer_name,
            ],
            'pos'
        );

        $message = 'Sale completed.';
        if ($data['payment_method'] === 'cash') {
            $message .= ' Change due: KES ' . number_format($change, 2);
        }
        if ($loyaltySettings->enabled && $loyaltySettings->show_balance_after_sale && $shopCustomer) {
            $balance = $loyalty->getCustomerBalance($shopCustomer->fresh());
            $message .= ' Loyalty balance: '.$balance.' pts.';
        }

        $openDrawer = $settings->shouldOpenDrawerAfterCashSale($data['payment_method'], (float) $change);

        return redirect()
            ->route('admin.pos.receipt', $order)
            ->with('success', $message)
            ->with('pos_auto_print', (bool) $settings->auto_print_receipt)
            ->with('pos_open_drawer', $openDrawer);
    }

    public function receipt(Order $order): View
    {
        $isPos = str_starts_with((string) $order->order_number, 'POS-')
            || (Schema::hasColumn('orders', 'source') && $order->source === 'pos');

        abort_unless($isPos, 404);

        $order->load(['items', 'user', 'shopCustomer.loyaltyCard', 'loyaltyTransactions']);

        $loyaltySettings = app(LoyaltyService::class)->settings();

        return view('admin.pos.receipt', compact('order', 'loyaltySettings'));
    }

    private function addProductToCart(Product $product, int $qty, ?ProductVariant $variant = null): ?string
    {
        if (! $product->is_active) {
            return 'This product is not available.';
        }

        if ($product->usesVariants() && ! $variant) {
            return $product->name.' has variants. Scan a variant barcode or choose a size/colour.';
        }

        if ($variant && (! $variant->is_active || (int) $variant->product_id !== (int) $product->id)) {
            return 'Selected variant is not available.';
        }

        $allowNegative = (bool) Setting::get_settings()->allow_negative_stock;
        $stockService = app(ProductVariantService::class);
        $inventory = app(\App\Services\InventoryStockService::class);
        $settings = Setting::get_settings();
        $posLocation = $inventory->resolvePosLocation($settings);
        $available = $inventory->enabled()
            ? $inventory->posAvailableStock($product, $variant, $settings)
            : $stockService->availableStock($product, $variant, $posLocation);
        $totalElsewhere = $inventory->enabled() ? $inventory->totalQuantity($product, $variant) : $available;

        if (! $allowNegative && $available < 1) {
            $label = $variant ? $variant->displayName() : $product->name;
            $locName = ($settings->pos_sell_from_all_locations ?? false)
                ? 'POS locations'
                : ($posLocation?->name ?? 'POS location');
            $msg = "{$label} is out of stock at {$locName}.";
            if ($totalElsewhere > $available) {
                $msg .= ' Total stock across locations: '.$totalElsewhere.'.';
                if (! ($settings->pos_sell_from_all_locations ?? false)) {
                    $msg .= ' Enable “Allow POS to sell from all locations” in Settings, or transfer stock to '.$locName.'.';
                }
            }

            return $msg;
        }

        $cart = session('pos_cart', []);
        $key = $variant ? 'v'.$variant->id : 'p'.$product->id;
        $nextQty = ($cart[$key]['qty'] ?? 0) + $qty;

        if (! $allowNegative && $nextQty > $available) {
            $locName = ($settings->pos_sell_from_all_locations ?? false)
                ? 'POS locations'
                : ($posLocation?->name ?? 'POS location');

            return 'Only '.$available.' left at '.$locName.' for '.($variant ? $variant->displayName() : $product->name).'.';
        }

        $cart[$key] = [
            'product_id' => $product->id,
            'product_variant_id' => $variant?->id,
            'name' => $variant ? $variant->displayName() : $product->name,
            'sku' => $variant?->sku ?: $product->sku,
            'price' => $variant ? $variant->currentPrice() : $product->currentPrice(),
            'tax_rate' => $variant && is_numeric($variant->tax_rate)
                ? (float) $variant->tax_rate
                : (is_numeric($product->tax_rate) ? (float) $product->tax_rate : null),
            'image_url' => $variant?->image_url ?: $product->image_url,
            'qty' => $nextQty,
        ];

        session(['pos_cart' => $cart]);

        return null;
    }

    private function totals(array $cart, bool $ignoreLoyaltyRedeem = false): array
    {
        $settings = Setting::get_settings();
        $subtotal = (float) collect($cart)->sum(fn ($item) => ((float) $item['price']) * ((int) $item['qty']));
        $couponDiscount = 0.0;
        $couponCode = session('pos_coupon');

        if ($couponCode) {
            $coupon = Coupon::query()->whereRaw('UPPER(code) = ?', [strtoupper((string) $couponCode)])->first();
            $valid = $coupon
                && $coupon->is_active
                && $subtotal >= (float) $coupon->min_order_amount
                && (! $coupon->starts_at || ! $coupon->starts_at->isFuture())
                && (! $coupon->expires_at || ! $coupon->expires_at->isPast())
                && ($coupon->usage_limit === null || $coupon->used_count < $coupon->usage_limit);

            if ($valid) {
                $couponDiscount = $coupon->type === 'percent'
                    ? round($subtotal * ((float) $coupon->value / 100), 2)
                    : (float) $coupon->value;
                $couponDiscount = min($couponDiscount, $subtotal);
            } else {
                $couponCode = null;
            }
        }

        $afterCoupon = max($subtotal - $couponDiscount, 0);
        $loyaltyDiscount = 0.0;
        $loyaltyPointsRedeemed = 0;

        if (! $ignoreLoyaltyRedeem) {
            $loyalty = app(LoyaltyService::class);
            $loyaltySettings = $loyalty->settings();
            $points = (int) session('pos_loyalty_redeem_points', 0);
            if (
                $loyaltySettings->enabled
                && $loyaltySettings->redemption_enabled
                && $loyaltySettings->allow_redemption_at_pos
                && $points > 0
            ) {
                $loyaltyPointsRedeemed = $points;
                $loyaltyDiscount = min($loyalty->getRedemptionValue($points), $afterCoupon);
            }
        }

        $discount = round($couponDiscount + $loyaltyDiscount, 2);
        $afterDiscount = max($subtotal - $discount, 0);

        $tax = 0.0;
        if ($settings->tax_enabled && $afterDiscount > 0 && $subtotal > 0) {
            foreach ($cart as $item) {
                $lineGross = ((float) $item['price']) * ((int) $item['qty']);
                $lineNet = $afterDiscount * ($lineGross / $subtotal);
                $tax += $settings->taxFromAmount($lineNet, $item['tax_rate'] ?? null);
            }
            $tax = round($tax, 2);
        }

        $total = $settings->totalWithTax($afterDiscount, $tax);

        return [
            'subtotal' => $subtotal,
            'coupon_discount' => $couponDiscount,
            'loyalty_discount' => $loyaltyDiscount,
            'loyalty_points_redeemed' => $loyaltyPointsRedeemed,
            'discount' => $discount,
            'tax' => $tax,
            'tax_enabled' => (bool) $settings->tax_enabled,
            'tax_inclusive' => (bool) ($settings->tax_inclusive ?? true),
            'tax_label' => $settings->taxReceiptLabel(),
            'total' => $total,
            'coupon_code' => $couponCode,
            'count' => (int) collect($cart)->sum('qty'),
        ];
    }
}
