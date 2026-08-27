@extends('layouts.admin')
@section('title', 'Subscribers')
@section('heading', 'Subscribers')
@section('subheading', 'Newsletter emails collected from the store.')

@section('content')
    <div class="admin-kpis">
        <div class="admin-kpi"><span>Total Subscribers</span><strong>{{ $subscriberCount }}</strong></div>
        <div class="admin-kpi"><span>Today</span><strong>{{ $todayCount }}</strong></div>
    </div>
    <form class="admin-toolbar" method="GET" action="{{ route('admin.newsletter-subscribers.index') }}">
        <div class="admin-filters">
            <div class="field" style="flex:2;">
                <label>Search</label>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Email address">
            </div>
            <button class="btn" type="submit">Search</button>
            @if(request('search'))
                <a class="btn btn-secondary" href="{{ route('admin.newsletter-subscribers.index') }}">Clear</a>
            @endif
        </div>
    </form>
    <div class="card">
        <div class="table-wrap">
            <table class="admin-table">
                <thead>
                    <tr><th>Email</th><th>Subscribed</th><th>Actions</th></tr>
                </thead>
                <tbody>
                @forelse($subscribers as $subscriber)
                    <tr>
                        <td><strong>{{ $subscriber->email }}</strong></td>
                        <td>{{ $subscriber->created_at->format('d M Y H:i') }}</td>
                        <td class="row-actions">
                            <form method="POST" action="{{ route('admin.newsletter-subscribers.destroy', $subscriber) }}">
                                @csrf @method('DELETE')
                                <button class="btn btn-danger" type="submit" onclick="return confirm('Delete this subscriber?')">Delete</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="3" class="empty-cell">No newsletter subscribers found.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
        <div style="margin-top:12px;">{{ $subscribers->links() }}</div>
    </div>
@endsection
