@extends('layouts.admin')
@section('title', 'Coupons')
@section('heading', 'Coupons')
@section('subheading', 'Create and track discount codes.')

@section('content')
<div class="ta-page">
    <div class="ta-kpis ta-kpis-3">
        <div class="ta-kpi">
            <div class="ta-kpi-icon is-purple">
                <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/></svg>
            </div>
            <div>
                <div class="ta-kpi-label">Total Coupons</div>
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
            <div class="ta-kpi-icon is-blue">
                <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/></svg>
            </div>
            <div>
                <div class="ta-kpi-label">Total Uses</div>
                <div class="ta-kpi-value">{{ $stats['uses'] }}</div>
            </div>
        </div>
    </div>

    <div class="ta-toolbar">
        <div class="flex-1"></div>
        <a class="ta-btn" href="{{ route('admin.coupons.create') }}">+ Add Coupon</a>
    </div>

    <div class="ta-table-card">
        <div class="ta-table-wrap">
            <table class="ta-table">
                <thead>
                    <tr><th>Code</th><th>Type</th><th>Value</th><th>Usage</th><th>Status</th><th>Actions</th></tr>
                </thead>
                <tbody>
                @forelse($coupons as $coupon)
                    <tr>
                        <td><div class="ta-name">{{ $coupon->code }}</div></td>
                        <td>{{ ucfirst($coupon->type) }}</td>
                        <td>{{ $coupon->type === 'percent' ? $coupon->value.'%' : 'KES '.number_format((float)$coupon->value, 2) }}</td>
                        <td>{{ $coupon->used_count }}{{ $coupon->usage_limit ? ' / '.$coupon->usage_limit : '' }}</td>
                        <td><span class="ta-pill {{ $coupon->is_active ? 'ta-pill-success' : 'ta-pill-muted' }}">{{ $coupon->is_active ? 'Active' : 'Inactive' }}</span></td>
                        <td>
                            <div class="ta-actions">
                                <a class="ta-btn-outline ta-btn-sm" href="{{ route('admin.coupons.edit', $coupon) }}">Edit</a>
                                <form method="POST" action="{{ route('admin.coupons.destroy', $coupon) }}">
                                    @csrf @method('DELETE')
                                    <button class="ta-btn-danger ta-btn-sm" type="submit" onclick="return confirm('Delete coupon?')">Delete</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="ta-empty">No coupons yet. Create one to start offering discounts.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
        <div class="ta-table-footer">{{ $coupons->links() }}</div>
    </div>
</div>
@endsection
