@extends('layouts.storefront')

@section('title', 'Checkout')

@php
    $itemCount = (int) collect($cart)->sum('qty');
    $selectedPayment = old('payment_method', 'cash_on_delivery');
@endphp

@push('styles')
<style>
    .co-page { margin: 16px 0 28px; }
    .co-head { margin-bottom: 18px; }
    .co-head h1 { margin: 0 0 6px; font-size: 1.65rem; font-weight: 800; color: #121212; }
    .co-head p { margin: 0; color: #6b7280; font-size: 14px; }

    .co-grid {
        display: grid;
        grid-template-columns: minmax(0, 1.15fr) minmax(0, .85fr);
        gap: 18px;
        align-items: start;
    }

    .co-card {
        background: #fff;
        border: 1px solid #ececec;
        border-radius: 14px;
        box-shadow: 0 2px 10px rgba(16, 24, 40, 0.04);
        padding: 18px;
    }

    .co-section-title {
        display: flex;
        align-items: center;
        gap: 8px;
        margin: 0 0 4px;
        font-size: 16px;
        font-weight: 800;
        color: #121212;
    }
    .co-section-sub { margin: 0 0 14px; color: #6b7280; font-size: 13px; }
    .co-icon {
        width: 28px; height: 28px; border-radius: 8px;
        display: inline-flex; align-items: center; justify-content: center;
        background: #fffbeb; color: #b8942d; font-size: 14px; flex: 0 0 28px;
    }

    .co-field { margin-bottom: 12px; }
    .co-field label {
        display: block; margin-bottom: 6px; font-size: 13px; font-weight: 700; color: #374151;
    }
    .co-field label .req { color: #dc2626; }
    .co-field input,
    .co-field textarea,
    .co-field select {
        width: 100%;
        padding: 12px 14px;
        border: 1px solid #d1d5db;
        border-radius: 10px;
        font: inherit;
        background: #fff;
        color: #121212;
    }
    .co-field input:focus,
    .co-field textarea:focus { outline: 2px solid rgba(212, 175, 55, 0.35); border-color: #d4af37; }

    .co-safe {
        margin-top: 14px;
        padding: 12px 14px;
        border-radius: 10px;
        background: #f9fafb;
        border: 1px solid #ececec;
        display: flex; gap: 10px; align-items: flex-start;
        font-size: 13px; color: #4b5563;
    }
    .co-safe strong { display: block; color: #121212; margin-bottom: 2px; }

    .co-summary-item {
        display: grid;
        grid-template-columns: 56px 1fr auto;
        gap: 10px;
        align-items: center;
        padding: 10px 0;
        border-bottom: 1px solid #f3f4f6;
    }
    .co-summary-item:last-of-type { border-bottom: none; }
    .co-thumb {
        width: 56px; height: 56px; border-radius: 10px;
        background: #f3f4f6 center/cover no-repeat;
        border: 1px solid #ececec;
    }
    .co-item-name { font-size: 13px; font-weight: 700; color: #121212; line-height: 1.35; }
    .co-item-meta { font-size: 12px; color: #6b7280; margin-top: 2px; }
    .co-item-price { font-size: 13px; font-weight: 800; color: #121212; white-space: nowrap; }

    .co-lines { margin-top: 8px; padding-top: 10px; border-top: 1px dashed #e5e7eb; }
    .co-line {
        display: flex; justify-content: space-between; gap: 12px;
        padding: 5px 0; font-size: 14px; color: #374151;
    }
    .co-line.is-free { color: #059669; font-weight: 700; }
    .co-total-bar {
        margin-top: 12px;
        padding: 12px 14px;
        border-radius: 10px;
        background: #fffbeb;
        border: 1px solid #f3e8c2;
        display: flex; justify-content: space-between; align-items: center;
        font-weight: 800; font-size: 15px;
    }
    .co-total-bar span:last-child { font-size: 1.15rem; color: #121212; }

    .co-trust-grid {
        display: grid; grid-template-columns: repeat(3, minmax(0, 1fr)); gap: 8px;
        margin-top: 14px; padding-top: 14px; border-top: 1px solid #f3f4f6;
    }
    .co-trust-item { text-align: center; font-size: 11px; color: #6b7280; }
    .co-trust-item strong { display: block; font-size: 12px; color: #121212; margin-top: 4px; }
    .co-trust-ico { font-size: 18px; line-height: 1; }

    .co-pay-options { display: grid; gap: 10px; margin-bottom: 14px; }
    .co-pay-option {
        position: relative;
        display: flex; align-items: flex-start; gap: 12px;
        padding: 12px 14px;
        border: 1.5px solid #e5e7eb;
        border-radius: 12px;
        cursor: pointer;
        background: #fff;
        transition: border-color .15s, box-shadow .15s, background .15s;
    }
    .co-pay-option input {
        position: absolute; opacity: 0; pointer-events: none;
    }
    .co-pay-option.is-active {
        border-color: #d4af37;
        background: #fffdf5;
        box-shadow: 0 0 0 1px rgba(212, 175, 55, 0.15);
    }
    .co-pay-radio {
        width: 18px; height: 18px; border-radius: 50%;
        border: 2px solid #d1d5db; margin-top: 2px; flex: 0 0 18px;
        display: grid; place-items: center;
    }
    .co-pay-option.is-active .co-pay-radio {
        border-color: #d4af37;
    }
    .co-pay-option.is-active .co-pay-radio::after {
        content: "";
        width: 8px; height: 8px; border-radius: 50%; background: #d4af37;
    }
    .co-pay-copy strong { display: block; font-size: 14px; color: #121212; }
    .co-pay-copy small { display: block; margin-top: 2px; font-size: 12px; color: #6b7280; line-height: 1.35; }
    .co-pay-ico {
        width: 34px; height: 34px; border-radius: 10px;
        display: inline-flex; align-items: center; justify-content: center;
        background: #fffbeb; font-size: 16px; flex: 0 0 34px;
    }

    .co-instructions { display: grid; gap: 10px; margin-bottom: 14px; }
    .co-instruction {
        display: flex; gap: 12px; align-items: flex-start;
        padding: 12px 14px; border-radius: 12px;
        background: #fafafa; border: 1px solid #f0f0f0;
        font-size: 13px; color: #4b5563;
    }
    .co-instruction strong { display: block; color: #121212; margin-bottom: 2px; font-size: 13px; }
    .co-instruction[hidden] { display: none !important; }

    .co-alert {
        padding: 12px 14px;
        border-radius: 10px;
        background: #fffbeb;
        border: 1px solid #f3e8c2;
        font-size: 13px;
        color: #5c4a12;
        display: flex; gap: 10px; align-items: flex-start;
        margin-bottom: 14px;
    }

    .co-submit {
        width: 100%;
        border: 0;
        border-radius: 12px;
        padding: 14px 16px;
        font-size: 15px;
        font-weight: 800;
        cursor: pointer;
        background: linear-gradient(135deg, #d4af37, #b8942d);
        color: #121212;
        display: inline-flex; align-items: center; justify-content: center; gap: 8px;
        box-shadow: 0 8px 18px rgba(212, 175, 55, 0.28);
    }
    .co-submit:hover { opacity: 0.95; }

    .co-legal {
        margin-top: 10px;
        text-align: center;
        font-size: 11px;
        color: #9ca3af;
        line-height: 1.45;
    }
    .co-secure-note {
        margin-top: 10px;
        text-align: center;
        font-size: 12px;
        color: #6b7280;
        display: flex; align-items: center; justify-content: center; gap: 6px;
    }

    .co-footer-trust {
        margin-top: 18px;
        display: grid;
        grid-template-columns: repeat(4, minmax(0, 1fr));
        gap: 12px;
    }
    .co-footer-trust .co-card {
        padding: 14px;
        text-align: center;
        font-size: 12px;
        color: #6b7280;
    }
    .co-footer-trust strong { display: block; color: #121212; font-size: 13px; margin: 6px 0 2px; }

    @media (max-width: 900px) {
        .co-grid { grid-template-columns: 1fr; }
        .co-footer-trust { grid-template-columns: repeat(2, minmax(0, 1fr)); }
        .co-trust-grid { grid-template-columns: 1fr; }
    }
    @media (max-width: 520px) {
        .co-footer-trust { grid-template-columns: 1fr; }
        .co-head h1 { font-size: 1.35rem; }
    }
</style>
@endpush

@section('content')
<div class="container co-page">
    <div class="co-head">
        <h1>Checkout</h1>
        <p>Fill in your details to complete your order.</p>
    </div>

    <form method="POST" action="{{ route('checkout.place') }}" id="checkout-form">
        @csrf

        <div class="co-grid">
            <div class="co-main">
                <div class="co-card" style="margin-bottom:14px;">
                    <h2 class="co-section-title"><span class="co-icon">👤</span> Customer details</h2>
                    <p class="co-section-sub">Tell us where to send your order.</p>

                    <div class="co-field">
                        <label for="customer_name">Full Name <span class="req">*</span></label>
                        <input id="customer_name" type="text" name="customer_name" value="{{ old('customer_name', auth()->user()->name) }}" placeholder="Enter your full name" required>
                    </div>
                    <div class="co-field">
                        <label for="customer_email">Email <span class="req">*</span></label>
                        <input id="customer_email" type="email" name="customer_email" value="{{ old('customer_email', auth()->user()->email) }}" placeholder="Enter your email address" required>
                    </div>
                    <div class="co-field">
                        <label for="customer_phone">Phone</label>
                        <input id="customer_phone" type="text" name="customer_phone" value="{{ old('customer_phone') }}" placeholder="Enter your phone number">
                    </div>
                    <div class="co-field">
                        <label for="shipping_address">Shipping Address <span class="req">*</span></label>
                        <textarea id="shipping_address" name="shipping_address" rows="4" placeholder="Enter your shipping address" required>{{ old('shipping_address') }}</textarea>
                    </div>
                    <div class="co-field">
                        <label for="notes">Order Notes (optional)</label>
                        <textarea id="notes" name="notes" rows="3" placeholder="Any special instructions for delivery">{{ old('notes') }}</textarea>
                    </div>

                    <div class="co-safe">
                        <span class="co-icon" style="margin:0;">🛡</span>
                        <div>
                            <strong>Your data is safe with us</strong>
                            We use secure encryption to protect your personal information.
                        </div>
                    </div>
                </div>

                <div class="co-card">
                    <h2 class="co-section-title"><span class="co-icon">💳</span> Payment Method</h2>
                    <p class="co-section-sub">Choose your preferred payment option.</p>

                    <div class="co-pay-options">
                        @foreach([
                            'cash_on_delivery' => ['icon' => '💵', 'title' => 'Cash on Delivery', 'desc' => 'Pay when your package arrives.'],
                            'mobile_money' => ['icon' => '📱', 'title' => 'Mobile Money', 'desc' => 'You will receive a payment prompt after placing order.'],
                            'bank_transfer' => ['icon' => '🏦', 'title' => 'Bank Transfer', 'desc' => 'Bank details are sent in your order confirmation email.'],
                            'card' => ['icon' => '💳', 'title' => 'Card', 'desc' => 'Card processing to be enabled in the next payment integration stage.'],
                        ] as $value => $option)
                            <label class="co-pay-option {{ $selectedPayment === $value ? 'is-active' : '' }}" data-payment-option="{{ $value }}">
                                <input type="radio" name="payment_method" value="{{ $value }}" @checked($selectedPayment === $value) required>
                                <span class="co-pay-radio" aria-hidden="true"></span>
                                <span class="co-pay-ico">{{ $option['icon'] }}</span>
                                <span class="co-pay-copy">
                                    <strong>{{ $option['title'] }}</strong>
                                    <small>{{ $option['desc'] }}</small>
                                </span>
                            </label>
                        @endforeach
                    </div>

                    <h3 class="co-section-title" style="font-size:15px;margin-top:4px;"><span class="co-icon">ℹ</span> Payment Instructions</h3>

                    <div class="co-instructions">
                        <div class="co-instruction" data-payment-help="cash_on_delivery" @if($selectedPayment !== 'cash_on_delivery') hidden @endif>
                            <span class="co-pay-ico">🚚</span>
                            <div><strong>Cash on Delivery</strong> Pay when your package arrives at your doorstep.</div>
                        </div>
                        <div class="co-instruction" data-payment-help="mobile_money" @if($selectedPayment !== 'mobile_money') hidden @endif>
                            <span class="co-pay-ico">📱</span>
                            <div><strong>Mobile Money</strong> You will receive a payment prompt after placing your order.</div>
                        </div>
                        <div class="co-instruction" data-payment-help="bank_transfer" @if($selectedPayment !== 'bank_transfer') hidden @endif>
                            <span class="co-pay-ico">🏦</span>
                            <div><strong>Bank Transfer</strong> Make payment to our bank account using details in your confirmation email.</div>
                        </div>
                        <div class="co-instruction" data-payment-help="card" @if($selectedPayment !== 'card') hidden @endif>
                            <span class="co-pay-ico">💳</span>
                            <div><strong>Card</strong> Card processing will be enabled in the next payment integration stage.</div>
                        </div>
                    </div>

                    <div class="co-alert">
                        <span>🛡</span>
                        <div><strong>Important:</strong> Please ensure your contact details are correct so we can reach you regarding your order.</div>
                    </div>

                    <div class="co-secure-note">🔒 100% Secure &amp; Safe Checkout</div>
                </div>
            </div>

            <aside class="co-summary co-card">
                <h2 class="co-section-title"><span class="co-icon">🛍</span> Order Summary</h2>
                <p class="co-section-sub">{{ $itemCount }} {{ \Illuminate\Support\Str::plural('item', $itemCount) }} in your cart</p>

                @foreach($cart as $item)
                    <div class="co-summary-item">
                        <div class="co-thumb" style="background-image:url('{{ $item['image_url'] ?: 'https://via.placeholder.com/160x160?text=Item' }}');"></div>
                        <div>
                            <div class="co-item-name">{{ $item['name'] }}</div>
                            <div class="co-item-meta">Qty: {{ $item['qty'] }}</div>
                        </div>
                        <div class="co-item-price">KES {{ number_format((float) ($item['price'] * $item['qty']), 2) }}</div>
                    </div>
                @endforeach

                <div class="co-lines">
                    <div class="co-line">
                        <span>Subtotal ({{ $itemCount }} {{ \Illuminate\Support\Str::plural('item', $itemCount) }})</span>
                        <span>KES {{ number_format((float) $totals['subtotal'], 2) }}</span>
                    </div>
                    <div class="co-line">
                        <span>Discount</span>
                        <span>- KES {{ number_format((float) $totals['discount'], 2) }}</span>
                    </div>
                    @if(!empty($totals['coupon_code']))
                        <div class="co-line" style="font-size:12px;color:#6b7280;">
                            <span>Coupon</span>
                            <span>{{ $totals['coupon_code'] }}</span>
                        </div>
                    @endif
                    <div class="co-line is-free">
                        <span>Delivery</span>
                        <span>FREE</span>
                    </div>
                </div>

                <div class="co-total-bar">
                    <span>Total</span>
                    <span>KES {{ number_format((float) $totals['total'], 2) }}</span>
                </div>

                <div class="co-trust-grid">
                    <div class="co-trust-item">
                        <div class="co-trust-ico">🔒</div>
                        <strong>Secure Checkout</strong>
                        Protected data
                    </div>
                    <div class="co-trust-item">
                        <div class="co-trust-ico">↩</div>
                        <strong>Easy Returns</strong>
                        7-day policy
                    </div>
                    <div class="co-trust-item">
                        <div class="co-trust-ico">🎧</div>
                        <strong>Need Help?</strong>
                        Contact support
                    </div>
                </div>

                <button class="co-submit" type="submit" style="margin-top:14px;">
                    <span>🔒</span> Place Order
                </button>
                <div class="co-secure-note">Secure checkout. Your data is protected.</div>
                <div class="co-legal">By placing this order, you agree to our Terms &amp; Conditions and Privacy Policy.</div>
            </aside>
        </div>
    </form>

    <div class="co-footer-trust">
        <div class="co-card"><div style="font-size:20px;">🚚</div><strong>Free Delivery</strong>On selected orders</div>
        <div class="co-card"><div style="font-size:20px;">🔒</div><strong>Secure Payment</strong>100% secure transactions</div>
        <div class="co-card"><div style="font-size:20px;">↩</div><strong>Easy Returns</strong>7 days return policy</div>
        <div class="co-card"><div style="font-size:20px;">🎧</div><strong>Support</strong>24/7 customer support</div>
    </div>
</div>

@push('scripts')
<script>
    (function () {
        const options = document.querySelectorAll('[data-payment-option]');
        const helpBlocks = document.querySelectorAll('[data-payment-help]');

        function setPayment(value) {
            options.forEach(function (option) {
                const active = option.dataset.paymentOption === value;
                option.classList.toggle('is-active', active);
                const input = option.querySelector('input[type="radio"]');
                if (input) input.checked = active;
            });
            helpBlocks.forEach(function (block) {
                block.hidden = block.dataset.paymentHelp !== value;
            });
        }

        options.forEach(function (option) {
            option.addEventListener('click', function () {
                setPayment(option.dataset.paymentOption);
            });
        });

        document.querySelectorAll('input[name="payment_method"]').forEach(function (input) {
            input.addEventListener('change', function () {
                if (input.checked) setPayment(input.value);
            });
        });
    })();
</script>
@endpush
@endsection
