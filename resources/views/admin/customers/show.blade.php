@extends('layouts.admin')
@section('title', $customer->name)
@section('heading', $customer->name)
@section('subheading', 'Customer profile')

@section('content')
<div class="ta-page">
    <x-admin.page-header :title="$customer->name" subtitle="Customer profile & loyalty">
        <x-slot:actions>
            <a href="{{ route('admin.customers.index') }}" class="ta-btn-ghost">Back to customers</a>
        </x-slot:actions>
    </x-admin.page-header>

    <div class="admin-form-grid" style="align-items:start;">
        <x-admin.list-card title="Details">
            <form method="POST" action="{{ route('admin.customers.update', $customer) }}" class="admin-form-grid">
                @csrf
                @method('PUT')
                <div class="ta-field">
                    <label>Full name *</label>
                    <input type="text" name="name" value="{{ old('name', $customer->name) }}" required>
                </div>
                <div class="ta-field">
                    <label>Phone</label>
                    <input type="text" name="phone" value="{{ old('phone', $customer->phone) }}">
                </div>
                <div class="ta-field">
                    <label>Email</label>
                    <input type="email" name="email" value="{{ old('email', $customer->email) }}">
                </div>
                <div class="ta-field">
                    <label>Address</label>
                    <input type="text" name="address" value="{{ old('address', $customer->address) }}">
                </div>
                <div class="ta-field" style="grid-column:1/-1;">
                    <label>Notes</label>
                    <textarea name="notes" rows="2">{{ old('notes', $customer->notes) }}</textarea>
                </div>
                <label class="ta-field" style="display:flex;align-items:center;gap:8px;">
                    <input type="checkbox" name="is_active" value="1" @checked(old('is_active', $customer->is_active))>
                    Active
                </label>
                <div style="grid-column:1/-1;">
                    <button type="submit" class="ta-btn">Save changes</button>
                </div>
            </form>
        </x-admin.list-card>

        <x-admin.list-card title="Loyalty">
            @if($card)
                <div class="ta-kpi-grid" style="grid-template-columns:1fr 1fr;">
                    <div class="ta-kpi"><span>Loyalty Card</span><strong>{{ $card->card_number }}</strong></div>
                    <div class="ta-kpi"><span>Status</span><strong>{{ ucfirst($card->status) }}</strong></div>
                    <div class="ta-kpi"><span>Available Points</span><strong>⭐ {{ number_format($card->points_balance) }}</strong></div>
                    <div class="ta-kpi"><span>Total Earned</span><strong>{{ number_format($stats['earned']) }}</strong></div>
                    <div class="ta-kpi"><span>Total Redeemed</span><strong>{{ number_format($stats['redeemed']) }}</strong></div>
                    <div class="ta-kpi"><span>Transactions</span><strong>{{ number_format($stats['transactions']) }}</strong></div>
                </div>
                <div class="ta-actions" style="margin-top:1rem;flex-wrap:wrap;">
                    <a href="{{ route('admin.loyalty.history', ['customer_id' => $customer->id]) }}" class="ta-btn-ghost">View Loyalty History</a>
                    @if($canManageLoyalty)
                        <button type="button" class="ta-btn" onclick="document.getElementById('adjust-panel').hidden = !document.getElementById('adjust-panel').hidden">Adjust Points</button>
                        @if($card->status === 'active')
                            <form method="POST" action="{{ route('admin.loyalty.cards.block', $card) }}" onsubmit="return confirm('Block this loyalty card?')">
                                @csrf
                                <button type="submit" class="ta-btn-danger">Block Card</button>
                            </form>
                        @endif
                    @endif
                </div>
                @if($canManageLoyalty)
                    <div id="adjust-panel" hidden style="margin-top:1rem;padding-top:1rem;border-top:1px solid rgba(28,36,52,.1);">
                        <form method="POST" action="{{ route('admin.customers.loyalty.adjust', $customer) }}" class="admin-form-grid">
                            @csrf
                            <div class="ta-field">
                                <label>Type</label>
                                <select name="direction" required>
                                    <option value="add">Add</option>
                                    <option value="remove">Remove</option>
                                </select>
                            </div>
                            <div class="ta-field">
                                <label>Points</label>
                                <input type="number" name="points" min="1" required>
                            </div>
                            <div class="ta-field" style="grid-column:1/-1;">
                                <label>Reason</label>
                                <input type="text" name="reason" required maxlength="500" placeholder="Customer promotion">
                            </div>
                            <div>
                                <button type="submit" class="ta-btn">Save Adjustment</button>
                            </div>
                        </form>
                    </div>
                @endif
            @elseif($loyaltyEnabled)
                <p class="ta-muted">No active loyalty card.</p>
                <form method="POST" action="{{ route('admin.customers.loyalty.issue', $customer) }}" style="margin-top:12px;">
                    @csrf
                    <button type="submit" class="ta-btn">Issue Loyalty Card</button>
                </form>
            @else
                <p class="ta-muted">Loyalty program is disabled. Enable it under Settings → Customer Loyalty.</p>
            @endif
        </x-admin.list-card>
    </div>

    @if($recent->isNotEmpty())
        <div class="ta-table-card" style="margin-top:1.25rem;">
            <div class="ta-table-head"><h3>Recent Loyalty Activity</h3></div>
            <div class="ta-table-wrap">
                <table class="ta-table">
                    <thead>
                        <tr>
                            <th>Type</th>
                            <th>Points</th>
                            <th>Balance</th>
                            <th>Description</th>
                            <th>Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($recent as $tx)
                            <tr>
                                <td>{{ ucfirst($tx->type) }}</td>
                                <td>{{ $tx->points > 0 ? '+'.$tx->points : $tx->points }}</td>
                                <td>{{ $tx->balance_after }}</td>
                                <td>{{ $tx->description }}</td>
                                <td>{{ $tx->created_at?->format('d M Y H:i') }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endif
</div>
@endsection
