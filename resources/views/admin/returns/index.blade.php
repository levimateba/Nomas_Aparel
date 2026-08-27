@extends('layouts.admin')
@section('title', 'Returns')
@section('heading', 'Returns')
@section('subheading', 'Refund POS items and restore stock.')

@section('content')
    <div class="admin-toolbar">
        <div></div>
        <a class="btn" href="{{ route('admin.returns.create') }}">New Return</a>
    </div>
    <div class="card">
        <div class="table-wrap">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>Return #</th>
                        <th>Sale</th>
                        <th>Cashier</th>
                        <th>Total</th>
                        <th>Date</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                @forelse($returns as $item)
                    <tr>
                        <td><strong>{{ $item->return_number }}</strong></td>
                        <td>{{ $item->order?->order_number }}</td>
                        <td>{{ $item->user?->name ?: '—' }}</td>
                        <td>KES {{ number_format((float) $item->total, 2) }}</td>
                        <td>{{ $item->created_at?->format('d M Y H:i') }}</td>
                        <td class="row-actions"><a class="btn btn-secondary" href="{{ route('admin.returns.show', $item) }}">View</a></td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="empty-cell">No returns yet.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
        <div style="margin-top:12px;">{{ $returns->links() }}</div>
    </div>
@endsection
