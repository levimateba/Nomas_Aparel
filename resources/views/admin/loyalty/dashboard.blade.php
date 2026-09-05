@extends('layouts.admin')
@section('title', 'Loyalty Dashboard')
@section('heading', 'Loyalty')
@section('subheading', 'Customer loyalty overview')

@section('content')
<div class="ta-page">
    <x-admin.page-header title="Loyalty Overview" subtitle="Members, points issued, and top customers">
        <x-slot:actions>
            <a href="{{ route('admin.loyalty.history') }}" class="ta-btn-ghost">History</a>
            @if(auth()->user()?->hasPermission('manage_loyalty'))
                <a href="{{ route('admin.loyalty.settings') }}" class="ta-btn">Settings</a>
            @endif
        </x-slot:actions>
    </x-admin.page-header>

    <div class="ta-kpis">
        <div class="ta-kpi">
            <div class="ta-kpi-label">Loyalty Members</div>
            <div class="ta-kpi-value">{{ number_format($members) }}</div>
        </div>
        <div class="ta-kpi">
            <div class="ta-kpi-label">Points Issued</div>
            <div class="ta-kpi-value">{{ number_format($pointsIssued) }}</div>
        </div>
        <div class="ta-kpi">
            <div class="ta-kpi-label">Points Redeemed</div>
            <div class="ta-kpi-value">{{ number_format($pointsRedeemed) }}</div>
        </div>
        <div class="ta-kpi">
            <div class="ta-kpi-label">Active Cards</div>
            <div class="ta-kpi-value">{{ number_format($activeCards) }}</div>
        </div>
    </div>

    <div class="ta-table-card" style="margin-top:1.25rem;">
        <div class="ta-table-head"><h3>Top Loyalty Customers</h3></div>
        <div class="ta-table-wrap">
            <table class="ta-table">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Customer</th>
                        <th>Card</th>
                        <th>Points</th>
                        <th>Total Spent</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($topCustomers as $i => $row)
                        <tr>
                            <td>{{ $i + 1 }}</td>
                            <td><div class="ta-name">{{ $row['customer'] }}</div></td>
                            <td>{{ $row['card'] }}</td>
                            <td>⭐ {{ number_format($row['points']) }}</td>
                            <td>KES {{ number_format($row['spent'], 2) }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="ta-empty">No loyalty members yet.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
