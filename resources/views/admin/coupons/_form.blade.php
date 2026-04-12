<div class="coupon-form-fields">
    <div class="coupon-row coupon-row-two">
        <div class="coupon-field">
            <label for="coupon-code">Code</label>
            <input id="coupon-code" class="coupon-input @error('code') is-invalid @enderror" type="text" name="code" value="{{ old('code', $coupon->code ?? '') }}" required placeholder="e.g. SAVE10">
            @error('code')
                <small class="coupon-error">{{ $message }}</small>
            @enderror
        </div>
        <div class="coupon-field">
            <label for="coupon-type">Type</label>
            <select id="coupon-type" class="coupon-input @error('type') is-invalid @enderror" name="type" required>
                <option value="fixed" {{ old('type', $coupon->type ?? 'fixed') === 'fixed' ? 'selected' : '' }}>Fixed Amount</option>
                <option value="percent" {{ old('type', $coupon->type ?? 'fixed') === 'percent' ? 'selected' : '' }}>Percent</option>
            </select>
            @error('type')
                <small class="coupon-error">{{ $message }}</small>
            @enderror
        </div>
    </div>

    <div class="coupon-row coupon-row-two">
        <div class="coupon-field">
            <label for="coupon-value">Value</label>
            <input id="coupon-value" class="coupon-input @error('value') is-invalid @enderror" type="number" step="0.01" min="0.01" name="value" value="{{ old('value', $coupon->value ?? '') }}" required>
            <small class="coupon-help">For percent coupons, use numbers like 10 for 10%.</small>
            @error('value')
                <small class="coupon-error">{{ $message }}</small>
            @enderror
        </div>
        <div class="coupon-field">
            <label for="coupon-min-order">Minimum Order Amount</label>
            <input id="coupon-min-order" class="coupon-input @error('min_order_amount') is-invalid @enderror" type="number" step="0.01" min="0" name="min_order_amount" value="{{ old('min_order_amount', $coupon->min_order_amount ?? 0) }}">
            @error('min_order_amount')
                <small class="coupon-error">{{ $message }}</small>
            @enderror
        </div>
    </div>

    <div class="coupon-row coupon-row-two">
        <div class="coupon-field">
            <label for="coupon-usage-limit">Usage Limit (optional)</label>
            <input id="coupon-usage-limit" class="coupon-input @error('usage_limit') is-invalid @enderror" type="number" min="1" name="usage_limit" value="{{ old('usage_limit', $coupon->usage_limit ?? '') }}">
            @error('usage_limit')
                <small class="coupon-error">{{ $message }}</small>
            @enderror
        </div>
        <div class="coupon-field">
            <label for="coupon-starts-at">Starts At (optional)</label>
            <input id="coupon-starts-at" class="coupon-input @error('starts_at') is-invalid @enderror" type="datetime-local" name="starts_at" value="{{ old('starts_at', !empty($coupon?->starts_at) ? $coupon->starts_at->format('Y-m-d\TH:i') : '') }}">
            @error('starts_at')
                <small class="coupon-error">{{ $message }}</small>
            @enderror
        </div>
    </div>

    <div class="coupon-row coupon-row-two">
        <div class="coupon-field">
            <label for="coupon-expires-at">Expires At (optional)</label>
            <input id="coupon-expires-at" class="coupon-input @error('expires_at') is-invalid @enderror" type="datetime-local" name="expires_at" value="{{ old('expires_at', !empty($coupon?->expires_at) ? $coupon->expires_at->format('Y-m-d\TH:i') : '') }}">
            @error('expires_at')
                <small class="coupon-error">{{ $message }}</small>
            @enderror
        </div>
        <div class="coupon-field">
            <label class="coupon-switch" for="coupon-active">
                <input id="coupon-active" type="checkbox" name="is_active" value="1" {{ old('is_active', $coupon->is_active ?? true) ? 'checked' : '' }}>
                <span>Active coupon</span>
            </label>
            <small class="coupon-help">Inactive coupons cannot be applied at checkout.</small>
        </div>
    </div>
</div>
