@extends('layouts.admin')

@section('title', 'Newsletter Subscribers')

@section('content')
<style>
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
</style>

<div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:20px;gap:12px;flex-wrap:wrap;">
    <div>
        <h2 style="margin:0;">Newsletter Subscribers</h2>
        <p style="margin:4px 0 0;color:#777;font-size:0.9rem;">Email addresses collected from the footer newsletter form.</p>
    </div>
    <div style="display:flex;gap:8px;flex-wrap:wrap;">
        <span style="background:rgba(124,58,237,0.12);color:#6d28d9;font-weight:700;padding:8px 18px;border-radius:999px;font-size:0.9rem;">
            {{ $subscriberCount }} total
        </span>
        <span style="background:rgba(22,87,82,0.08);color:#165752;font-weight:700;padding:8px 18px;border-radius:999px;font-size:0.9rem;">
            {{ $todayCount }} today
        </span>
    </div>
</div>

<form method="GET" action="{{ route('admin.newsletter-subscribers.index') }}" class="search-bar">
    <input type="text" name="search" value="{{ request('search') }}" placeholder="Search by email address…">
    <button type="submit" style="padding:10px 22px;">Search</button>
    @if(request('search'))
        <a href="{{ route('admin.newsletter-subscribers.index') }}" class="btn btn-secondary" style="text-decoration:none;padding:10px 18px;">Clear</a>
    @endif
</form>

<div class="card" style="padding:0;overflow-x:auto;overflow-y:hidden;-webkit-overflow-scrolling:touch;">
    <table>
        <thead style="background:rgba(22,69,110,0.04);">
            <tr>
                <th style="padding:14px 16px;">Email</th>
                <th>Subscribed</th>
                <th style="width:120px;">Actions</th>
            </tr>
        </thead>
        <tbody>
        @forelse($subscribers as $subscriber)
            <tr>
                <td style="padding:14px 16px;">
                    <a href="mailto:{{ $subscriber->email }}" style="color:var(--primary-green);text-decoration:none;font-weight:600;">{{ $subscriber->email }}</a>
                </td>
                <td style="font-size:0.85rem;color:#888;">{{ $subscriber->created_at->format('M d, Y H:i') }}</td>
                <td>
                    <form method="POST" action="{{ route('admin.newsletter-subscribers.destroy', $subscriber) }}" style="display:inline;">
                        @csrf @method('DELETE')
                        <button class="btn" style="background:linear-gradient(135deg,#c0392b,#e74c3c);padding:7px 14px;font-size:0.85rem;"
                            type="submit" onclick="return confirm('Delete this subscriber?')">Delete</button>
                    </form>
                </td>
            </tr>
        @empty
            <tr><td colspan="3" style="text-align:center;color:#aaa;padding:40px;">No newsletter subscribers found.</td></tr>
        @endforelse
        </tbody>
    </table>
    <div style="padding:16px;">{{ $subscribers->links() }}</div>
</div>
@endsection
