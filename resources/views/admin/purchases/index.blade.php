@extends('layouts.admin')
@section('title', 'Purchases')
@section('heading', 'Purchases / Receive Stock')
@section('subheading', 'Record supplier deliveries and increase inventory')

@section('content')
<div class="ta-page">
    <div class="ta-toolbar">
        <p class="ta-muted" style="margin:0;flex:1;">Receiving stock updates product quantities and buying price.</p>
        <a class="ta-btn" href="{{ route('admin.purchases.create') }}">Receive stock</a>
    </div>

    <div class="ta-table-card">
        <div class="ta-table-wrap">
            <table class="ta-table">
                <thead>
                    <tr>
                        <th>Purchase #</th>
                        <th>Supplier</th>
                        <th>Date</th>
                        <th style="text-align:right;">Total</th>
                        <th>Payment</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($purchases as $purchase)
                        <tr>
                            <td><div class="ta-name">{{ $purchase->purchase_number }}</div></td>
                            <td>{{ $purchase->supplier?->name ?: '—' }}</td>
                            <td>{{ $purchase->purchase_date->format('d M Y') }}</td>
                            <td style="text-align:right;">KES {{ number_format((float)$purchase->total, 2) }}</td>
                            <td>
                                @php
                                    $pillClass = match($purchase->payment_status) {
                                        'paid' => 'ta-pill-success',
                                        'partial' => 'ta-pill-warning',
                                        default => 'ta-pill-muted',
                                    };
                                @endphp
                                <span class="ta-pill {{ $pillClass }}">{{ ucfirst($purchase->payment_status) }}</span>
                            </td>
                            <td>
                                <div class="ta-actions">
                                    <a class="ta-btn-outline ta-btn-sm" href="{{ route('admin.purchases.show', $purchase) }}">View</a>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="ta-empty">No purchases yet.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="ta-table-footer">{{ $purchases->links() }}</div>
    </div>
</div>
@endsection
