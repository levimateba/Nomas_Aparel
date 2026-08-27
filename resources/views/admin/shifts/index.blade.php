@extends('layouts.admin')
@section('title', 'Cashier Shifts')
@section('heading', 'Cashier Shifts')
@section('subheading', 'Open a shift before selling, then close and reconcile cash.')

@section('content')
    @if($current)
        <div class="card" style="margin-bottom:16px;border-color:#bbf7d0;background:#f0fdf4;">
            <div class="page-head">
                <div>
                    <h2 style="font-size:1rem;margin:0;">Current open shift</h2>
                    <p class="muted">Opened {{ $current->opened_at?->format('d M Y H:i') }} · Opening cash KES {{ number_format((float) $current->opening_cash, 2) }}</p>
                </div>
                <a class="btn btn-secondary" href="{{ route('admin.shifts.show', $current) }}">View details</a>
            </div>
            <form method="POST" action="{{ route('admin.shifts.close', $current) }}" class="admin-form-grid">
                @csrf
                <div>
                    <label>Actual cash in drawer</label>
                    <input type="number" step="0.01" min="0" name="actual_cash" required>
                </div>
                <div>
                    <label>Closing notes</label>
                    <input name="notes" placeholder="Optional">
                </div>
                <div style="grid-column:1/-1;">
                    <button type="submit">Close shift</button>
                </div>
            </form>
        </div>
    @else
        <div class="card" style="margin-bottom:16px;">
            <h3 style="margin-top:0;">Open a new shift</h3>
            <form method="POST" action="{{ route('admin.shifts.store') }}" class="admin-form-grid">
                @csrf
                <div>
                    <label>Opening cash</label>
                    <input type="number" step="0.01" min="0" name="opening_cash" value="0" required>
                </div>
                <div>
                    <label>Notes</label>
                    <input name="notes">
                </div>
                <div style="grid-column:1/-1;">
                    <button type="submit">Open shift</button>
                </div>
            </form>
        </div>
    @endif

    <div class="card">
        <div class="table-wrap">
            <table class="admin-table">
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
                        <td>{{ ucfirst($shift->status) }}</td>
                        <td class="row-actions"><a class="btn btn-secondary" href="{{ route('admin.shifts.show', $shift) }}">View</a></td>
                    </tr>
                @empty
                    <tr><td colspan="8" class="empty-cell">No shifts yet.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
        <div style="margin-top:12px;">{{ $shifts->links() }}</div>
    </div>
@endsection
