@extends('layouts.admin')
@section('title', 'Product Types')
@section('heading', 'Product Types / Styles')
@section('subheading', 'T-Shirt, Jeans, Dress, and other apparel styles')

@section('content')
<div class="ta-page">
    <x-admin.list-card title="Add product type">
        <form method="POST" action="{{ route('admin.product-styles.store') }}" class="admin-form-grid">
            @csrf
            <div class="ta-field">
                <label>Name *</label>
                <input type="text" name="name" class="ta-input" value="{{ old('name') }}" required placeholder="e.g. Hoodie">
            </div>
            <div style="grid-column:1/-1;">
                <button type="submit" class="ta-btn">Save type</button>
            </div>
        </form>
    </x-admin.list-card>

    <div class="ta-toolbar">
        <form method="GET" style="display:contents;">
            <div class="ta-field" style="flex:2;">
                <label>Search</label>
                <input type="text" name="q" class="ta-input" value="{{ request('q') }}" placeholder="Search types">
            </div>
            <button class="ta-btn" type="submit">Search</button>
        </form>
    </div>

    <div class="ta-table-card">
        <div class="ta-table-wrap">
            <table class="ta-table">
                <thead>
                    <tr>
                        <th>Type / Style</th>
                        <th>Products</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($styles as $style)
                        <tr>
                            <td><div class="ta-name">{{ $style->name }}</div></td>
                            <td>{{ $style->products_count }}</td>
                            <td>
                                <div class="ta-actions">
                                    <form method="POST" action="{{ route('admin.product-styles.destroy', $style) }}" onsubmit="return confirm('Delete this product type?')">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="ta-btn-danger ta-btn-sm">Delete</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="3" class="ta-empty">No product types yet.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="ta-table-footer">{{ $styles->links() }}</div>
    </div>
</div>
@endsection
