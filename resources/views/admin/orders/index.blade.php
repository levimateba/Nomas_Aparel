@extends('layouts.admin')
@section('title', 'Orders')
@section('content')
    <h2>Orders</h2>
    <div class="dashboard-grid" style="display:grid;grid-template-columns:repeat(auto-fit,minmax(180px,1fr));gap:12px;margin:10px 0 14px;">
        <div class="card"><strong>Today</strong><div>{{ $summaries['today']['count'] }} orders</div><div>KES {{ number_format($summaries['today']['revenue'], 2) }}</div></div>
        <div class="card"><strong>This Week</strong><div>{{ $summaries['week']['count'] }} orders</div><div>KES {{ number_format($summaries['week']['revenue'], 2) }}</div></div>
        <div class="card"><strong>This Month</strong><div>{{ $summaries['month']['count'] }} orders</div><div>KES {{ number_format($summaries['month']['revenue'], 2) }}</div></div>
    </div>
    <form method="GET" action="{{ route('admin.orders.index') }}" style="margin: 12px 0; display:flex; gap:8px; flex-wrap:wrap;">
        <input type="text" name="q" value="{{ request('q') }}" placeholder="Order # / customer / email" style="max-width:280px;">
        <select name="status" style="max-width:180px;">
            <option value="">All status</option>
            @foreach(['pending','processing','paid','shipped','delivered','cancellation_requested','cancelled'] as $status)
                <option value="{{ $status }}" {{ request('status') === $status ? 'selected' : '' }}>{{ ucfirst($status) }}</option>
            @endforeach
        </select>
        <select name="payment_method" style="max-width:210px;">
            <option value="">All payment methods</option>
            @foreach(['cash_on_delivery','mobile_money','bank_transfer','card'] as $method)
                <option value="{{ $method }}" {{ request('payment_method') === $method ? 'selected' : '' }}>{{ ucwords(str_replace('_', ' ', $method)) }}</option>
            @endforeach
        </select>
        <input type="date" name="from_date" value="{{ request('from_date') }}" style="max-width:180px;">
        <input type="date" name="to_date" value="{{ request('to_date') }}" style="max-width:180px;">
        <button class="btn btn-secondary" type="submit">Filter</button>
        @if(request()->filled('q') || request()->filled('status') || request()->filled('payment_method') || request()->filled('from_date') || request()->filled('to_date'))
            <a class="btn btn-secondary" href="{{ route('admin.orders.index') }}">Clear</a>
        @endif
        <a class="btn btn-secondary" href="{{ route('admin.orders.export.csv', request()->query()) }}">Export CSV</a>
    </form>

    <div class="card">
        <form method="POST" action="{{ route('admin.orders.bulk-update') }}">
            @csrf
            <div style="display:flex;gap:8px;align-items:center;flex-wrap:wrap;margin-bottom:10px;">
                <select name="status" required style="max-width:230px;">
                    <option value="">Set status for selected</option>
                    @foreach(['pending','processing','paid','shipped','delivered','cancellation_requested','cancelled'] as $status)
                        <option value="{{ $status }}">{{ ucfirst($status) }}</option>
                    @endforeach
                </select>
                <button class="btn btn-secondary" type="submit" onclick="return confirm('Update selected orders?')">Apply</button>
            </div>
        <table>
            <thead>
            <tr>
                <th><input type="checkbox" id="select-all-orders"></th>
                <th>Order #</th>
                <th>Customer</th>
                <th>Total</th>
                <th>Payment</th>
                <th>Status</th>
                <th>Date</th>
                <th>Actions</th>
            </tr>
            </thead>
            <tbody>
            @forelse($orders as $order)
                <tr>
                    <td><input type="checkbox" name="order_ids[]" value="{{ $order->id }}" class="order-check"></td>
                    <td>{{ $order->order_number }}</td>
                    <td>{{ $order->customer_name }}<br><small>{{ $order->customer_email }}</small></td>
                    <td>KES {{ number_format((float) $order->total_amount, 2) }}</td>
                    <td>{{ ucwords(str_replace('_', ' ', $order->payment_method)) }}</td>
                    <td>{{ ucfirst($order->status) }}</td>
                    <td>{{ $order->created_at->format('Y-m-d H:i') }}</td>
                    <td>
                        <a class="btn btn-secondary" href="{{ route('admin.orders.show', $order) }}">View</a>
                    </td>
                </tr>
            @empty
                <tr><td colspan="8">No orders yet.</td></tr>
            @endforelse
            </tbody>
        </table>
        </form>
        {{ $orders->links() }}
    </div>
    <script>
        (function () {
            const all = document.getElementById('select-all-orders');
            if (!all) return;
            all.addEventListener('change', function () {
                document.querySelectorAll('.order-check').forEach(function (el) {
                    el.checked = all.checked;
                });
            });
        })();
    </script>
@endsection
