@extends('layouts.admin')
@section('title', 'Create Coupon')
@section('content')
    <style>
        .coupon-page {
            display: grid;
            gap: 20px;
        }
        .coupon-head {
            padding: 22px 24px;
            border-radius: 18px;
            border: 1px solid #dbe3ec;
            background: linear-gradient(135deg, #ffffff 0%, #f6f8fc 62%, #eef2f7 100%);
            box-shadow: 0 16px 30px rgba(13, 29, 47, 0.08);
        }
        .coupon-head h2 {
            margin: 0;
            font-size: 1.8rem;
            color: #111827;
            letter-spacing: -0.02em;
        }
        .coupon-head p {
            margin: 10px 0 0;
            color: #556273;
            font-size: 0.95rem;
            line-height: 1.45;
        }
        .coupon-grid {
            display: grid;
            grid-template-columns: minmax(0, 1.7fr) minmax(280px, 1fr);
            gap: 16px;
            align-items: start;
        }
        .coupon-card {
            border: 1px solid #d7dde4;
            border-radius: 16px;
            padding: 18px;
            background: #fff;
            box-shadow: 0 10px 22px rgba(17, 24, 39, 0.07);
        }
        .coupon-card h3 {
            margin: 0;
            font-size: 1.04rem;
            color: #1f2937;
        }
        .coupon-sub {
            margin: 6px 0 14px;
            font-size: 0.9rem;
            color: #6b7280;
        }
        .coupon-form-fields {
            display: grid;
            gap: 14px;
        }
        .coupon-row {
            display: grid;
            gap: 12px;
        }
        .coupon-row-two {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }
        .coupon-field {
            display: grid;
            gap: 6px;
        }
        .coupon-field label {
            font-size: 0.84rem;
            font-weight: 700;
            color: #253344;
        }
        .coupon-input {
            width: 100%;
            border: 1px solid #d7dde4;
            border-radius: 12px;
            background: #f7f8fa;
            color: #162233;
            font: inherit;
            font-size: 0.95rem;
            padding: 11px 12px;
            transition: border-color 0.2s ease, box-shadow 0.2s ease, background-color 0.2s ease;
        }
        .coupon-input:focus {
            outline: none;
            border-color: #b8c2cd;
            box-shadow: 0 0 0 4px rgba(61, 92, 126, 0.12);
            background: #fff;
        }
        .coupon-input.is-invalid {
            border-color: #d25157;
            box-shadow: 0 0 0 3px rgba(210, 81, 87, 0.15);
            background: #fff8f8;
        }
        .coupon-help {
            color: #617285;
            font-size: 0.79rem;
            line-height: 1.4;
        }
        .coupon-error {
            color: #b4232a;
            font-size: 0.8rem;
            font-weight: 600;
        }
        .coupon-switch {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            width: fit-content;
            padding: 8px 12px;
            border-radius: 12px;
            border: 1px solid #d7e0ea;
            background: #f8fafc;
            color: #1f2f42;
            font-weight: 600;
        }
        .coupon-switch input {
            width: 16px;
            height: 16px;
            accent-color: #2a8d48;
        }
        .coupon-preview {
            display: grid;
            gap: 10px;
            border: 1px solid #d2dae5;
            border-radius: 14px;
            background: linear-gradient(180deg, #ffffff 0%, #f7f9fc 100%);
            padding: 16px;
        }
        .coupon-status {
            display: inline-flex;
            width: fit-content;
            border-radius: 999px;
            padding: 5px 12px;
            font-size: 0.75rem;
            letter-spacing: 0.07em;
            text-transform: uppercase;
            font-weight: 700;
            border: 1px solid transparent;
        }
        .coupon-status.active {
            color: #14532d;
            background: rgba(34, 197, 94, 0.15);
            border-color: rgba(34, 197, 94, 0.28);
        }
        .coupon-status.inactive {
            color: #4b5563;
            background: rgba(107, 114, 128, 0.14);
            border-color: rgba(107, 114, 128, 0.28);
        }
        .coupon-preview-code {
            font-size: 1.2rem;
            font-weight: 800;
            color: #111827;
            letter-spacing: 0.04em;
        }
        .coupon-preview-value {
            font-size: 1rem;
            font-weight: 700;
            color: #1f2f42;
        }
        .coupon-meta {
            display: flex;
            flex-wrap: wrap;
            gap: 7px;
        }
        .coupon-chip {
            font-size: 0.78rem;
            color: #304258;
            background: #eff3f8;
            border: 1px solid #d5deea;
            border-radius: 999px;
            padding: 4px 10px;
        }
        .coupon-actions {
            display: flex;
            gap: 10px;
            margin-top: 16px;
            flex-wrap: wrap;
        }
        .coupon-btn-primary,
        .coupon-btn-secondary {
            border-radius: 999px;
            padding: 10px 16px;
            font-weight: 700;
            text-decoration: none;
        }
        .coupon-btn-primary {
            border: 1px solid transparent;
            color: #1a1300;
            background: linear-gradient(135deg, #e2c15a 0%, #d4af37 100%);
            box-shadow: 0 10px 20px rgba(212, 175, 55, 0.28);
        }
        .coupon-btn-secondary {
            border: 1px solid #c9d3df;
            color: #233142;
            background: #ffffff;
        }
        .coupon-preview-panel {
            position: sticky;
            top: 88px;
        }
        @media (max-width: 960px) {
            .coupon-grid {
                grid-template-columns: 1fr;
            }
            .coupon-row-two {
                grid-template-columns: 1fr;
            }
            .coupon-preview-panel {
                position: static;
            }
        }
    </style>

    <div class="coupon-page">
        <div class="coupon-head">
            <h2>Create Coupon</h2>
            <p>Create a discount code with clear rules so checkout promotions stay controlled and easy to manage.</p>
        </div>

        <div class="card">
            <form method="POST" action="{{ route('admin.coupons.store') }}">
                @csrf
                <div class="coupon-grid">
                    <section class="coupon-card">
                        <h3>Coupon Details</h3>
                        <p class="coupon-sub">Set value, order limits, activity status, and validity window.</p>
                        @include('admin.coupons._form', ['coupon' => new \App\Models\Coupon()])
                    </section>

                    <aside class="coupon-card coupon-preview-panel">
                        <h3>Live Preview</h3>
                        <p class="coupon-sub">Quick summary of what customers will receive.</p>
                        <div class="coupon-preview">
                            <span id="coupon-status-preview" class="coupon-status active">Active</span>
                            <div id="coupon-code-preview" class="coupon-preview-code">NEWCOUPON</div>
                            <div id="coupon-value-preview" class="coupon-preview-value">KES 0.00 off</div>
                            <div class="coupon-meta">
                                <span id="coupon-min-preview" class="coupon-chip">Min order: KES 0.00</span>
                                <span id="coupon-limit-preview" class="coupon-chip">Usage: Unlimited</span>
                                <span id="coupon-start-preview" class="coupon-chip">Starts: Immediately</span>
                                <span id="coupon-expiry-preview" class="coupon-chip">Expires: Never</span>
                            </div>
                        </div>
                    </aside>
                </div>

                <div class="coupon-actions">
                    <button type="submit" class="coupon-btn-primary">Save Coupon</button>
                    <a class="coupon-btn-secondary" href="{{ route('admin.coupons.index') }}">Cancel</a>
                </div>
            </form>
        </div>
    </div>

    <script>
        (function () {
            const codeInput = document.getElementById('coupon-code');
            const typeInput = document.getElementById('coupon-type');
            const valueInput = document.getElementById('coupon-value');
            const minInput = document.getElementById('coupon-min-order');
            const limitInput = document.getElementById('coupon-usage-limit');
            const startsInput = document.getElementById('coupon-starts-at');
            const expiresInput = document.getElementById('coupon-expires-at');
            const activeInput = document.getElementById('coupon-active');

            const codePreview = document.getElementById('coupon-code-preview');
            const valuePreview = document.getElementById('coupon-value-preview');
            const minPreview = document.getElementById('coupon-min-preview');
            const limitPreview = document.getElementById('coupon-limit-preview');
            const startPreview = document.getElementById('coupon-start-preview');
            const expiryPreview = document.getElementById('coupon-expiry-preview');
            const statusPreview = document.getElementById('coupon-status-preview');

            function formatCurrency(value) {
                const amount = Number.parseFloat(value);
                return Number.isNaN(amount) ? 'KES 0.00' : `KES ${amount.toFixed(2)}`;
            }

            function formatDate(value, fallback) {
                if (!value) {
                    return fallback;
                }
                const date = new Date(value);
                if (Number.isNaN(date.getTime())) {
                    return fallback;
                }
                return date.toLocaleString();
            }

            function updatePreview() {
                const code = (codeInput && codeInput.value ? codeInput.value : '').trim();
                const type = typeInput && typeInput.value ? typeInput.value : 'fixed';
                const value = valueInput && valueInput.value ? valueInput.value : '';
                const minOrder = minInput && minInput.value ? minInput.value : '';
                const usageLimit = limitInput && limitInput.value ? limitInput.value : '';
                const startsAt = startsInput && startsInput.value ? startsInput.value : '';
                const expiresAt = expiresInput && expiresInput.value ? expiresInput.value : '';
                const isActive = !!(activeInput && activeInput.checked);

                codePreview.textContent = (code || 'NEWCOUPON').toUpperCase();

                if (type === 'percent') {
                    const percent = Number.parseFloat(value);
                    valuePreview.textContent = Number.isNaN(percent) ? '0% off' : `${percent}% off`;
                } else {
                    valuePreview.textContent = `${formatCurrency(value)} off`;
                }

                minPreview.textContent = `Min order: ${formatCurrency(minOrder)}`;
                limitPreview.textContent = usageLimit ? `Usage: ${usageLimit}` : 'Usage: Unlimited';
                startPreview.textContent = `Starts: ${formatDate(startsAt, 'Immediately')}`;
                expiryPreview.textContent = `Expires: ${formatDate(expiresAt, 'Never')}`;

                statusPreview.className = `coupon-status ${isActive ? 'active' : 'inactive'}`;
                statusPreview.textContent = isActive ? 'Active' : 'Inactive';
            }

            [codeInput, typeInput, valueInput, minInput, limitInput, startsInput, expiresInput, activeInput].forEach(function (el) {
                if (!el) {
                    return;
                }
                el.addEventListener('input', updatePreview);
                el.addEventListener('change', updatePreview);
            });

            updatePreview();
        })();
    </script>
@endsection
