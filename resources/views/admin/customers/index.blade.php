@extends('layouts.admin')
@section('title', 'Customers')
@section('heading', 'Customers')
@section('subheading', 'POS customer directory')

@section('content')
<div class="ta-page">
    <x-admin.list-card title="Add customer">
        <form method="POST" action="{{ route('admin.customers.store') }}" class="admin-form-grid">
            @csrf
            <div class="ta-field">
                <label>Full name *</label>
                <input type="text" name="name" required>
            </div>
            <div class="ta-field">
                <label>Phone</label>
                <input type="text" name="phone">
            </div>
            <div class="ta-field">
                <label>Email</label>
                <input type="email" name="email">
            </div>
            <div class="ta-field">
                <label>Address</label>
                <input type="text" name="address">
            </div>
            <div class="ta-field" style="grid-column:1/-1;">
                <label>Notes</label>
                <textarea name="notes" rows="2"></textarea>
            </div>
            @if($loyaltyEnabled ?? false)
                <label class="ta-field" style="display:flex;align-items:center;gap:8px;grid-column:1/-1;">
                    <input type="checkbox" name="issue_loyalty_card" value="1">
                    Create Loyalty Card
                </label>
            @endif
            <div style="grid-column:1/-1;">
                <button type="submit" class="ta-btn">Save customer</button>
            </div>
        </form>
    </x-admin.list-card>

    <div class="ta-toolbar">
        <form method="GET" style="display:contents;">
            <div class="ta-field" style="flex:2;">
                <label>Search</label>
                <input type="text" name="q" value="{{ request('q') }}" placeholder="Search name, phone, email">
            </div>
            <div class="ta-field">
                <label>Loyalty</label>
                <select name="loyalty">
                    <option value="">All customers</option>
                    <option value="members" @selected(request('loyalty') === 'members')>Loyalty members</option>
                    <option value="non_members" @selected(request('loyalty') === 'non_members')>Non-loyalty members</option>
                    <option value="active" @selected(request('loyalty') === 'active')>Active cards</option>
                    <option value="blocked" @selected(request('loyalty') === 'blocked')>Blocked cards</option>
                </select>
            </div>
            <button type="submit" class="ta-btn">Search</button>
            <a href="{{ route('admin.loyalty.history') }}" class="ta-btn-ghost">Loyalty History</a>
        </form>
    </div>

    <div class="ta-table-card">
        <div class="ta-table-wrap">
            <table class="ta-table">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Phone</th>
                        <th>Loyalty Card</th>
                        <th>Points</th>
                        <th>Loyalty Status</th>
                        <th>Status</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($customers as $customer)
                        <tr>
                            <td><div class="ta-name"><a href="{{ route('admin.customers.show', $customer) }}">{{ $customer->name }}</a></div></td>
                            <td>{{ $customer->phone ?: '—' }}</td>
                            <td>{{ $customer->loyaltyCard?->card_number ?? '—' }}</td>
                            <td>
                                @if($customer->loyaltyCard)
                                    ⭐ {{ number_format($customer->loyaltyCard->points_balance) }}
                                @else
                                    —
                                @endif
                            </td>
                            <td>
                                @if($customer->loyaltyCard)
                                    <span class="ta-pill ta-pill-success">{{ ucfirst($customer->loyaltyCard->status) }}</span>
                                @else
                                    <span class="ta-pill ta-pill-muted">None</span>
                                @endif
                            </td>
                            <td>
                                <span class="ta-pill {{ $customer->is_active ? 'ta-pill-success' : 'ta-pill-muted' }}">
                                    {{ $customer->is_active ? 'Active' : 'Inactive' }}
                                </span>
                            </td>
                            <td>
                                <div class="ta-actions">
                                    <a href="{{ route('admin.customers.show', $customer) }}" class="ta-btn-ghost ta-btn-sm">View</a>
                                    <form method="POST" action="{{ route('admin.customers.destroy', $customer) }}" onsubmit="return confirm('Delete this customer?')">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="ta-btn-danger ta-btn-sm">Delete</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="ta-empty">No customers yet.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="ta-pagination">{{ $customers->links() }}</div>
    </div>
</div>
@endsection
