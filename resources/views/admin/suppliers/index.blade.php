@extends('layouts.admin')
@section('title', 'Suppliers')
@section('heading', 'Suppliers')
@section('subheading', 'Procurement suppliers (separate from marketplace vendors)')

@section('content')
<div class="ta-page">
    <div class="ta-toolbar">
        <form method="GET" style="display:contents;">
            <div class="ta-field" style="flex:2;">
                <label>Search</label>
                <input type="text" name="q" value="{{ request('q') }}" placeholder="Search suppliers">
            </div>
            <button class="ta-btn" type="submit">Search</button>
        </form>
        <a class="ta-btn" href="{{ route('admin.suppliers.create') }}">Add supplier</a>
    </div>

    <div class="ta-table-card">
        <div class="ta-table-wrap">
            <table class="ta-table">
                <thead>
                    <tr>
                        <th>Supplier</th>
                        <th>Phone</th>
                        <th>Products</th>
                        <th>Purchases</th>
                        <th>Status</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($suppliers as $supplier)
                        <tr>
                            <td>
                                <div class="ta-name">{{ $supplier->name }}</div>
                                @if($supplier->company_name)<div class="ta-muted">{{ $supplier->company_name }}</div>@endif
                            </td>
                            <td>{{ $supplier->phone ?: '—' }}</td>
                            <td>{{ $supplier->products_count }}</td>
                            <td>{{ $supplier->purchases_count }}</td>
                            <td>
                                <span class="ta-pill {{ $supplier->is_active ? 'ta-pill-success' : 'ta-pill-muted' }}">
                                    {{ $supplier->is_active ? 'Active' : 'Inactive' }}
                                </span>
                            </td>
                            <td>
                                <div class="ta-actions">
                                    <a class="ta-btn-outline ta-btn-sm" href="{{ route('admin.suppliers.edit', $supplier) }}">Edit</a>
                                    <form method="POST" action="{{ route('admin.suppliers.destroy', $supplier) }}" onsubmit="return confirm('Remove or deactivate this supplier?')">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="ta-btn-danger ta-btn-sm">Delete</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="ta-empty">No suppliers yet.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="ta-table-footer">{{ $suppliers->links() }}</div>
    </div>
</div>
@endsection
