@extends('layouts.admin')
@section('title', 'Create Product')
@section('content')
    <style>
        .product-form-wrap {
            --surface: #ffffff;
            --surface-soft: #f7f8fa;
            --line: #d7dde4;
            --line-strong: #b8c2cd;
            --text: #121212;
            --muted: #556273;
            --accent: #d4af37;
            --accent-ink: #5f4711;
            display: grid;
            gap: 20px;
        }
        .product-form-head {
            padding: 22px 24px;
            border-radius: 18px;
            background: linear-gradient(135deg, #ffffff 0%, #f6f8fc 62%, #eef2f7 100%);
            border: 1px solid #dbe3ec;
            box-shadow: 0 18px 36px rgba(13, 29, 47, 0.08);
        }
        .product-form-eyebrow {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            font-size: 0.76rem;
            letter-spacing: 0.08em;
            text-transform: uppercase;
            font-weight: 700;
            color: #48566b;
            background: rgba(255, 255, 255, 0.9);
            border: 1px solid #d2dce7;
            border-radius: 999px;
            padding: 6px 12px;
            margin-bottom: 10px;
        }
        .product-form-head h2 {
            margin: 0;
            font-size: clamp(1.4rem, 2.3vw, 1.95rem);
            color: var(--text);
            letter-spacing: -0.02em;
        }
        .product-form-head p {
            margin: 10px 0 0;
            color: var(--muted);
            max-width: 72ch;
            font-size: 0.97rem;
            line-height: 1.45;
        }
        .product-page-card {
            padding: 16px;
            border-radius: 20px;
            border: 1px solid #dce4ee;
            background: linear-gradient(180deg, #f9fbfd 0%, #f2f5f9 100%);
        }
        .product-form-grid {
            display: grid;
            grid-template-columns: minmax(0, 1.75fr) minmax(280px, 1fr);
            gap: 18px;
            align-items: start;
        }
        .product-section-card {
            border: 1px solid var(--line);
            border-radius: 16px;
            padding: 18px;
            background: var(--surface);
            box-shadow: 0 10px 24px rgba(16, 32, 52, 0.07);
        }
        .product-section-title {
            margin: 0;
            font-size: 1.02rem;
            font-weight: 700;
            color: #1a2430;
        }
        .product-section-subtitle {
            margin: 6px 0 14px;
            font-size: 0.9rem;
            color: var(--muted);
        }
        .product-form-fields {
            display: grid;
            gap: 15px;
        }
        .product-row {
            display: grid;
            gap: 14px;
        }
        .product-row-two {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }
        .product-field {
            display: grid;
            gap: 6px;
        }
        .product-field label {
            font-size: 0.84rem;
            font-weight: 700;
            color: #253344;
            letter-spacing: 0.01em;
        }
        .product-input {
            width: 100%;
            border: 1px solid var(--line);
            border-radius: 12px;
            background: var(--surface-soft);
            color: #162233;
            font: inherit;
            font-size: 0.95rem;
            padding: 11px 12px;
            transition: border-color 0.2s ease, box-shadow 0.2s ease, background-color 0.2s ease;
        }
        textarea.product-input {
            resize: vertical;
            min-height: 120px;
        }
        .product-input:focus {
            outline: none;
            border-color: var(--line-strong);
            box-shadow: 0 0 0 4px rgba(61, 92, 126, 0.12);
            background: #fff;
        }
        .product-input.is-invalid {
            border-color: #d25157;
            box-shadow: 0 0 0 3px rgba(210, 81, 87, 0.15);
            background: #fff8f8;
        }
        .product-help {
            color: #617285;
            font-size: 0.79rem;
            line-height: 1.4;
        }
        .product-error {
            color: #b4232a;
            font-size: 0.8rem;
            font-weight: 600;
        }
        .product-switch {
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
        .product-switch input {
            width: 16px;
            height: 16px;
            accent-color: #2a8d48;
        }
        .product-preview {
            border: 1px solid #d2dae5;
            border-radius: 14px;
            background: linear-gradient(180deg, #ffffff 0%, #f7f9fc 100%);
            padding: 16px;
            display: grid;
            gap: 12px;
        }
        .product-status {
            border-radius: 999px;
            padding: 5px 12px;
            width: fit-content;
            font-size: 0.76rem;
            font-weight: 700;
            letter-spacing: 0.07em;
            text-transform: uppercase;
            border: 1px solid transparent;
        }
        .product-status.active {
            background: rgba(46, 125, 50, 0.12);
            color: #1b5e20;
            border-color: rgba(46, 125, 50, 0.26);
        }
        .product-status.inactive {
            background: rgba(107, 114, 128, 0.14);
            color: #4b5563;
            border-color: rgba(107, 114, 128, 0.3);
        }
        .product-preview-image-box {
            border-radius: 14px;
            background: #edf1f6;
            border: 1px dashed #c4cedb;
            min-height: 180px;
            display: grid;
            place-items: center;
            padding: 10px;
        }
        .product-preview-img {
            width: 100%;
            max-width: 180px;
            height: 180px;
            object-fit: cover;
            border-radius: 12px;
            border: 1px solid #d5dde8;
            background: #ffffff;
            display: block;
        }
        .product-preview-placeholder {
            color: #708095;
            font-size: 0.88rem;
            font-weight: 600;
            text-align: center;
        }
        .product-preview-name {
            font-weight: 700;
            color: #172131;
            font-size: 1.04rem;
            margin: 0;
        }
        .product-price-row {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
            align-items: center;
        }
        .product-preview-price {
            color: #1f2f42;
            font-size: 1.02rem;
            font-weight: 700;
            margin: 0;
        }
        .product-preview-sale {
            color: #b4232a;
            font-size: 0.88rem;
            font-weight: 700;
            background: rgba(180, 35, 42, 0.08);
            border: 1px solid rgba(180, 35, 42, 0.2);
            border-radius: 999px;
            padding: 4px 10px;
        }
        .product-preview-sale.is-hidden {
            display: none;
        }
        .product-preview-meta {
            display: flex;
            flex-wrap: wrap;
            gap: 7px;
        }
        .product-meta-chip {
            font-size: 0.78rem;
            color: #304258;
            background: #eff3f8;
            border: 1px solid #d5deea;
            border-radius: 999px;
            padding: 4px 10px;
        }
        .product-actions {
            display: flex;
            gap: 10px;
            margin-top: 16px;
            flex-wrap: wrap;
        }
        .product-actions .btn-create,
        .product-actions .btn-outline {
            border-radius: 999px;
            padding: 10px 16px;
            font-weight: 700;
            text-decoration: none;
            font-size: 0.92rem;
            transition: transform 0.18s ease, box-shadow 0.2s ease, background-color 0.2s ease;
        }
        .product-actions .btn-create {
            border: 1px solid transparent;
            color: #1a1300;
            background: linear-gradient(135deg, #e2c15a 0%, #d4af37 100%);
            box-shadow: 0 10px 20px rgba(212, 175, 55, 0.28);
        }
        .product-actions .btn-create:hover {
            transform: translateY(-2px);
            box-shadow: 0 14px 22px rgba(212, 175, 55, 0.35);
        }
        .product-actions .btn-outline {
            border: 1px solid #c9d3df;
            color: #233142;
            background: #ffffff;
            box-shadow: 0 6px 14px rgba(23, 40, 62, 0.07);
        }
        .product-actions .btn-outline:hover {
            transform: translateY(-2px);
            background: #f8fbff;
        }
        .product-preview-panel {
            position: sticky;
            top: 88px;
        }
        @media (max-width: 960px) {
            .product-form-grid {
                grid-template-columns: 1fr;
            }
            .product-row-two {
                grid-template-columns: 1fr;
            }
            .product-preview-panel {
                position: static;
            }
        }
    </style>

    <div class="product-form-wrap">
        <div class="product-form-head">
            <span class="product-form-eyebrow">Product Management</span>
            <h2>Create Product</h2>
            <p>Add a new product with clear details, accurate pricing, and optional media. You can adjust everything later, so this page helps you publish quickly with confidence.</p>
        </div>

        <div class="card product-page-card">
            <form method="POST" action="{{ route('admin.products.store') }}" enctype="multipart/form-data">
                @csrf
                <div class="product-form-grid">
                    <div class="product-section-card">
                        <h3 class="product-section-title">Product Details</h3>
                        <p class="product-section-subtitle">Complete the fields below to publish a polished, storefront-ready product listing.</p>
                        @include('admin.products.partials.form', ['product' => null])
                    </div>

                    <div class="product-section-card product-preview-panel">
                        <h3 class="product-section-title">Live Preview</h3>
                        <p class="product-section-subtitle">See how your product card looks while you type.</p>
                        <div class="product-preview">
                            <span id="product-status" class="product-status active">Active</span>

                            <div class="product-preview-image-box" id="product-image-preview">
                                <div class="product-preview-placeholder">No image selected yet</div>
                            </div>

                            <p id="product-name-preview" class="product-preview-name">Product Name Preview</p>

                            <div class="product-price-row">
                                <p id="product-price-preview" class="product-preview-price">KES 0.00</p>
                                <span id="product-sale-preview" class="product-preview-sale is-hidden"></span>
                            </div>

                            <div class="product-preview-meta">
                                <span id="product-meta-category" class="product-meta-chip">Uncategorized</span>
                                <span id="product-meta-vendor" class="product-meta-chip">Platform / In-house</span>
                                <span id="product-meta-stock" class="product-meta-chip">Stock: 0</span>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="product-actions">
                    <button type="submit" class="btn-create">Create Product</button>
                    <a class="btn-outline" href="{{ route('admin.products.index') }}">Cancel</a>
                </div>
            </form>
        </div>
    </div>

    <script>
        (function () {
            const nameInput = document.getElementById('product-name');
            const priceInput = document.getElementById('product-price');
            const salePriceInput = document.getElementById('product-sale-price');
            const stockInput = document.getElementById('product-stock');
            const categoryInput = document.getElementById('product-category');
            const vendorInput = document.getElementById('product-vendor');
            const activeInput = document.getElementById('product-active');
            const urlInput = document.getElementById('product-image-url');
            const fileInput = document.getElementById('product-image-file');

            const namePreview = document.getElementById('product-name-preview');
            const pricePreview = document.getElementById('product-price-preview');
            const salePreview = document.getElementById('product-sale-preview');
            const statusPreview = document.getElementById('product-status');
            const categoryPreview = document.getElementById('product-meta-category');
            const vendorPreview = document.getElementById('product-meta-vendor');
            const stockPreview = document.getElementById('product-meta-stock');
            const imagePreview = document.getElementById('product-image-preview');

            function formatCurrency(value) {
                const amount = Number.parseFloat(value);
                return Number.isNaN(amount) ? 'KES 0.00' : `KES ${amount.toFixed(2)}`;
            }

            function selectedOptionText(selectElement, fallback) {
                if (!selectElement) {
                    return fallback;
                }
                const option = selectElement.options[selectElement.selectedIndex];
                return option && option.text ? option.text : fallback;
            }

            function setImagePreview(source) {
                if (!imagePreview) {
                    return;
                }

                imagePreview.innerHTML = '';
                if (!source) {
                    const placeholder = document.createElement('div');
                    placeholder.className = 'product-preview-placeholder';
                    placeholder.textContent = 'No image selected yet';
                    imagePreview.appendChild(placeholder);
                    return;
                }

                const image = document.createElement('img');
                image.className = 'product-preview-img';
                image.alt = 'Product image preview';
                image.src = source;
                imagePreview.appendChild(image);
            }

            function updatePreview() {
                const nameValue = (nameInput && nameInput.value ? nameInput.value : '').trim();
                const priceValue = (priceInput && priceInput.value ? priceInput.value : '').trim();
                const saleValue = (salePriceInput && salePriceInput.value ? salePriceInput.value : '').trim();
                const stockValue = (stockInput && stockInput.value ? stockInput.value : '0').trim();
                const isActive = !!(activeInput && activeInput.checked);
                const urlValue = (urlInput && urlInput.value ? urlInput.value : '').trim();

                namePreview.textContent = nameValue || 'Product Name Preview';
                pricePreview.textContent = formatCurrency(priceValue);

                const saleAmount = Number.parseFloat(saleValue);
                if (!Number.isNaN(saleAmount) && saleAmount > 0) {
                    salePreview.textContent = `Sale ${formatCurrency(saleAmount)}`;
                    salePreview.classList.remove('is-hidden');
                } else {
                    salePreview.textContent = '';
                    salePreview.classList.add('is-hidden');
                }

                statusPreview.className = `product-status ${isActive ? 'active' : 'inactive'}`;
                statusPreview.textContent = isActive ? 'Active' : 'Inactive';

                categoryPreview.textContent = selectedOptionText(categoryInput, 'Uncategorized');
                vendorPreview.textContent = selectedOptionText(vendorInput, 'Platform / In-house');
                stockPreview.textContent = `Stock: ${stockValue || '0'}`;

                if (urlValue) {
                    setImagePreview(urlValue);
                } else if (!fileInput || !fileInput.files || !fileInput.files.length) {
                    setImagePreview(null);
                }
            }

            [
                nameInput,
                priceInput,
                salePriceInput,
                stockInput,
                categoryInput,
                vendorInput,
                activeInput,
                urlInput,
            ].forEach(function (element) {
                if (!element) {
                    return;
                }
                element.addEventListener('input', updatePreview);
                element.addEventListener('change', updatePreview);
            });

            if (fileInput) {
                fileInput.addEventListener('change', function () {
                    if (fileInput.files && fileInput.files[0]) {
                        const reader = new FileReader();
                        reader.onload = function (event) {
                            const result = event && event.target ? event.target.result : null;
                            setImagePreview(result || null);
                        };
                        reader.readAsDataURL(fileInput.files[0]);
                        return;
                    }
                    updatePreview();
                });
            }

            updatePreview();
        })();
    </script>
@endsection
