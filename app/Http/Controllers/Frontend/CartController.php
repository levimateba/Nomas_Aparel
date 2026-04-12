<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Mail\OrderConfirmationMail;
use App\Models\Coupon;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\AbandonedCart;
use App\Services\Payments\PaymentGatewayService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Schema;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CartController extends Controller
{
    private function calculateTotals(array $cart): array
    {
        $subtotal = (float) collect($cart)->sum(fn ($item) => $item['price'] * $item['qty']);
        $couponData = session('coupon');
        $discount = 0.0;
        $couponCode = null;

        if (is_array($couponData) && !empty($couponData['code'])) {
            $coupon = Coupon::query()->where('code', $couponData['code'])->first();
            if ($coupon && $coupon->is_active) {
                if ($coupon->min_order_amount <= $subtotal) {
                    if ($coupon->type === 'percent') {
                        $discount = round($subtotal * ((float) $coupon->value / 100), 2);
                    } else {
                        $discount = (float) $coupon->value;
                    }
                    $discount = min($discount, $subtotal);
                    $couponCode = $coupon->code;
                }
            }
        }

        return [
            'subtotal' => $subtotal,
            'discount' => $discount,
            'total' => max($subtotal - $discount, 0),
            'coupon_code' => $couponCode,
        ];
    }

    public function index(): View
    {
        $cart = session('cart', []);
        $totals = $this->calculateTotals($cart);

        return view('frontend.cart.index', compact('cart', 'totals'));
    }

    public function add(Request $request, Product $product): RedirectResponse
    {
        $qty = max(1, (int) $request->input('qty', 1));
        $price = (float) ($product->sale_price ?: $product->price);

        $cart = session('cart', []);
        $key = (string) $product->id;

        if (isset($cart[$key])) {
            $cart[$key]['qty'] += $qty;
        } else {
            $cart[$key] = [
                'product_id' => $product->id,
                'name' => $product->name,
                'price' => $price,
                'image_url' => $product->image_url,
                'qty' => $qty,
            ];
        }

        session(['cart' => $cart]);
        $this->syncAbandonedCart($cart);

        return back()->with('success', 'Product added to cart.');
    }

    public function update(Request $request, Product $product): RedirectResponse
    {
        $qty = max(1, (int) $request->input('qty', 1));
        $cart = session('cart', []);
        $key = (string) $product->id;

        if (isset($cart[$key])) {
            $cart[$key]['qty'] = $qty;
            session(['cart' => $cart]);
            $this->syncAbandonedCart($cart);
        }

        return back()->with('success', 'Cart updated.');
    }

    public function remove(Product $product): RedirectResponse
    {
        $cart = session('cart', []);
        unset($cart[(string) $product->id]);
        session(['cart' => $cart]);
        $this->syncAbandonedCart($cart);

        return back()->with('success', 'Item removed.');
    }

    public function clear(): RedirectResponse
    {
        session()->forget('cart');
        $this->syncAbandonedCart([]);

        return back()->with('success', 'Cart cleared.');
    }

    public function checkout(): View|RedirectResponse
    {
        $cart = session('cart', []);
        if (empty($cart)) {
            return redirect()->route('shop.index')->with('success', 'Your cart is empty.');
        }

        $totals = $this->calculateTotals($cart);

        return view('frontend.checkout.index', compact('cart', 'totals'));
    }

    public function placeOrder(Request $request): RedirectResponse
    {
        $cart = session('cart', []);
        if (empty($cart)) {
            return redirect()->route('shop.index')->with('success', 'Your cart is empty.');
        }

        $data = $request->validate([
            'customer_name' => 'required|string|max:255',
            'customer_email' => 'required|email|max:255',
            'customer_phone' => 'nullable|string|max:80',
            'shipping_address' => 'required|string',
            'notes' => 'nullable|string',
            'payment_method' => 'required|in:cash_on_delivery,bank_transfer,mobile_money,card',
        ]);

        $totals = $this->calculateTotals($cart);
        $total = $totals['total'];
        $orderNumber = 'ORD-' . now()->format('YmdHis') . '-' . random_int(100, 999);

        $order = DB::transaction(function () use ($data, $cart, $total, $orderNumber, $totals) {
            $createdOrder = Order::create([
                'order_number' => $orderNumber,
                'user_id' => auth()->id(),
                'customer_name' => $data['customer_name'],
                'customer_email' => $data['customer_email'],
                'customer_phone' => $data['customer_phone'] ?? null,
                'shipping_address' => $data['shipping_address'],
                'notes' => $data['notes'] ?? null,
                'status' => 'pending',
                'payment_method' => $data['payment_method'],
                'payment_status' => 'pending',
                'total_amount' => $total,
            ]);

            $payment = app(PaymentGatewayService::class)->initialize($createdOrder);
            $createdOrder->update([
                'payment_status' => $payment['status'] ?? 'pending',
                'payment_reference' => $payment['reference'] ?? null,
            ]);

            foreach ($cart as $item) {
                $product = Product::query()->lockForUpdate()->find($item['product_id']);
                if (! $product || ! $product->is_active) {
                    abort(422, 'One or more products are no longer available.');
                }

                if ($product->stock < $item['qty']) {
                    abort(422, 'Insufficient stock for ' . $product->name . '.');
                }

                $product->decrement('stock', $item['qty']);

                OrderItem::create([
                    'order_id' => $createdOrder->id,
                    'product_id' => $item['product_id'],
                    'product_name' => $item['name'],
                    'unit_price' => $item['price'],
                    'quantity' => $item['qty'],
                    'line_total' => $item['price'] * $item['qty'],
                ]);
            }

            if (!empty($totals['coupon_code'])) {
                Coupon::query()->where('code', $totals['coupon_code'])->increment('used_count');
            }

            return $createdOrder;
        });

        try {
            $order->load('items');
            Mail::to($order->customer_email)->send(new OrderConfirmationMail($order));
            $adminEmail = env('MAIL_FROM_ADDRESS');
            if ($adminEmail) {
                Mail::html(
                    'New order received: <strong>' . e($order->order_number) . '</strong> from ' . e($order->customer_name) . '.',
                    fn ($message) => $message->to($adminEmail)->subject('New Order ' . $order->order_number)
                );
            }
        } catch (\Throwable $exception) {
            // Do not fail order placement if mail delivery fails.
        }

        session()->forget('cart');
        session()->forget('coupon');
        $this->syncAbandonedCart([]);

        return redirect()->route('checkout.success', ['order' => $orderNumber]);
    }

    public function success(string $order): View
    {
        return view('frontend.checkout.success', ['orderNumber' => $order]);
    }

    public function applyCoupon(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'coupon_code' => 'required|string|max:100',
        ]);

        $coupon = Coupon::query()->where('code', strtoupper(trim($data['coupon_code'])))->first();
        if (! $coupon || ! $coupon->is_active) {
            return back()->withErrors(['coupon_code' => 'Invalid coupon code.']);
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
        $subtotal = (float) collect(session('cart', []))->sum(fn ($item) => $item['price'] * $item['qty']);
        if ($subtotal < (float) $coupon->min_order_amount) {
            return back()->withErrors(['coupon_code' => 'Coupon requires minimum order of KES ' . number_format((float) $coupon->min_order_amount, 2)]);
        }

        session(['coupon' => ['code' => $coupon->code]]);

        return back()->with('success', 'Coupon applied.');
    }

    public function removeCoupon(): RedirectResponse
    {
        session()->forget('coupon');

        return back()->with('success', 'Coupon removed.');
    }

    private function syncAbandonedCart(array $cart): void
    {
        if (! Schema::hasTable('abandoned_carts')) {
            return;
        }

        $userId = auth()->id();
        $email = auth()->user()->email ?? null;

        if (empty($cart)) {
            if ($userId || $email) {
                AbandonedCart::query()
                    ->when($userId, fn ($q) => $q->where('user_id', $userId))
                    ->when(!$userId && $email, fn ($q) => $q->where('email', $email))
                    ->delete();
            }
            return;
        }

        $total = (float) collect($cart)->sum(fn ($item) => $item['price'] * $item['qty']);

        if ($userId) {
            AbandonedCart::updateOrCreate(
                ['user_id' => $userId],
                [
                    'email' => $email,
                    'cart_payload' => $cart,
                    'total_amount' => $total,
                    'last_activity_at' => now(),
                ]
            );
            return;
        }

        if ($email) {
            AbandonedCart::updateOrCreate(
                ['email' => $email],
                [
                    'user_id' => null,
                    'cart_payload' => $cart,
                    'total_amount' => $total,
                    'last_activity_at' => now(),
                ]
            );
        }
    }
}
