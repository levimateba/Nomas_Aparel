@extends('layouts.admin')
@section('title','Quote Requests')
@section('content')
<style>
    .quote-status-badge {
        display: inline-block;
        padding: 4px 14px;
        border-radius: 999px;
        font-size: 0.75rem;
        font-weight: 700;
        letter-spacing: 0.08em;
        text-transform: uppercase;
        color: #fff;
    }
    .filter-tabs {
        display: flex;
        flex-wrap: wrap;
        gap: 8px;
        margin-bottom: 18px;
    }
    .filter-tab {
        padding: 6px 16px;
        border-radius: 999px;
        border: 1.5px solid #dde3ea;
        background: #fff;
        color: #555;
        font-size: 0.82rem;
        font-weight: 700;
        cursor: pointer;
        text-decoration: none;
        transition: background 0.18s, color 0.18s, border-color 0.18s;
    }
    .filter-tab.active, .filter-tab:hover {
        background: var(--primary-dark);
        color: #fff;
        border-color: var(--primary-dark);
    }
    .search-bar {
        display: flex;
        gap: 10px;
        margin-bottom: 18px;
    }
    .search-bar input {
        flex: 1;
        padding: 10px 14px;
        border: 1.5px solid #dde3ea;
        border-radius: 10px;
        font-size: 0.95rem;
    }
    .quote-new-indicator {
        width: 8px;
        height: 8px;
        border-radius: 50%;
        background: #2196f3;
        display: inline-block;
        margin-right: 6px;
        flex-shrink: 0;
    }
</style>

<div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:20px;">
    <div>
        <h2 style="margin:0;">Quote Requests</h2>
        <p style="margin:4px 0 0;color:#777;font-size:0.9rem;">Manage and respond to incoming quote inquiries.</p>
    </div>
    <span style="background:rgba(33,150,243,0.1);color:#1565c0;font-weight:700;padding:8px 18px;border-radius:999px;font-size:0.9rem;">
        {{ $counts['new'] }} new
    </span>
</div>

@if(session('success'))
    <div class="alert">{{ session('success') }}</div>
@endif

<!-- Status Filter Tabs -->
<div class="filter-tabs">
    <a href="{{ route('admin.quotes.index') }}" class="filter-tab {{ !request('status') ? 'active' : '' }}">
        All <span style="opacity:0.6;">({{ $counts['all'] }})</span>
    </a>
    @foreach(['new' => '#2196f3','reviewing' => '#ff9800','quoted' => '#9c27b0','accepted' => '#4caf50','declined' => '#f44336'] as $s => $color)
    <a href="{{ route('admin.quotes.index', ['status' => $s] + request()->except('status','page')) }}"
        class="filter-tab {{ request('status') === $s ? 'active' : '' }}"
        style="{{ request('status') === $s ? "background:{$color};border-color:{$color};" : '' }}">
        {{ ucfirst($s) }} <span style="opacity:0.6;">({{ $counts[$s] }})</span>
    </a>
    @endforeach
</div>

<!-- Search -->
<form method="GET" action="{{ route('admin.quotes.index') }}" class="search-bar">
    @if(request('status'))<input type="hidden" name="status" value="{{ request('status') }}">@endif
    <input type="text" name="search" value="{{ request('search') }}" placeholder="Search by name, email, company or service…">
    <button type="submit" style="padding:10px 22px;">Search</button>
    @if(request('search'))<a href="{{ route('admin.quotes.index', request()->except('search','page')) }}" class="btn btn-secondary" style="text-decoration:none;padding:10px 18px;">Clear</a>@endif
</form>

<div class="card" style="padding:0;overflow-x:auto;overflow-y:hidden;-webkit-overflow-scrolling:touch;">
    <table>
        <thead style="background:rgba(22,69,110,0.04);">
            <tr>
                <th style="padding:14px 16px;">Client</th>
                <th>Service</th>
                <th>Budget</th>
                <th>Status</th>
                <th>Submitted</th>
                <th style="width:120px;">Actions</th>
            </tr>
        </thead>
        <tbody>
        @forelse($quotes as $quote)
            <tr>
                <td style="padding:14px 16px;">
                    <div style="display:flex;align-items:center;gap:6px;">
                        @if($quote->status === 'new')
                            <span class="quote-new-indicator" title="New"></span>
                        @endif
                        <div>
                            <strong style="display:block;">{{ $quote->name }}</strong>
                            <span style="font-size:0.82rem;color:#888;">{{ $quote->email }}</span>
                            @if($quote->company)
                                <span style="font-size:0.8rem;color:#aaa;display:block;">{{ $quote->company }}</span>
                            @endif
                        </div>
                    </div>
                </td>
                <td style="font-size:0.9rem;">{{ $quote->service_type }}</td>
                <td style="font-size:0.85rem;color:#666;">{{ $quote->budget_range ?: '—' }}</td>
                <td>
                    <span class="quote-status-badge" style="background:{{ $quote->status_color }};">
                        {{ $quote->status_label }}
                    </span>
                </td>
                <td style="font-size:0.85rem;color:#888;">{{ $quote->created_at->format('M d, Y') }}</td>
                <td>
                    <a class="btn btn-secondary" href="{{ route('admin.quotes.show', $quote) }}" style="padding:7px 14px;font-size:0.85rem;">View</a>
                    <form method="POST" action="{{ route('admin.quotes.destroy', $quote) }}" style="display:inline;">
                        @csrf @method('DELETE')
                        <button class="btn" style="background:linear-gradient(135deg,#c0392b,#e74c3c);padding:7px 14px;font-size:0.85rem;"
                            type="submit" onclick="return confirm('Delete this quote request?')">Del</button>
                    </form>
                </td>
            </tr>
        @empty
            <tr><td colspan="6" style="text-align:center;color:#aaa;padding:40px;">No quote requests found.</td></tr>
        @endforelse
        </tbody>
    </table>
    <div style="padding:16px;">{{ $quotes->links() }}</div>
</div>
@endsection
