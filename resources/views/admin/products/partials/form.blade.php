<div class="product-form-fields">
    <div class="product-row product-row-two">
        <div class="product-field">
            <label for="product-category">Category</label>
            <select id="product-category" name="category_id" class="product-input @error('category_id') is-invalid @enderror">
                <option value="">Uncategorized</option>
                @foreach($categories as $category)
                    <option value="{{ $category->id }}" @selected((string) old('category_id', $product?->category_id) === (string) $category->id)>{{ $category->name }}</option>
                @endforeach
            </select>
            @error('category_id')
                <small class="product-error">{{ $message }}</small>
            @enderror
        </div>

        <div class="product-field">
            <label for="product-vendor">Vendor</label>
            <select id="product-vendor" name="vendor_id" class="product-input @error('vendor_id') is-invalid @enderror">
                <option value="">Platform / In-house</option>
                @foreach($vendors as $vendor)
                    <option value="{{ $vendor->id }}" @selected((string) old('vendor_id', $product?->vendor_id) === (string) $vendor->id)>{{ $vendor->name }}</option>
                @endforeach
            </select>
            @error('vendor_id')
                <small class="product-error">{{ $message }}</small>
            @enderror
        </div>
    </div>

    <div class="product-row product-row-two">
        <div class="product-field">
            <label for="product-name">Name</label>
            <input id="product-name" type="text" name="name" class="product-input @error('name') is-invalid @enderror" value="{{ old('name', $product?->name) }}" required maxlength="255">
            @error('name')
                <small class="product-error">{{ $message }}</small>
            @enderror
        </div>

        <div class="product-field">
            <label for="product-slug">Slug</label>
            <input id="product-slug" type="text" name="slug" class="product-input @error('slug') is-invalid @enderror" value="{{ old('slug', $product?->slug) }}">
            @error('slug')
                <small class="product-error">{{ $message }}</small>
            @enderror
        </div>
    </div>

    @php
        $discountPercentValue = old('discount_percent');
        if ($discountPercentValue === null && $product?->hasSale()) {
            $discountPercentValue = $product->discountPercent();
        }
    @endphp
    <div class="product-row product-row-three">
        <div class="product-field">
            <label for="product-price">Regular Price (KES)</label>
            <input id="product-price" type="number" name="price" class="product-input @error('price') is-invalid @enderror" step="0.01" min="0" value="{{ old('price', $product?->price) }}" required>
            @error('price')
                <small class="product-error">{{ $message }}</small>
            @enderror
        </div>

        <div class="product-field">
            <label for="product-discount-percent">Discount %</label>
            <input id="product-discount-percent" type="number" name="discount_percent" class="product-input @error('discount_percent') is-invalid @enderror" step="1" min="1" max="99" value="{{ $discountPercentValue }}" placeholder="e.g. 16">
            @error('discount_percent')
                <small class="product-error">{{ $message }}</small>
            @enderror
        </div>

        <div class="product-field">
            <label for="product-sale-price">Sale Price (KES)</label>
            <input id="product-sale-price" type="number" name="sale_price" class="product-input @error('sale_price') is-invalid @enderror" step="0.01" min="0" value="{{ old('sale_price', $product?->sale_price) }}">
            @error('sale_price')
                <small class="product-error">{{ $message }}</small>
            @enderror
        </div>
    </div>

        <div class="product-row product-row-two">
        <div class="product-field">
            <label for="product-sku">SKU</label>
            <input id="product-sku" type="text" name="sku" class="product-input @error('sku') is-invalid @enderror" value="{{ old('sku', $product?->sku) }}">
            @error('sku')
                <small class="product-error">{{ $message }}</small>
            @enderror
        </div>

        <div class="product-field">
            <label for="product-barcode">Barcode</label>
            <input
                id="product-barcode"
                type="text"
                name="barcode"
                class="product-input @error('barcode') is-invalid @enderror"
                value="{{ old('barcode', $product?->barcode) }}"
                maxlength="64"
                autocomplete="off"
                inputmode="numeric"
                placeholder="Scan or type barcode"
            >
            <small class="product-help">Click this field, then scan the product barcode. You can also type it. Used at POS to add items.</small>
            @error('barcode')
                <small class="product-error">{{ $message }}</small>
            @enderror
        </div>
    </div>

    <div class="product-field">
            <label for="product-stock">Stock</label>
            <input id="product-stock" type="number" name="stock" class="product-input @error('stock') is-invalid @enderror" min="0" value="{{ old('stock', $product?->stock ?? 0) }}" required>
            @error('stock')
                <small class="product-error">{{ $message }}</small>
            @enderror
        </div>

    <div class="product-field">
        <label for="product-description">Description</label>
        <textarea id="product-description" name="description" class="product-input @error('description') is-invalid @enderror" rows="5">{{ old('description', $product?->description) }}</textarea>
        @error('description')
            <small class="product-error">{{ $message }}</small>
        @enderror
    </div>

    <div class="product-row product-row-two">
        <div class="product-field">
            <label for="product-image-url">Image URL</label>
            <input id="product-image-url" type="url" name="image_url" class="product-input @error('image_url') is-invalid @enderror" value="{{ old('image_url', $product?->image_url) }}">
            @error('image_url')
                <small class="product-error">{{ $message }}</small>
            @enderror
        </div>

        <div class="product-field">
            <label for="product-image-file">Upload Image</label>
            <input id="product-image-file" type="file" name="image_file" class="product-input @error('image_file') is-invalid @enderror" accept="image/*">
            @error('image_file')
                <small class="product-error">{{ $message }}</small>
            @enderror
        </div>
    </div>

    <div class="product-field">
        <label class="product-switch" for="product-active">
            <input id="product-active" type="checkbox" name="is_active" value="1" {{ old('is_active', $product?->is_active ?? true) ? 'checked' : '' }}>
            <span>Active</span>
        </label>
    </div>
</div>

<script>
    (function () {
        const priceInput = document.getElementById('product-price');
        const saleInput = document.getElementById('product-sale-price');
        const percentInput = document.getElementById('product-discount-percent');
        if (!priceInput || !saleInput || !percentInput) {
            return;
        }

        let syncing = false;

        function roundMoney(value) {
            return (Math.round(value * 100) / 100).toFixed(2);
        }

        percentInput.addEventListener('input', function () {
            if (syncing) {
                return;
            }
            syncing = true;
            const price = Number.parseFloat(priceInput.value);
            const percent = Number.parseFloat(percentInput.value);
            if (price > 0 && percent > 0 && percent < 100) {
                saleInput.value = roundMoney(price * (1 - (percent / 100)));
            } else if (!percentInput.value) {
                saleInput.value = '';
            }
            syncing = false;
        });

        saleInput.addEventListener('input', function () {
            if (syncing) {
                return;
            }
            syncing = true;
            const price = Number.parseFloat(priceInput.value);
            const sale = Number.parseFloat(saleInput.value);
            if (price > 0 && sale > 0 && sale < price) {
                percentInput.value = String(Math.round((1 - (sale / price)) * 100));
            } else if (!saleInput.value) {
                percentInput.value = '';
            }
            syncing = false;
        });

        priceInput.addEventListener('input', function () {
            if (percentInput.value) {
                percentInput.dispatchEvent(new Event('input'));
            }
        });

        const barcodeInput = document.getElementById('product-barcode');
        if (barcodeInput) {
            barcodeInput.addEventListener('keydown', function (event) {
                if (event.key === 'Enter') {
                    event.preventDefault();
                    document.getElementById('product-stock')?.focus();
                }
            });
        }
    })();
</script>
