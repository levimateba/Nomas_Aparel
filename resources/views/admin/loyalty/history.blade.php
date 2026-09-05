@extends('layouts.admin')
@section('title', 'Loyalty History')
@section('heading', 'Loyalty History')
@section('subheading', 'Full points ledger')

@section('content')
<div class="ta-page">
    <x-admin.page-header title="Loyalty Transactions" subtitle="Earned, redeemed, reversed, and adjustments">
        <x-slot:actions>
            <a href="{{ route('admin.loyalty.dashboard') }}" class="ta-btn-ghost">Dashboard</a>
            @if(auth()->user()?->hasPermission('manage_loyalty'))
                <a href="{{ route('admin.loyalty.settings') }}" class="ta-btn-ghost">Settings</a>
            @endif
        </x-slot:actions>
    </x-admin.page-header>

    <div class="ta-kpis">
        <div class="ta-kpi">
            <div class="ta-kpi-label">Total Loyalty Members</div>
            <div class="ta-kpi-value">{{ number_format($kpis['members']) }}</div>
        </div>
        <div class="ta-kpi">
            <div class="ta-kpi-label">Total Points Issued</div>
            <div class="ta-kpi-value">{{ number_format($kpis['issued']) }}</div>
        </div>
        <div class="ta-kpi">
            <div class="ta-kpi-label">Total Points Redeemed</div>
            <div class="ta-kpi-value">{{ number_format($kpis['redeemed']) }}</div>
        </div>
        <div class="ta-kpi">
            <div class="ta-kpi-label">Total Active Cards</div>
            <div class="ta-kpi-value">{{ number_format($kpis['active']) }}</div>
        </div>
    </div>

    <div class="ta-toolbar" style="margin-top:1rem;">
        <form method="GET" style="display:contents;">
            <div class="ta-field" style="flex:2;">
                <label>Search</label>
                <input type="text" name="q" value="{{ request('q') }}" placeholder="Customer, card, sale, description">
            </div>
            <div class="ta-field">
                <label>Type</label>
                <select name="type">
                    <option value="">All</option>
                    @foreach(['earned','redeemed','reversed','adjustment','expired'] as $type)
                        <option value="{{ $type }}" @selected(request('type') === $type)>{{ ucfirst($type) }}</option>
                    @endforeach
                </select>
            </div>
            <div class="ta-field">
                <label>Customer</label>
                <select name="customer_id">
                    <option value="">All</option>
                    @foreach($customers as $c)
                        <option value="{{ $c->id }}" @selected((string) request('customer_id') === (string) $c->id)>{{ $c->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="ta-field">
                <label>From</label>
                <input type="date" name="from" value="{{ request('from') }}">
            </div>
            <div class="ta-field">
                <label>To</label>
                <input type="date" name="to" value="{{ request('to') }}">
            </div>
            <button type="submit" class="ta-btn">Filter</button>
        </form>
    </div>

    <div class="ta-table-card">
        <div class="ta-table-wrap">
            <table class="ta-table">
                <thead>
                    <tr>
                        <th>Customer</th>
                        <th>Card</th>
                        <th>Type</th>
                        <th>Points</th>
                        <th>Balance</th>
                        <th>Sale</th>
                        <th>Date</th>
                        <th>Created By</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($transactions as $tx)
                        @php
                            $badge = match($tx->type) {
                                'earned' => 'ta-pill-success',
                                'redeemed' => 'ta-pill-warning',
                                'reversed' => 'ta-pill-danger',
                                'adjustment' => 'ta-pill-info',
                                default => 'ta-pill-muted',
                            };
                        @endphp
                        <tr>
                            <td><div class="ta-name">{{ $tx->customer?->name ?? '—' }}</div></td>
                            <td>{{ $tx->card?->card_number ?? '—' }}</td>
                            <td><span class="ta-pill {{ $badge }}">{{ ucfirst($tx->type) }}</span></td>
                            <td>{{ $tx->points > 0 ? '+'.$tx->points : $tx->points }}</td>
                            <td>{{ $tx->balance_after }}</td>
                            <td>
                                @if($tx->order)
                                    <a href="{{ route('admin.orders.show', $tx->order) }}">{{ $tx->order->order_number }}</a>
                                @else
                                    —
                                @endif
                            </td>
                            <td>{{ $tx->created_at?->format('d M Y H:i') }}</td>
                            <td>{{ $tx->creator?->name ?? 'System' }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="8" class="ta-empty">No loyalty transactions yet.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="ta-pagination">{{ $transactions->links() }}</div>
    </div>
</div>
@endsection
