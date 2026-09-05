@extends('layouts.admin')
@section('title', 'Vendors')
@section('heading', 'Vendors')
@section('subheading', 'Manage sellers, commissions, and catalog owners.')

@section('content')
<div class="ta-page">
    <div class="ta-kpis">
        <div class="ta-kpi">
            <div class="ta-kpi-icon is-purple">
                <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
            </div>
            <div>
                <div class="ta-kpi-label">Total Vendors</div>
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
    </div>

    <div class="ta-toolbar">
        <div class="flex-1"></div>
        <a class="ta-btn" href="{{ route('admin.vendors.create') }}">+ Add Vendor</a>
    </div>

    <div class="ta-table-card">
        <div class="ta-table-wrap">
            <table class="ta-table">
                <thead>
                    <tr><th>Name</th><th>Email</th><th>Phone</th><th>Commission</th><th>Products</th><th>Status</th><th>Actions</th></tr>
                </thead>
                <tbody>
                @forelse($vendors as $vendor)
                    <tr>
                        <td><div class="ta-name">{{ $vendor->name }}</div></td>
                        <td>{{ $vendor->email ?: '—' }}</td>
                        <td>{{ $vendor->phone ?: '—' }}</td>
                        <td>{{ number_format((float) $vendor->commission_rate, 2) }}%</td>
                        <td>{{ $vendor->products_count }}</td>
                        <td><span class="ta-pill {{ $vendor->is_active ? 'ta-pill-success' : 'ta-pill-muted' }}">{{ $vendor->is_active ? 'Active' : 'Inactive' }}</span></td>
                        <td>
                            <div class="ta-actions">
                                <a class="ta-btn-outline ta-btn-sm" href="{{ route('admin.vendors.edit', $vendor) }}">Edit</a>
                                <form method="POST" action="{{ route('admin.vendors.destroy', $vendor) }}">
                                    @csrf @method('DELETE')
                                    <button class="ta-btn-danger ta-btn-sm" type="submit" onclick="return confirm('Delete vendor?')">Delete</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="ta-empty">No vendors yet.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
        <div class="ta-table-footer">{{ $vendors->links() }}</div>
    </div>
</div>
@endsection
