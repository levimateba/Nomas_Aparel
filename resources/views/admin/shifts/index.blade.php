@extends('layouts.admin')
@section('title', 'Cashier Shifts')
@section('heading', 'Cashier Shifts')
@section('subheading', 'Open a shift before selling, then close and reconcile cash.')

@section('content')
<div class="ta-page">
    @if($current)
        <x-admin.list-card title="Current open shift" desc="Opened {{ $current->opened_at?->format('d M Y H:i') }} · Opening cash KES {{ number_format((float) $current->opening_cash, 2) }}">
            <x-slot:actions>
                <a class="ta-btn-outline" href="{{ route('admin.shifts.show', $current) }}">View details</a>
            </x-slot:actions>
            <form method="POST" action="{{ route('admin.shifts.close', $current) }}" class="admin-form-grid">
                @csrf
                <div class="ta-field">
                    <label>Actual cash in drawer</label>
                    <input type="number" step="0.01" min="0" name="actual_cash" required>
                </div>
                <div class="ta-field">
                    <label>Closing notes</label>
                    <input name="notes" placeholder="Optional">
                </div>
                <div style="grid-column:1/-1;">
                    <button type="submit" class="ta-btn">Close shift</button>
                </div>
            </form>
        </x-admin.list-card>
    @else
        <x-admin.list-card title="Open a new shift">
            <form method="POST" action="{{ route('admin.shifts.store') }}" class="admin-form-grid">
                @csrf
                <div class="ta-field">
                    <label>Opening cash</label>
                    <input type="number" step="0.01" min="0" name="opening_cash" value="0" required>
                </div>
                <div class="ta-field">
                    <label>Notes</label>
                    <input name="notes">
                </div>
                <div style="grid-column:1/-1;">
                    <button type="submit" class="ta-btn">Open shift</button>
                </div>
            </form>
        </x-admin.list-card>
    @endif

    <div class="ta-table-card">
        <div class="ta-table-wrap">
            <table class="ta-table">
                <thead>
                    <tr>
                        <th>Cashier</th>
                        <th>Opened</th>
                        <th>Closed</th>
                        <th>Expected</th>
                        <th>Actual</th>
                        <th>Diff</th>
                        <th>Status</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                @forelse($shifts as $shift)
                    <tr>
                        <td>{{ $shift->user?->name }}</td>
                        <td>{{ $shift->opened_at?->format('d M H:i') }}</td>
                        <td>{{ $shift->closed_at?->format('d M H:i') ?: '—' }}</td>
                        <td>KES {{ number_format((float) ($shift->expected_cash ?? $shift->opening_cash), 2) }}</td>
                        <td>{{ $shift->actual_cash !== null ? 'KES '.number_format((float) $shift->actual_cash, 2) : '—' }}</td>
                        <td style="color: {{ ($shift->difference ?? 0) < 0 ? '#b91c1c' : '#15803d' }}">
                            {{ $shift->difference !== null ? 'KES '.number_format((float) $shift->difference, 2) : '—' }}
                        </td>
                        <td>
                            @php
                                $pillClass = ($shift->status === 'open') ? 'ta-pill-success' : 'ta-pill-muted';
                            @endphp
                            <span class="ta-pill {{ $pillClass }}">{{ ucfirst($shift->status) }}</span>
                        </td>
                        <td>
                            <div class="ta-actions">
                                <a class="ta-btn-outline ta-btn-sm" href="{{ route('admin.shifts.show', $shift) }}">View</a>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="8" class="ta-empty">No shifts yet.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
        <div class="ta-table-footer">{{ $shifts->links() }}</div>
    </div>
</div>
@endsection
