@extends('layouts.admin')
@section('title', 'Returns')
@section('heading', 'Returns')
@section('subheading', 'Refund POS items and restore stock.')

@section('content')
<div class="ta-page">
    <div class="ta-toolbar">
        <div class="flex-1"></div>
        <a class="ta-btn" href="{{ route('admin.returns.create') }}">New Return</a>
    </div>

    <div class="ta-table-card">
        <div class="ta-table-wrap">
            <table class="ta-table">
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
                        <td><div class="ta-name">{{ $item->return_number }}</div></td>
                        <td>{{ $item->order?->order_number }}</td>
                        <td>{{ $item->user?->name ?: '—' }}</td>
                        <td>KES {{ number_format((float) $item->total, 2) }}</td>
                        <td>{{ $item->created_at?->format('d M Y H:i') }}</td>
                        <td>
                            <div class="ta-actions">
                                <a class="ta-btn-outline ta-btn-sm" href="{{ route('admin.returns.show', $item) }}">View</a>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="ta-empty">No returns yet.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
        <div class="ta-table-footer">{{ $returns->links() }}</div>
    </div>
</div>
@endsection
