@extends('layouts.admin')
@section('title', 'Vendors')
@section('content')
    <h2>Vendors</h2>
    <a class="btn" href="{{ route('admin.vendors.create') }}">New vendor</a>
    <div class="card" style="margin-top:12px;">
        <table>
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Phone</th>
                    <th>Commission</th>
                    <th>Products</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
            @forelse($vendors as $vendor)
                <tr>
                    <td>{{ $vendor->name }}</td>
                    <td>{{ $vendor->email ?: 'N/A' }}</td>
                    <td>{{ $vendor->phone ?: 'N/A' }}</td>
                    <td>{{ number_format((float) $vendor->commission_rate, 2) }}%</td>
                    <td>{{ $vendor->products_count }}</td>
                    <td>{{ $vendor->is_active ? 'Active' : 'Inactive' }}</td>
                    <td>
                        <a class="btn btn-secondary" href="{{ route('admin.vendors.edit', $vendor) }}">Edit</a>
                        <form method="POST" action="{{ route('admin.vendors.destroy', $vendor) }}">
                            @csrf @method('DELETE')
                            <button type="submit" class="btn" onclick="return confirm('Delete vendor?')">Delete</button>
                        </form>
                    </td>
                </tr>
            @empty
                <tr><td colspan="7">No vendors yet.</td></tr>
            @endforelse
            </tbody>
        </table>
        {{ $vendors->links() }}
    </div>
@endsection
