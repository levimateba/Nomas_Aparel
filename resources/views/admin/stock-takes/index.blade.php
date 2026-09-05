@extends('layouts.admin')
@section('title', 'Stocktakes')
@section('heading', 'Stocktakes')
@section('subheading', 'Physical count sessions — inventory adjustments only after approval.')

@section('content')
<div class="ta-page">
    <div class="ta-kpis">
        <div class="ta-kpi">
            <div class="ta-kpi-icon is-blue">
                <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
            </div>
            <div>
                <div class="ta-kpi-label">Open / Counting</div>
                <div class="ta-kpi-value">{{ $stats['open'] }}</div>
                <div class="ta-kpi-sub">Active sessions</div>
            </div>
        </div>
        <div class="ta-kpi">
            <div class="ta-kpi-icon is-warning">
                <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
            </div>
            <div>
                <div class="ta-kpi-label">In Review</div>
                <div class="ta-kpi-value">{{ $stats['review'] }}</div>
                <div class="ta-kpi-sub">Awaiting review</div>
            </div>
        </div>
        <div class="ta-kpi">
            <div class="ta-kpi-icon is-success">
                <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            </div>
            <div>
                <div class="ta-kpi-label">Approved</div>
                <div class="ta-kpi-value">{{ $stats['approved'] }}</div>
                <div class="ta-kpi-sub">Completed</div>
            </div>
        </div>
        <div class="ta-kpi">
            <div class="ta-kpi-icon is-purple">
                <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/></svg>
            </div>
            <div>
                <div class="ta-kpi-label">This Month Net</div>
                <div class="ta-kpi-value" style="font-size:1.3rem;">KES {{ number_format($stats['month_net'], 2) }}</div>
                <div class="ta-kpi-sub">Variance value</div>
            </div>
        </div>
    </div>

    <div class="ta-toolbar">
        <p class="ta-muted" style="margin:0;flex:1;">Start a new physical stock count session.</p>
        <a href="{{ route('admin.stock-takes.create') }}" class="ta-btn">
            <svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
            New Stocktake
        </a>
    </div>

    <div class="ta-table-card">
        <div class="ta-table-wrap">
            <table class="ta-table">
                <thead>
                    <tr>
                        <th>Number</th>
                        <th>Date</th>
                        <th>Status</th>
                        <th>Counted By</th>
                        <th>Variance (+ / −)</th>
                        <th>Items Counted</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                @forelse($takes as $take)
                    @php
                        $posQty = (int) $take->items->where('variance', '>', 0)->sum('variance');
                        $negQty = abs((int) $take->items->where('variance', '<', 0)->sum('variance'));
                        $itemCount = $take->items->count();
                        $pillClass = match($take->status) {
                            'approved'  => 'ta-pill-success',
                            'review'    => 'ta-pill-warning',
                            'counting'  => 'ta-pill-info',
                            'cancelled' => 'ta-pill-muted',
                            default     => 'ta-pill-muted',
                        };
                        $initials = strtoupper(substr($take->user?->name ?? 'A', 0, 1));
                    @endphp
                    <tr>
                        <td>
                            <div class="ta-name">{{ $take->reference }}</div>
                            <div class="ta-muted">Main Warehouse</div>
                        </td>
                        <td>
                            <div class="ta-name">{{ ($take->stocktake_date ?? $take->created_at)?->format('d M Y') }}</div>
                            <div class="ta-muted">{{ $take->created_at->format('g:i A') }}</div>
                        </td>
                        <td>
                            <span class="ta-pill {{ $pillClass }}">{{ ucfirst($take->status) }}</span>
                        </td>
                        <td>
                            <div style="display:flex;align-items:center;gap:8px;">
                                <div style="width:32px;height:32px;border-radius:50%;background:#1a1300;color:#d4af37;font-size:13px;font-weight:800;display:inline-flex;align-items:center;justify-content:center;">{{ $initials }}</div>
                                <div>
                                    <div class="ta-name">{{ $take->user?->name ?: 'Unknown' }}</div>
                                    <div class="ta-muted">{{ $take->user?->roles?->first()?->name ?? 'Super Admin' }}</div>
                                </div>
                            </div>
                        </td>
                        <td>
                            <div>
                                <span style="color:#059669;font-weight:700;">+{{ number_format($posQty) }}</span>
                                <span class="ta-muted"> / </span>
                                <span style="color:#dc2626;font-weight:700;">−{{ number_format($negQty) }}</span>
                            </div>
                            <div class="ta-muted">KES {{ number_format((float)($take->positive_variance_value ?? 0), 2) }}</div>
                        </td>
                        <td>
                            <div class="ta-name">{{ number_format($itemCount) }}</div>
                            <div class="ta-muted">of {{ $take->items->count() }} items</div>
                        </td>
                        <td>
                            <div class="ta-actions">
                                <a href="{{ route('admin.stock-takes.show', $take) }}" class="ta-btn-outline ta-btn-sm">View</a>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="ta-empty">No stocktakes yet.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
        <div class="ta-table-footer">
            <span class="ta-muted">Showing {{ $takes->firstItem() ?? 0 }} to {{ $takes->lastItem() ?? 0 }} of {{ $takes->total() }} stocktakes</span>
            {{ $takes->links() }}
        </div>
    </div>

    <x-admin.list-card title="How it works" desc="Items counted in stocktake sessions will only affect inventory after final approval.">
        <x-slot:actions>
            <a href="#" class="ta-btn-outline ta-btn-sm">View guide</a>
        </x-slot:actions>
    </x-admin.list-card>
</div>
@endsection
