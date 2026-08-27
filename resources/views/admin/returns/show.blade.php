@extends('layouts.admin')
@section('title', 'Return '.$return->return_number)
@section('heading', $return->return_number)
@section('subheading', 'Refund processed for '.$return->order?->order_number)

@section('content')
    <div class="card">
        <p>Cashier: <strong>{{ $return->user?->name ?: '—' }}</strong></p>
        <p>Method: {{ ucwords(str_replace('_', ' ', $return->refund_method)) }}</p>
        <p>Reason: {{ $return->reason }}</p>
        <p>Total refunded: <strong>KES {{ number_format((float) $return->total, 2) }}</strong></p>
        <div class="table-wrap" style="margin-top:16px;">
            <table class="admin-table">
                <thead><tr><th>Product</th><th>Qty</th><th>Amount</th></tr></thead>
                <tbody>
                @foreach($return->items as $item)
                    <tr>
                        <td>{{ $item->orderItem?->product_name ?: $item->product?->name }}</td>
                        <td>{{ $item->quantity }}</td>
                        <td>KES {{ number_format((float) $item->total, 2) }}</td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
        <div class="row-actions" style="margin-top:16px;">
            <a class="btn btn-secondary" href="{{ route('admin.returns.index') }}">All returns</a>
            @if($return->order)
                <a class="btn btn-secondary" href="{{ route('admin.orders.show', $return->order) }}">View sale</a>
            @endif
        </div>
    </div>
@endsection
