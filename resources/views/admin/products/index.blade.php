@extends('layouts.admin')
@section('title', 'Products')
@section('content')
    <h2>Products</h2>
    <a class="btn" href="{{ route('admin.products.create') }}">New product</a>
    <form method="GET" action="{{ route('admin.products.index') }}" style="margin: 12px 0; display:flex; gap:8px; flex-wrap:wrap;">
        <input type="text" name="q" value="{{ request('q') }}" placeholder="Search by name or SKU" style="max-width:260px;">
        <select name="stock" style="max-width:180px;">
            <option value="">All stock</option>
            <option value="low" {{ request('stock') === 'low' ? 'selected' : '' }}>Low stock (<=5)</option>
            <option value="out" {{ request('stock') === 'out' ? 'selected' : '' }}>Out of stock</option>
        </select>
        <select name="status" style="max-width:180px;">
            <option value="">All status</option>
            <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Active</option>
            <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>Inactive</option>
        </select>
        <button class="btn btn-secondary" type="submit">Filter</button>
        @if(request()->filled('q') || request()->filled('stock') || request()->filled('status'))
            <a class="btn btn-secondary" href="{{ route('admin.products.index') }}">Clear</a>
        @endif
        <a class="btn btn-secondary" href="{{ route('admin.products.export.csv', request()->query()) }}">Export CSV</a>
    </form>

    <div class="card" style="margin-top: 14px;">
        <form method="POST" action="{{ route('admin.products.bulk-update') }}">
            @csrf
            <div style="display:flex;gap:8px;align-items:center;flex-wrap:wrap;margin-bottom:10px;">
                <select name="action" required style="max-width:220px;">
                    <option value="">Bulk action</option>
                    <option value="activate">Activate selected</option>
                    <option value="deactivate">Deactivate selected</option>
                    <option value="delete">Delete selected</option>
                </select>
                <button class="btn btn-secondary" type="submit" onclick="return confirm('Apply bulk action to selected products?')">Apply</button>
            </div>
        <table>
            <thead>
            <tr>
                <th><input type="checkbox" id="select-all-products"></th>
                <th>Image</th>
                <th>Name</th>
                <th>Vendor</th>
                <th>Category</th>
                <th>Price</th>
                <th>Stock</th>
                <th>Status</th>
                <th>Actions</th>
            </tr>
            </thead>
            <tbody>
            @forelse($products as $product)
                <tr>
                    <td><input type="checkbox" name="product_ids[]" value="{{ $product->id }}" class="product-check"></td>
                    <td>
                        <img src="{{ $product->image_url ?: 'https://via.placeholder.com/72x72?text=No+Img' }}" alt="{{ $product->name }}" style="width:48px;height:48px;object-fit:cover;border-radius:6px;border:1px solid #e5e7eb;">
                    </td>
                    <td>{{ $product->name }}</td>
                    <td>{{ $product->vendor?->name ?: 'In-house' }}</td>
                    <td>{{ $product->category?->name ?: 'Uncategorized' }}</td>
                    <td>
                        KES {{ number_format((float) ($product->sale_price ?: $product->price), 2) }}
                        @if($product->sale_price)
                            <br><small style="color:#6b7280;">Was {{ number_format((float) $product->price, 2) }}</small>
                        @endif
                    </td>
                    <td>
                        {{ $product->stock }}
                        @if($product->stock === 0)
                            <span style="display:inline-block;margin-left:6px;padding:2px 8px;border-radius:999px;background:#fee2e2;color:#b91c1c;font-size:.75rem;">Out</span>
                        @elseif($product->stock <= 5)
                            <span style="display:inline-block;margin-left:6px;padding:2px 8px;border-radius:999px;background:#fef3c7;color:#b45309;font-size:.75rem;">Low</span>
                        @endif
                    </td>
                    <td>{{ $product->is_active ? 'Active' : 'Inactive' }}</td>
                    <td>
                        <a class="btn btn-secondary" href="{{ route('shop.show', $product) }}" target="_blank" rel="noopener">View</a>
                        <a class="btn btn-secondary" href="{{ route('admin.products.edit', $product) }}">Edit</a>
                        <form method="POST" action="{{ route('admin.products.destroy', $product) }}">
                            @csrf
                            @method('DELETE')
                            <button class="btn" type="submit" onclick="return confirm('Delete this product?')">Delete</button>
                        </form>
                    </td>
                </tr>
            @empty
                <tr><td colspan="9">No products yet.</td></tr>
            @endforelse
            </tbody>
        </table>
        </form>
        {{ $products->links() }}
    </div>
    <script>
        (function () {
            const all = document.getElementById('select-all-products');
            if (!all) return;
            all.addEventListener('change', function () {
                document.querySelectorAll('.product-check').forEach(function (el) {
                    el.checked = all.checked;
                });
            });
        })();
    </script>
@endsection
