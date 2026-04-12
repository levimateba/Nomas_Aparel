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
            <small class="product-help">Assign a category for better organization.</small>
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
            <small class="product-help">Select the vendor supplying this product.</small>
            @error('vendor_id')
                <small class="product-error">{{ $message }}</small>
            @enderror
        </div>
    </div>

    <div class="product-row product-row-two">
        <div class="product-field">
            <label for="product-name">Name</label>
            <input id="product-name" type="text" name="name" class="product-input @error('name') is-invalid @enderror" value="{{ old('name', $product?->name) }}" required maxlength="255" placeholder="e.g. Wireless Mouse">
            <small class="product-help">Enter a clear, descriptive product name.</small>
            @error('name')
                <small class="product-error">{{ $message }}</small>
            @enderror
        </div>

        <div class="product-field">
            <label for="product-slug">Slug (optional)</label>
            <input id="product-slug" type="text" name="slug" class="product-input @error('slug') is-invalid @enderror" value="{{ old('slug', $product?->slug) }}" placeholder="auto-generated-if-empty">
            <small class="product-help">Leave blank to auto-generate from name.</small>
            @error('slug')
                <small class="product-error">{{ $message }}</small>
            @enderror
        </div>
    </div>

    <div class="product-row product-row-two">
        <div class="product-field">
            <label for="product-price">Price</label>
            <input id="product-price" type="number" name="price" class="product-input @error('price') is-invalid @enderror" step="0.01" min="0" value="{{ old('price', $product?->price) }}" required>
            <small class="product-help">Set the regular price (KES).</small>
            @error('price')
                <small class="product-error">{{ $message }}</small>
            @enderror
        </div>

        <div class="product-field">
            <label for="product-sale-price">Sale Price (optional)</label>
            <input id="product-sale-price" type="number" name="sale_price" class="product-input @error('sale_price') is-invalid @enderror" step="0.01" min="0" value="{{ old('sale_price', $product?->sale_price) }}">
            <small class="product-help">Discounted price if on sale.</small>
            @error('sale_price')
                <small class="product-error">{{ $message }}</small>
            @enderror
        </div>
    </div>

    <div class="product-row product-row-two">
        <div class="product-field">
            <label for="product-sku">SKU (optional)</label>
            <input id="product-sku" type="text" name="sku" class="product-input @error('sku') is-invalid @enderror" value="{{ old('sku', $product?->sku) }}" placeholder="e.g. WM-1234">
            <small class="product-help">Stock Keeping Unit for inventory tracking.</small>
            @error('sku')
                <small class="product-error">{{ $message }}</small>
            @enderror
        </div>

        <div class="product-field">
            <label for="product-stock">Stock</label>
            <input id="product-stock" type="number" name="stock" class="product-input @error('stock') is-invalid @enderror" min="0" value="{{ old('stock', $product?->stock ?? 0) }}" required>
            <small class="product-help">How many units are available for sale?</small>
            @error('stock')
                <small class="product-error">{{ $message }}</small>
            @enderror
        </div>
    </div>

    <div class="product-field">
        <label for="product-description">Description</label>
        <textarea id="product-description" name="description" class="product-input @error('description') is-invalid @enderror" rows="5" placeholder="Product details, features, etc.">{{ old('description', $product?->description) }}</textarea>
        <small class="product-help">Describe the product and its features.</small>
        @error('description')
            <small class="product-error">{{ $message }}</small>
        @enderror
    </div>

    <div class="product-row product-row-two">
        <div class="product-field">
            <label for="product-image-url">Image URL (optional)</label>
            <input id="product-image-url" type="url" name="image_url" class="product-input @error('image_url') is-invalid @enderror" value="{{ old('image_url', $product?->image_url) }}" placeholder="https://example.com/image.jpg">
            <small class="product-help">Paste a direct image link or upload below.</small>
            @error('image_url')
                <small class="product-error">{{ $message }}</small>
            @enderror
        </div>

        <div class="product-field">
            <label for="product-image-file">Upload Image (optional)</label>
            <input id="product-image-file" type="file" name="image_file" class="product-input @error('image_file') is-invalid @enderror" accept="image/*">
            <small class="product-help">Choose a file to upload a new product image.</small>
            @error('image_file')
                <small class="product-error">{{ $message }}</small>
            @enderror
        </div>
    </div>

    <div class="product-field">
        <label class="product-switch" for="product-active">
            <input id="product-active" type="checkbox" name="is_active" value="1" {{ old('is_active', $product?->is_active ?? true) ? 'checked' : '' }}>
            <span>Active product</span>
        </label>
        <small class="product-help">Inactive products are hidden from the storefront.</small>
    </div>
</div>
