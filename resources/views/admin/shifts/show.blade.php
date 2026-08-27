@extends('layouts.admin')
@section('title', 'Shift details')
@section('heading', 'Shift details')
@section('subheading', ($shift->user?->name ?: 'Cashier').' · '.$shift->opened_at?->format('d M Y H:i'))

@section('content')
    <div class="admin-kpis">
        <div class="admin-kpi"><span>Tickets</span><strong>{{ $performance['transactions'] }}</strong></div>
        <div class="admin-kpi"><span>Net sales</span><strong>KES {{ number_format($performance['net_sales'], 2) }}</strong></div>
        <div class="admin-kpi"><span>Returns</span><strong>KES {{ number_format($performance['returns'], 2) }}</strong></div>
        <div class="admin-kpi"><span>Expected cash</span><strong>KES {{ number_format($performance['expected_cash'], 2) }}</strong></div>
        <div class="admin-kpi"><span>Actual cash</span><strong>{{ $performance['actual_cash'] === null ? '—' : 'KES '.number_format($performance['actual_cash'], 2) }}</strong></div>
        <div class="admin-kpi"><span>Variance</span><strong>{{ $performance['variance'] === null ? '—' : 'KES '.number_format($performance['variance'], 2) }}</strong></div>
    </div>

    <div class="card" style="margin-bottom:16px;">
        <h3 style="margin-top:0;">Payments</h3>
        <p>Cash: KES {{ number_format($performance['cash'], 2) }}</p>
        <p>M-Pesa: KES {{ number_format($performance['mobile_money'], 2) }}</p>
        <p>Card: KES {{ number_format($performance['card'], 2) }}</p>
        <p>Bank: KES {{ number_format($performance['bank'], 2) }}</p>
        @if($shift->notes)
            <p class="muted">{{ $shift->notes }}</p>
        @endif
    </div>

    <div class="card">
        <h3 style="margin-top:0;">Sales on this shift</h3>
        <div class="table-wrap">
            <table class="admin-table">
                <thead><tr><th>Ticket</th><th>Customer</th><th>Payment</th><th>Total</th></tr></thead>
                <tbody>
                @forelse($shift->orders as $order)
                    <tr>
                        <td><a href="{{ route('admin.orders.show', $order) }}"><strong>{{ $order->order_number }}</strong></a></td>
                        <td>{{ $order->customer_name }}</td>
                        <td>{{ ucwords(str_replace('_', ' ', $order->payment_method)) }}</td>
                        <td>KES {{ number_format((float) $order->total_amount, 2) }}</td>
                    </tr>
                @empty
                    <tr><td colspan="4" class="empty-cell">No sales on this shift yet.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection
