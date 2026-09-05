@extends('layouts.admin')
@section('title', 'Brands')
@section('heading', 'Brands')
@section('subheading', 'Product brands for inventory')

@section('content')
<div class="ta-page">
    <x-admin.list-card title="Add brand">
        <form method="POST" action="{{ route('admin.brands.store') }}" class="admin-form-grid">
            @csrf
            <div class="ta-field">
                <label>Name *</label>
                <input type="text" name="name" value="{{ old('name') }}" required>
            </div>
            <div class="ta-field">
                <label>Short description</label>
                <input type="text" name="short_description" value="{{ old('short_description') }}">
            </div>
            <div style="grid-column:1/-1;">
                <button type="submit" class="ta-btn">Save brand</button>
            </div>
        </form>
    </x-admin.list-card>

    <div class="ta-toolbar">
        <form method="GET" style="display:contents;">
            <div class="ta-field" style="flex:2;">
                <label>Search</label>
                <input type="text" name="q" value="{{ request('q') }}" placeholder="Search brands">
            </div>
            <button class="ta-btn" type="submit">Search</button>
        </form>
    </div>

    <div class="ta-table-card">
        <div class="ta-table-wrap">
            <table class="ta-table">
                <thead>
                    <tr>
                        <th>Brand</th>
                        <th>Products</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($brands as $brand)
                        <tr>
                            <td>
                                <div class="ta-name">{{ $brand->name }}</div>
                                @if($brand->short_description)<div class="ta-muted">{{ $brand->short_description }}</div>@endif
                            </td>
                            <td>{{ $brand->products_count }}</td>
                            <td>
                                <div class="ta-actions">
                                    <form method="POST" action="{{ route('admin.brands.destroy', $brand) }}" onsubmit="return confirm('Delete this brand?')">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="ta-btn-danger ta-btn-sm">Delete</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="3" class="ta-empty">No brands yet.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="ta-table-footer">{{ $brands->links() }}</div>
    </div>
</div>
@endsection
