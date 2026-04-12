@extends('layouts.storefront')

@section('title', 'Checkout')

@section('content')
<div class="container" style="margin-top:16px;">
    <div style="display:grid;grid-template-columns:1.2fr .8fr;gap:16px;">
        <div class="card" style="padding:16px;">
            <h2 style="margin-top:0;">Checkout</h2>
            <form method="POST" action="{{ route('checkout.place') }}">
                @csrf
                <label>Full Name</label>
                <input type="text" name="customer_name" value="{{ old('customer_name') }}" required style="width:100%;padding:10px;border:1px solid #d1d5db;border-radius:8px;margin-bottom:10px;">

                <label>Email</label>
                <input type="email" name="customer_email" value="{{ old('customer_email') }}" required style="width:100%;padding:10px;border:1px solid #d1d5db;border-radius:8px;margin-bottom:10px;">

                <label>Phone</label>
                <input type="text" name="customer_phone" value="{{ old('customer_phone') }}" style="width:100%;padding:10px;border:1px solid #d1d5db;border-radius:8px;margin-bottom:10px;">

                <label>Shipping Address</label>
                <textarea name="shipping_address" required rows="4" style="width:100%;padding:10px;border:1px solid #d1d5db;border-radius:8px;margin-bottom:10px;">{{ old('shipping_address') }}</textarea>

                <label>Order Notes (optional)</label>
                <textarea name="notes" rows="3" style="width:100%;padding:10px;border:1px solid #d1d5db;border-radius:8px;margin-bottom:14px;">{{ old('notes') }}</textarea>

                <label>Payment Method</label>
                <select name="payment_method" required style="width:100%;padding:10px;border:1px solid #d1d5db;border-radius:8px;margin-bottom:14px;">
                    <option value="cash_on_delivery" @selected(old('payment_method') === 'cash_on_delivery')>Cash on Delivery</option>
                    <option value="mobile_money" @selected(old('payment_method') === 'mobile_money')>Mobile Money</option>
                    <option value="bank_transfer" @selected(old('payment_method') === 'bank_transfer')>Bank Transfer</option>
                    <option value="card" @selected(old('payment_method') === 'card')>Card</option>
                </select>
                <div class="card" style="padding:10px;margin-bottom:12px;background:#fafafa;">
                    <strong>Payment Instructions</strong>
                    <ul style="margin:8px 0 0 18px;padding:0;color:#555;">
                        <li><strong>Cash on Delivery:</strong> pay when your package arrives.</li>
                        <li><strong>Mobile Money:</strong> you will receive a payment prompt after placing order.</li>
                        <li><strong>Bank Transfer:</strong> bank details are sent in order confirmation email.</li>
                        <li><strong>Card:</strong> card processing to be enabled in the next payment integration stage.</li>
                    </ul>
                </div>

                <button class="btn btn-primary" type="submit">Place Order</button>
            </form>
        </div>

        <div class="card" style="padding:16px;">
            <h3 style="margin-top:0;">Order Summary</h3>
            @foreach($cart as $item)
                <div style="display:flex;justify-content:space-between;padding:8px 0;border-bottom:1px solid #e5e7eb;">
                    <span>{{ $item['name'] }} x{{ $item['qty'] }}</span>
                    <span>KES {{ number_format((float) ($item['price'] * $item['qty']), 2) }}</span>
                </div>
            @endforeach
            <div style="display:flex;justify-content:space-between;padding-top:8px;">
                <span>Subtotal</span>
                <span>KES {{ number_format((float) $totals['subtotal'], 2) }}</span>
            </div>
            <div style="display:flex;justify-content:space-between;">
                <span>Discount</span>
                <span>- KES {{ number_format((float) $totals['discount'], 2) }}</span>
            </div>
            @if(!empty($totals['coupon_code']))
                <div style="color:#6b7280;font-size:13px;">Coupon: {{ $totals['coupon_code'] }}</div>
            @endif
            <div style="display:flex;justify-content:space-between;margin-top:10px;font-weight:700;">
                <span>Total</span>
                <span>KES {{ number_format((float) $totals['total'], 2) }}</span>
            </div>
        </div>
    </div>
</div>
@endsection
