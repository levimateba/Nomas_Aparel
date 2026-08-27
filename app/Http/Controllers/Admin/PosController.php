<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Coupon;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Services\CashierShiftService;
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
        $query = trim((string) $request->input('q', ''));

        if ($query !== '') {
            $scanned = Product::findActiveByScan($query);

            if ($scanned) {
                $error = $this->addProductToCart($scanned, 1);
                if ($error) {
                    return redirect()
                        ->route('admin.pos.index', $request->only('category_id'))
                        ->withErrors(['pos' => $error]);
                }

                return redirect()
                    ->route('admin.pos.index', $request->only('category_id'))
                    ->with('success', $scanned->name . ' added.');
            }
        }

        $products = Product::query()
            ->with('category')
            ->where('is_active', true)
            ->when($request->filled('category_id'), fn ($builder) => $builder->where('category_id', $request->integer('category_id')))
            ->when($query !== '', function ($builder) use ($query) {
                $builder->where(function ($search) use ($query) {
                    $search->where('name', 'like', '%' . $query . '%')
                        ->orWhere('sku', 'like', '%' . $query . '%');
                    if (Schema::hasColumn('products', 'barcode')) {
                        $search->orWhere('barcode', 'like', '%' . $query . '%');
                    }
                });
            })
            ->orderBy('name')
            ->paginate(18)
            ->withQueryString();

        $categories = Category::query()->where('is_active', true)->orderBy('name')->get();
        $cart = session('pos_cart', []);
        $totals = $this->totals($cart);
        $todayQuery = Order::query()->whereDate('created_at', now()->toDateString());
        if (Schema::hasColumn('orders', 'source')) {
            $todayQuery->where('source', 'pos');
        }

        return view('admin.pos.index', [
            'products' => $products,
            'categories' => $categories,
            'cart' => $cart,
            'totals' => $totals,
            'todayCount' => (clone $todayQuery)->count(),
            'todayRevenue' => (float) (clone $todayQuery)->where('status', '!=', 'cancelled')->sum('total_amount'),
        ]);
    }

    public function add(Product $product): RedirectResponse
    {
        $error = $this->addProductToCart($product, 1);
        if ($error) {
            return back()->withErrors(['pos' => $error]);
        }

        return back()->with('success', $product->name . ' added.');
    }

    public function update(Request $request, Product $product): RedirectResponse
    {
        $qty = max(0, (int) $request->input('qty', 1));
        $cart = session('pos_cart', []);
        $key = (string) $product->id;

        if ($qty < 1) {
            unset($cart[$key]);
            session(['pos_cart' => $cart]);

            return back()->with('success', 'Item removed.');
        }

        if ($product->stock < $qty) {
            return back()->withErrors(['pos' => 'Only ' . $product->stock . ' left in stock for ' . $product->name . '.']);
        }

        if (isset($cart[$key])) {
            $cart[$key]['qty'] = $qty;
            session(['pos_cart' => $cart]);
        }

        return back();
    }

    public function remove(Product $product): RedirectResponse
    {
        $cart = session('pos_cart', []);
        unset($cart[(string) $product->id]);
        session(['pos_cart' => $cart]);

        return back()->with('success', 'Item removed.');
    }

    public function clear(): RedirectResponse
    {
        session()->forget(['pos_cart', 'pos_coupon']);

        return back()->with('success', 'Sale cleared.');
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

        $order = DB::transaction(function () use ($data, $cart, $total, $totals, $orderNumber, $notes) {
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
                $payload['discount_amount'] = $totals['discount'];
            }
            if (Schema::hasColumn('orders', 'coupon_code')) {
                $payload['coupon_code'] = $totals['coupon_code'];
            }

            $created = Order::create($payload);

            foreach ($cart as $item) {
                $product = Product::query()->lockForUpdate()->find($item['product_id']);
                if (! $product || ! $product->is_active) {
                    throw ValidationException::withMessages(['pos' => 'One or more products are no longer available.']);
                }
                if ($product->stock < $item['qty']) {
                    throw ValidationException::withMessages(['pos' => 'Insufficient stock for ' . $product->name . '.']);
                }

                $product->decrement('stock', $item['qty']);

                OrderItem::create([
                    'order_id' => $created->id,
                    'product_id' => $product->id,
                    'product_name' => $item['name'],
                    'unit_price' => $item['price'],
                    'quantity' => $item['qty'],
                    'line_total' => $item['price'] * $item['qty'],
                ]);
            }

            if (! empty($totals['coupon_code'])) {
                Coupon::query()->whereRaw('UPPER(code) = ?', [strtoupper($totals['coupon_code'])])->increment('used_count');
            }

            return $created;
        });

        session()->forget(['pos_cart', 'pos_coupon']);

        $message = 'Sale completed.';
        if ($data['payment_method'] === 'cash') {
            $message .= ' Change due: KES ' . number_format($change, 2);
        }

        return redirect()
            ->route('admin.pos.receipt', $order)
            ->with('success', $message);
    }

    public function receipt(Order $order): View
    {
        $isPos = str_starts_with((string) $order->order_number, 'POS-')
            || (Schema::hasColumn('orders', 'source') && $order->source === 'pos');

        abort_unless($isPos, 404);

        $order->load(['items', 'user']);

        return view('admin.pos.receipt', compact('order'));
    }

    private function addProductToCart(Product $product, int $qty): ?string
    {
        if (! $product->is_active) {
            return 'This product is not available.';
        }
        if ($product->stock < 1) {
            return $product->name . ' is out of stock.';
        }

        $cart = session('pos_cart', []);
        $key = (string) $product->id;
        $nextQty = ($cart[$key]['qty'] ?? 0) + $qty;

        if ($nextQty > $product->stock) {
            return 'Only ' . $product->stock . ' left in stock for ' . $product->name . '.';
        }

        $cart[$key] = [
            'product_id' => $product->id,
            'name' => $product->name,
            'sku' => $product->sku,
            'price' => $product->currentPrice(),
            'image_url' => $product->image_url,
            'qty' => $nextQty,
        ];

        session(['pos_cart' => $cart]);

        return null;
    }

    private function totals(array $cart): array
    {
        $subtotal = (float) collect($cart)->sum(fn ($item) => ((float) $item['price']) * ((int) $item['qty']));
        $discount = 0.0;
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
                $discount = $coupon->type === 'percent'
                    ? round($subtotal * ((float) $coupon->value / 100), 2)
                    : (float) $coupon->value;
                $discount = min($discount, $subtotal);
            } else {
                $couponCode = null;
            }
        }

        return [
            'subtotal' => $subtotal,
            'discount' => $discount,
            'total' => max($subtotal - $discount, 0),
            'coupon_code' => $couponCode,
            'count' => (int) collect($cart)->sum('qty'),
        ];
    }
}
