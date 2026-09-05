@extends('layouts.admin')
@section('title', 'Purchase Orders')
@section('heading', 'Purchase Orders')
@section('subheading', 'Draft, approve, then receive stock')

@section('content')
<div class="ta-page">
    <div class="ta-toolbar">
        <div class="flex-1"></div>
        <a class="ta-btn" href="{{ route('admin.purchase-orders.create') }}">New purchase order</a>
    </div>

    <div class="ta-table-card">
        <div class="ta-table-wrap">
            <table class="ta-table">
                <thead>
                    <tr>
                        <th>PO #</th>
                        <th>Supplier</th>
                        <th>Date</th>
                        <th style="text-align:right;">Total</th>
                        <th>Status</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($orders as $order)
                        <tr>
                            <td><div class="ta-name">{{ $order->po_number }}</div></td>
                            <td>{{ $order->supplier?->name ?: '—' }}</td>
                            <td>{{ $order->order_date->format('d M Y') }}</td>
                            <td style="text-align:right;">KES {{ number_format((float)$order->total, 2) }}</td>
                            <td><span class="ta-pill ta-pill-info">{{ str_replace('_',' ', $order->status) }}</span></td>
                            <td>
                                <div class="ta-actions">
                                    <a class="ta-btn-outline ta-btn-sm" href="{{ route('admin.purchase-orders.show', $order) }}">Open</a>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="ta-empty">No purchase orders yet.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="ta-table-footer">{{ $orders->links() }}</div>
    </div>
</div>
@endsection
