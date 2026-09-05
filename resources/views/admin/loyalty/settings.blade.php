@extends('layouts.admin')
@section('title', 'Customer Loyalty Settings')
@section('heading', 'Customer Loyalty')
@section('subheading', 'Configure earning, redemption, POS, and refund rules')

@section('content')
<div class="ta-page">
    <x-admin.page-header
        title="Customer Loyalty Settings"
        subtitle="Points program for International Nomas Apparel POS"
    >
        <x-slot:actions>
            <a href="{{ route('admin.loyalty.dashboard') }}" class="ta-btn-ghost">Dashboard</a>
            <a href="{{ route('admin.loyalty.history') }}" class="ta-btn-ghost">History</a>
        </x-slot:actions>
    </x-admin.page-header>

    <form method="POST" action="{{ route('admin.loyalty.settings.update') }}" class="space-y-5">
        @csrf
        @method('PUT')

        <x-admin.list-card title="1. Program Status">
            <label class="ta-switch-row">
                <div>
                    <strong>Enable Loyalty Program</strong>
                    <p class="ta-muted">When off, POS will not earn or redeem points.</p>
                </div>
                <input type="checkbox" name="enabled" value="1" @checked(old('enabled', $settings->enabled))>
            </label>
        </x-admin.list-card>

        <x-admin.list-card title="2. Point Earning">
            <div class="admin-form-grid">
                <div class="ta-field">
                    <label>Amount Spent (KES)</label>
                    <input type="number" step="0.01" min="0.01" name="amount_per_point" value="{{ old('amount_per_point', $settings->amount_per_point) }}" required>
                    <small class="ta-muted">Spend this amount to earn points (e.g. 100).</small>
                </div>
                <div class="ta-field">
                    <label>Points Awarded</label>
                    <input type="number" min="1" name="points_awarded" value="{{ old('points_awarded', $settings->points_awarded) }}" required>
                    <small class="ta-muted">Whole points only. Formula: floor(amount ÷ amount_per_point) × points_awarded.</small>
                </div>
                <div class="ta-field">
                    <label>Minimum Purchase (KES)</label>
                    <input type="number" step="0.01" min="0" name="minimum_purchase" value="{{ old('minimum_purchase', $settings->minimum_purchase) }}" required>
                    <small class="ta-muted">Sale must meet this total (eligible amount) before points are awarded.</small>
                </div>
            </div>
            <p class="ta-muted" style="margin-top:12px;">Example: KES 100 = 1 point → KES 5,000 earns 50 points.</p>
        </x-admin.list-card>

        <x-admin.list-card title="3. Redemption Rules">
            <label class="ta-switch-row" style="margin-bottom:16px;">
                <div>
                    <strong>Enable Point Redemption</strong>
                    <p class="ta-muted">Allow customers to spend points for discounts.</p>
                </div>
                <input type="checkbox" name="redemption_enabled" value="1" @checked(old('redemption_enabled', $settings->redemption_enabled))>
            </label>
            <div class="admin-form-grid">
                <div class="ta-field">
                    <label>Points Required</label>
                    <input type="number" min="1" name="redemption_points" value="{{ old('redemption_points', $settings->redemption_points) }}" required>
                    <small class="ta-muted">Minimum block size (e.g. 100 points).</small>
                </div>
                <div class="ta-field">
                    <label>Reward Value (KES)</label>
                    <input type="number" step="0.01" min="0" name="redemption_value" value="{{ old('redemption_value', $settings->redemption_value) }}" required>
                    <small class="ta-muted">Discount for each block (e.g. 100 pts = KES 10).</small>
                </div>
            </div>
        </x-admin.list-card>

        <x-admin.list-card title="4. POS Options">
            <div class="space-y-3">
                <label class="ta-switch-row">
                    <div><strong>Allow loyalty redemption at POS</strong><p class="ta-muted">Cashiers can redeem points during checkout.</p></div>
                    <input type="checkbox" name="allow_redemption_at_pos" value="1" @checked(old('allow_redemption_at_pos', $settings->allow_redemption_at_pos))>
                </label>
                <label class="ta-switch-row">
                    <div><strong>Show points earned before completing sale</strong><p class="ta-muted">Estimate only — points award after payment succeeds.</p></div>
                    <input type="checkbox" name="show_estimated_points_on_pos" value="1" @checked(old('show_estimated_points_on_pos', $settings->show_estimated_points_on_pos))>
                </label>
                <label class="ta-switch-row">
                    <div><strong>Show loyalty balance after sale</strong><p class="ta-muted">Include remaining points in the success message.</p></div>
                    <input type="checkbox" name="show_balance_after_sale" value="1" @checked(old('show_balance_after_sale', $settings->show_balance_after_sale))>
                </label>
                <label class="ta-switch-row">
                    <div><strong>Print loyalty points on receipt</strong><p class="ta-muted">Show card, earned, redeemed, and balance on POS receipts.</p></div>
                    <input type="checkbox" name="show_on_receipt" value="1" @checked(old('show_on_receipt', $settings->show_on_receipt))>
                </label>
            </div>
        </x-admin.list-card>

        <x-admin.list-card title="5. Discount Rule">
            <label class="ta-switch-row">
                <div>
                    <strong>Allow points to be earned on discounted sales</strong>
                    <p class="ta-muted">If off, points are calculated from the amount after coupon discounts.</p>
                </div>
                <input type="checkbox" name="allow_earn_on_discounted" value="1" @checked(old('allow_earn_on_discounted', $settings->allow_earn_on_discounted))>
            </label>
        </x-admin.list-card>

        <x-admin.list-card title="6. Returns / Refunds">
            <label class="ta-switch-row">
                <div>
                    <strong>Automatically reverse loyalty points when a sale is refunded</strong>
                    <p class="ta-muted">Creates a reversed ledger entry; never deletes the original earn.</p>
                </div>
                <input type="checkbox" name="reverse_points_on_refund" value="1" @checked(old('reverse_points_on_refund', $settings->reverse_points_on_refund))>
            </label>
        </x-admin.list-card>

        <x-admin.list-card title="7. Point Expiration">
            <label class="ta-switch-row" style="margin-bottom:16px;">
                <div>
                    <strong>Enable point expiration</strong>
                    <p class="ta-muted">Only applied when enabled (job/manual expiry can use this period).</p>
                </div>
                <input type="checkbox" name="points_expiration_enabled" value="1" @checked(old('points_expiration_enabled', $settings->points_expiration_enabled))>
            </label>
            <div class="ta-field" style="max-width:240px;">
                <label>Expiration period (days)</label>
                <input type="number" min="1" name="points_expiration_days" value="{{ old('points_expiration_days', $settings->points_expiration_days) }}" required>
            </div>
        </x-admin.list-card>

        <div>
            <button type="submit" class="ta-btn">Save Loyalty Settings</button>
        </div>
    </form>
</div>

<style>
.ta-switch-row { display:flex; align-items:flex-start; justify-content:space-between; gap:16px; padding:12px 0; border-bottom:1px solid rgba(28,36,52,.08); }
.ta-switch-row:last-child { border-bottom:0; }
.ta-switch-row input[type=checkbox] { width:44px; height:24px; accent-color:#a58112; margin-top:4px; flex-shrink:0; }
.ta-muted { color:#64748b; font-size:.85rem; margin:.25rem 0 0; }
.space-y-5 > * + * { margin-top:1.25rem; }
.space-y-3 > * + * { margin-top:.75rem; }
</style>
@endsection
