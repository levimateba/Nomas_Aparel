@extends('layouts.admin')
@section('title', $transfer->transfer_number)
@section('heading', 'Transfer '.$transfer->transfer_number)
@section('subheading', 'Transfer details and audit trail')

@section('content')
<div class="page-head">
    <h2>{{ $transfer->transfer_number }}</h2>
    <a class="btn btn-secondary" href="{{ route('admin.stock-transfers.index') }}">Back</a>
</div>
<div class="card" style="padding:16px;">
    <div class="admin-form-grid">
        <div><div class="muted">Date</div><strong>{{ $transfer->created_at?->format('d M Y H:i') }}</strong></div>
        <div><div class="muted">User</div><strong>{{ $transfer->user?->name }}</strong></div>
        <div><div class="muted">From</div><strong>{{ $transfer->fromLocation?->name }}</strong></div>
        <div><div class="muted">To</div><strong>{{ $transfer->toLocation?->name }}</strong></div>
        <div><div class="muted">Status</div><strong>{{ ucfirst($transfer->status) }}</strong></div>
        <div><div class="muted">Reason</div><strong>{{ $transfer->reason ?: '—' }}</strong></div>
        <div style="grid-column:1/-1;"><div class="muted">Notes</div><strong>{{ $transfer->notes ?: '—' }}</strong></div>
        @if($transfer->completed_at)
            <div><div class="muted">Completed</div><strong>{{ $transfer->completed_at->format('d M Y H:i') }} by {{ $transfer->completedByUser?->name }}</strong></div>
        @endif
    </div>
    <div class="table-wrap" style="margin-top:16px;">
        <table>
            <thead><tr><th>Product</th><th>Variant</th><th style="text-align:right;">Qty</th></tr></thead>
            <tbody>
            @foreach($transfer->items as $item)
                <tr>
                    <td>{{ $item->product?->name }}</td>
                    <td>{{ $item->variant?->name ?: '—' }}</td>
                    <td style="text-align:right;">{{ $item->quantity }}</td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection
