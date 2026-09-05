@extends('layouts.admin')
@section('title', 'Categories')
@section('heading', 'Categories')
@section('subheading', 'Organize products for storefront browsing.')

@section('content')
<div class="ta-page">
    <div class="ta-kpis ta-kpis-3">
        <div class="ta-kpi">
            <div class="ta-kpi-icon is-purple">
                <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z"/></svg>
            </div>
            <div>
                <div class="ta-kpi-label">Total Categories</div>
                <div class="ta-kpi-value">{{ $stats['total'] }}</div>
            </div>
        </div>
        <div class="ta-kpi">
            <div class="ta-kpi-icon is-success">
                <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            </div>
            <div>
                <div class="ta-kpi-label">Active</div>
                <div class="ta-kpi-value">{{ $stats['active'] }}</div>
            </div>
        </div>
        <div class="ta-kpi">
            <div class="ta-kpi-icon is-warning">
                <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
            </div>
            <div>
                <div class="ta-kpi-label">With Products</div>
                <div class="ta-kpi-value">{{ $categories->where('products_count', '>', 0)->count() }}</div>
            </div>
        </div>
    </div>

    <div class="ta-toolbar">
        <div class="flex-1"></div>
        <a href="{{ route('admin.categories.create') }}" class="ta-btn">
            <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
            Add Category
        </a>
    </div>

    <div class="ta-table-card">
        <div class="ta-table-wrap">
            <table class="ta-table">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Slug</th>
                        <th>Products</th>
                        <th>Status</th>
                        <th style="text-align:right;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                @forelse($categories as $category)
                    <tr>
                        <td><span class="ta-name">{{ $category->name }}</span></td>
                        <td><span class="ta-muted" style="font-family:monospace;">{{ $category->slug }}</span></td>
                        <td>
                            <strong>{{ $category->products_count }}</strong>
                            <span class="ta-muted"> products</span>
                        </td>
                        <td>
                            <span class="ta-pill {{ $category->is_active ? 'ta-pill-success' : 'ta-pill-muted' }}">
                                {{ $category->is_active ? 'Active' : 'Inactive' }}
                            </span>
                        </td>
                        <td>
                            <div class="ta-actions">
                                <a href="{{ route('admin.categories.edit', $category) }}" class="ta-btn-outline ta-btn-sm" title="Edit">Edit</a>
                                <form method="POST" action="{{ route('admin.categories.destroy', $category) }}" style="margin:0;">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="ta-btn-danger ta-btn-sm" title="Delete" onclick="return confirm('Delete {{ addslashes($category->name) }}?')">Delete</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="ta-empty">No categories yet.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
        <div class="ta-table-footer">
            <span class="ta-muted">{{ $categories->total() }} total categories</span>
            {{ $categories->links() }}
        </div>
    </div>
</div>
@endsection
