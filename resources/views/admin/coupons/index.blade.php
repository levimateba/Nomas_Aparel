@extends('layouts.admin')
@section('title', 'Coupons')
@section('content')
    <h2>Coupons</h2>
    <a class="btn" href="{{ route('admin.coupons.create') }}">New coupon</a>
    <div class="card" style="margin-top:12px;">
        <table>
            <thead>
                <tr>
                    <th>Code</th>
                    <th>Type</th>
                    <th>Value</th>
                    <th>Usage</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
            @forelse($coupons as $coupon)
                <tr>
                    <td>{{ $coupon->code }}</td>
                    <td>{{ ucfirst($coupon->type) }}</td>
                    <td>{{ $coupon->type === 'percent' ? $coupon->value.'%' : 'KES '.number_format((float)$coupon->value,2) }}</td>
                    <td>{{ $coupon->used_count }}{{ $coupon->usage_limit ? ' / '.$coupon->usage_limit : '' }}</td>
                    <td>{{ $coupon->is_active ? 'Active' : 'Inactive' }}</td>
                    <td>
                        <a class="btn btn-secondary" href="{{ route('admin.coupons.edit', $coupon) }}">Edit</a>
                        <form method="POST" action="{{ route('admin.coupons.destroy', $coupon) }}">
                            @csrf @method('DELETE')
                            <button class="btn" type="submit" onclick="return confirm('Delete coupon?')">Delete</button>
                        </form>
                    </td>
                </tr>
            @empty
                <tr><td colspan="6">No coupons yet.</td></tr>
            @endforelse
            </tbody>
        </table>
        {{ $coupons->links() }}
    </div>
@endsection
