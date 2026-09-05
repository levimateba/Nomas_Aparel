@extends('layouts.admin')
@section('title', 'Expenses')
@section('heading', 'Expenses')
@section('subheading', 'Record shop operating expenses')

@section('content')
<div class="ta-page">
    <div class="ta-kpis ta-kpis-3">
        <div class="ta-kpi">
            <div class="ta-kpi-icon is-blue">
                <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
            </div>
            <div>
                <div class="ta-kpi-label">Today</div>
                <div class="ta-kpi-value" style="font-size:1.3rem;">KES {{ number_format($todayTotal, 2) }}</div>
            </div>
        </div>
        <div class="ta-kpi">
            <div class="ta-kpi-icon is-warning">
                <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
            </div>
            <div>
                <div class="ta-kpi-label">This month</div>
                <div class="ta-kpi-value" style="font-size:1.3rem;">KES {{ number_format($monthTotal, 2) }}</div>
            </div>
        </div>
        <div class="ta-kpi">
            <div class="ta-kpi-icon is-purple">
                <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/></svg>
            </div>
            <div>
                <div class="ta-kpi-label">All time</div>
                <div class="ta-kpi-value" style="font-size:1.3rem;">KES {{ number_format($allTotal, 2) }}</div>
            </div>
        </div>
    </div>

    <x-admin.list-card title="Record expense">
        <form method="POST" action="{{ route('admin.expenses.store') }}" class="admin-form-grid">
            @csrf
            <div class="ta-field">
                <label>Category (optional)</label>
                <select name="expense_category_id">
                    <option value="">Category (optional)</option>
                    @foreach($categories as $category)
                        <option value="{{ $category->id }}">{{ $category->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="ta-field">
                <label>Amount *</label>
                <input type="number" step="0.01" min="0.01" name="amount" placeholder="Amount *" required>
            </div>
            <div class="ta-field">
                <label>Description</label>
                <input type="text" name="description" placeholder="Description">
            </div>
            <div class="ta-field">
                <label>Payment method *</label>
                <select name="payment_method" required>
                    @foreach(['Cash','M-Pesa','Card','Bank','Credit'] as $method)
                        <option value="{{ $method }}">{{ $method }}</option>
                    @endforeach
                </select>
            </div>
            <div class="ta-field">
                <label>Date *</label>
                <input type="date" name="expense_date" value="{{ now()->toDateString() }}" required>
            </div>
            <div class="ta-field">
                <label>Reference</label>
                <input type="text" name="reference_number" placeholder="Reference">
            </div>
            <div class="ta-field" style="grid-column:1/-1;">
                <label>Notes</label>
                <textarea name="notes" rows="2" placeholder="Notes"></textarea>
            </div>
            <div style="grid-column:1/-1;">
                <button type="submit" class="ta-btn">Save expense</button>
            </div>
        </form>
    </x-admin.list-card>

    <div class="ta-table-card">
        <div class="ta-table-wrap">
            <table class="ta-table">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Category</th>
                        <th>Description</th>
                        <th>Method</th>
                        <th style="text-align:right;">Amount</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($expenses as $expense)
                        <tr>
                            <td>{{ $expense->expense_date->format('d M Y') }}</td>
                            <td>{{ $expense->category?->name ?: '—' }}</td>
                            <td>{{ $expense->description ?: '—' }}</td>
                            <td>{{ $expense->payment_method }}</td>
                            <td style="text-align:right;"><strong>KES {{ number_format((float)$expense->amount, 2) }}</strong></td>
                            <td>
                                <div class="ta-actions">
                                    <form method="POST" action="{{ route('admin.expenses.destroy', $expense) }}" onsubmit="return confirm('Delete expense?')">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="ta-btn-danger ta-btn-sm">Delete</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="ta-empty">No expenses recorded.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="ta-table-footer">{{ $expenses->links() }}</div>
    </div>
</div>
@endsection
