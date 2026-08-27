@extends('layouts.admin')
@section('title', 'Vendors')
@section('heading', 'Vendors')
@section('subheading', 'Manage sellers, commissions, and catalog owners.')

@section('content')
    <div class="admin-kpis">
        <div class="admin-kpi"><span>Total Vendors</span><strong>{{ $stats['total'] }}</strong></div>
        <div class="admin-kpi"><span>Active</span><strong>{{ $stats['active'] }}</strong></div>
    </div>
    <div class="admin-toolbar">
        <div></div>
        <a class="btn" href="{{ route('admin.vendors.create') }}">+ Add Vendor</a>
    </div>
    <div class="card">
        <div class="table-wrap">
            <table class="admin-table">
                <thead>
                    <tr><th>Name</th><th>Email</th><th>Phone</th><th>Commission</th><th>Products</th><th>Status</th><th>Actions</th></tr>
                </thead>
                <tbody>
                @forelse($vendors as $vendor)
                    <tr>
                        <td><strong>{{ $vendor->name }}</strong></td>
                        <td>{{ $vendor->email ?: '—' }}</td>
                        <td>{{ $vendor->phone ?: '—' }}</td>
                        <td>{{ number_format((float) $vendor->commission_rate, 2) }}%</td>
                        <td>{{ $vendor->products_count }}</td>
                        <td><span class="status-pill {{ $vendor->is_active ? 'on' : 'off' }}">{{ $vendor->is_active ? 'Active' : 'Inactive' }}</span></td>
                        <td class="row-actions">
                            <a class="btn btn-secondary" href="{{ route('admin.vendors.edit', $vendor) }}">Edit</a>
                            <form method="POST" action="{{ route('admin.vendors.destroy', $vendor) }}">
                                @csrf @method('DELETE')
                                <button class="btn btn-danger" type="submit" onclick="return confirm('Delete vendor?')">Delete</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="empty-cell">No vendors yet.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
        <div style="margin-top:12px;">{{ $vendors->links() }}</div>
    </div>
@endsection
