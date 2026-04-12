@extends('layouts.admin')
@section('title', 'Edit Product')
@section('content')
    <style>
        .product-form-wrap { display: grid; gap: 18px; }
        .product-form-head h2 { margin: 0; font-size: 1.6rem; color: #121212; }
        .product-form-head p { margin: 8px 0 0; color: #5f5f5f; font-size: 0.95rem; }
        .product-form-grid { display: grid; grid-template-columns: 2fr 1fr; gap: 20px; }
        .product-section-card { border: 1px solid rgba(0,0,0,0.08); border-radius: 14px; padding: 16px; background: #fff; }
        .product-section-title { margin: 0 0 14px; font-size: 1rem; font-weight: 700; color: #1f1f1f; }
        .product-actions { display: flex; gap: 10px; margin-top: 18px; flex-wrap: wrap; }
        .btn-outline { border: 1px solid #d4af37; border-radius: 999px; color: #1f1f1f; background: #fff; padding: 9px 15px; text-decoration: none; font-weight: 600; }
        @media (max-width: 960px) { .product-form-grid { grid-template-columns: 1fr; } }
        .product-preview-img { width: 120px; height: 120px; object-fit: cover; border-radius: 10px; border: 1px solid #e5e7eb; background: #fafafa; display: block; margin: 0 auto; }
        .product-preview-placeholder { width: 120px; height: 120px; border-radius: 10px; background: #f3f3f3; display: flex; align-items: center; justify-content: center; color: #aaa; font-size: 1.2rem; border: 1px dashed #d4af37; margin: 0 auto; }
    </style>

    <div class="product-form-wrap">
        <div class="product-form-head">
            <h2>Edit Product</h2>
            <p>Update product details, pricing, and images. All fields can be changed at any time.</p>
        </div>

        <div class="card">
            <form method="POST" action="{{ route('admin.products.update', $product) }}" enctype="multipart/form-data">
                @csrf
                @method('PUT')
                <div class="product-form-grid">
                    <div class="product-section-card">
                        <h3 class="product-section-title">Product Details</h3>
                        @include('admin.products.partials.form', ['product' => $product])
                    </div>
                    <div class="product-section-card">
                        <h3 class="product-section-title">Preview</h3>
                        <div class="product-preview">
                            <span id="product-status" class="product-status {{ $product?->is_active ? 'active' : 'inactive' }}">{{ ($product?->is_active ?? true) ? 'Active' : 'Inactive' }}</span>
                            <div id="product-name-preview" class="product-preview-name">{{ $product?->name ?: 'Product Name Preview' }}</div>
                            <div id="product-price-preview" class="product-preview-price">KES {{ number_format((float)($product?->price ?? 0), 2) }}</div>
                            <div id="product-image-preview">
                                @if(!empty($product?->image_url))
                                    <img src="{{ $product->image_url }}" class="product-preview-img" alt="Product image">
                                @else
                                    <div class="product-preview-placeholder">No image</div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
                <div class="product-actions">
                    <button type="submit">Update Product</button>
                    <a class="btn-outline" href="{{ route('admin.products.index') }}">Cancel</a>
                </div>
            </form>
        </div>
    </div>

    <script>
        (function () {
            const nameInput = document.getElementById('product-name');
            const priceInput = document.getElementById('product-price');
            const activeInput = document.getElementById('product-active');
            const urlInput = document.getElementById('product-image-url');
            const fileInput = document.getElementById('product-image-file');
            const namePreview = document.getElementById('product-name-preview');
            const pricePreview = document.getElementById('product-price-preview');
            const statusPreview = document.getElementById('product-status');
            const imagePreview = document.getElementById('product-image-preview');

            function formatPrice(val) {
                const num = parseFloat(val);
                return isNaN(num) ? 'KES 0.00' : 'KES ' + num.toFixed(2);
            }

            function setImagePreview(src) {
                imagePreview.innerHTML = src
                    ? `<img src="${src}" class="product-preview-img" alt="Product image">`
                    : `<div class="product-preview-placeholder">No image</div>`;
            }

            function updatePreview() {
                const nameValue = (nameInput?.value || '').trim();
                const priceValue = (priceInput?.value || '').trim();
                const isActive = !!activeInput?.checked;
                const urlValue = (urlInput?.value || '').trim();

                namePreview.textContent = nameValue || 'Product Name Preview';
                pricePreview.textContent = formatPrice(priceValue);
                statusPreview.className = 'product-status ' + (isActive ? 'active' : 'inactive');
                statusPreview.textContent = isActive ? 'Active' : 'Inactive';
                if (urlValue) setImagePreview(urlValue); else setImagePreview(null);
            }

            [nameInput, priceInput, activeInput, urlInput].forEach(function (element) {
                if (element) {
                    element.addEventListener('input', updatePreview);
                    element.addEventListener('change', updatePreview);
                }
            });

            if (fileInput) {
                fileInput.addEventListener('change', function () {
                    if (fileInput.files && fileInput.files[0]) {
                        const reader = new FileReader();
                        reader.onload = function (e) {
                            setImagePreview(e.target.result);
                        };
                        reader.readAsDataURL(fileInput.files[0]);
                    }
                });
            }

            updatePreview();
        })();
    </script>
@endsection
