@extends('layouts.admin')
@section('title', 'Coupons')
@section('heading', 'Coupons')
@section('subheading', 'Create and track discount codes.')

@section('content')
    <div class="admin-kpis">
        <div class="admin-kpi"><span>Total Coupons</span><strong>{{ $stats['total'] }}</strong></div>
        <div class="admin-kpi"><span>Active</span><strong>{{ $stats['active'] }}</strong></div>
        <div class="admin-kpi"><span>Total Uses</span><strong>{{ $stats['uses'] }}</strong></div>
    </div>
    <div class="admin-toolbar">
        <div></div>
        <a class="btn" href="{{ route('admin.coupons.create') }}">+ Add Coupon</a>
    </div>
    <div class="card">
        <div class="table-wrap">
            <table class="admin-table">
                <thead>
                    <tr><th>Code</th><th>Type</th><th>Value</th><th>Usage</th><th>Status</th><th>Actions</th></tr>
                </thead>
                <tbody>
                @forelse($coupons as $coupon)
                    <tr>
                        <td><strong>{{ $coupon->code }}</strong></td>
                        <td>{{ ucfirst($coupon->type) }}</td>
                        <td>{{ $coupon->type === 'percent' ? $coupon->value.'%' : 'KES '.number_format((float)$coupon->value, 2) }}</td>
                        <td>{{ $coupon->used_count }}{{ $coupon->usage_limit ? ' / '.$coupon->usage_limit : '' }}</td>
                        <td><span class="status-pill {{ $coupon->is_active ? 'on' : 'off' }}">{{ $coupon->is_active ? 'Active' : 'Inactive' }}</span></td>
                        <td class="row-actions">
                            <a class="btn btn-secondary" href="{{ route('admin.coupons.edit', $coupon) }}">Edit</a>
                            <form method="POST" action="{{ route('admin.coupons.destroy', $coupon) }}">
                                @csrf @method('DELETE')
                                <button class="btn btn-danger" type="submit" onclick="return confirm('Delete coupon?')">Delete</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="empty-cell">No coupons yet. Create one to start offering discounts.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
        <div style="margin-top:12px;">{{ $coupons->links() }}</div>
    </div>
@endsection
