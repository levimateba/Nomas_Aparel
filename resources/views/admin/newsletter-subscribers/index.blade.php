@extends('layouts.admin')
@section('title', 'Subscribers')

@section('page_header')
<div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
    <div>
        <nav class="mb-2 flex flex-wrap items-center gap-1.5 text-sm text-gray-400">
            <a href="{{ route('admin.dashboard') }}" class="hover:text-brand-500">Dashboard</a>
            <span>/</span>
            <span>Marketing</span>
            <span>/</span>
            <span class="text-gray-600 dark:text-gray-300">Newsletter</span>
        </nav>
        <h1 class="text-2xl font-bold text-gray-800 dark:text-white/90">Newsletter Subscribers</h1>
        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Emails collected from the storefront newsletter form.</p>
    </div>
</div>
@endsection

@section('content')
<div class="ta-page">
    <div class="ta-kpis">
        <div class="ta-kpi">
            <div class="ta-kpi-icon is-brand">
                <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M16 12H8m8 0a4 4 0 11-8 0 4 4 0 018 0zm-8 8h8a4 4 0 004-4V8a4 4 0 00-4-4H8a4 4 0 00-4 4v8a4 4 0 004 4z"/></svg>
            </div>
            <div>
                <div class="ta-kpi-label">Total Subscribers</div>
                <div class="ta-kpi-value">{{ number_format($subscriberCount) }}</div>
                <div class="ta-kpi-sub">All time</div>
            </div>
        </div>
        <div class="ta-kpi">
            <div class="ta-kpi-icon is-success">
                <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            </div>
            <div>
                <div class="ta-kpi-label">Today</div>
                <div class="ta-kpi-value">{{ number_format($todayCount) }}</div>
                <div class="ta-kpi-sub">New sign-ups</div>
            </div>
        </div>
    </div>

    <div class="ta-toolbar">
        <form method="GET" action="{{ route('admin.newsletter-subscribers.index') }}" class="flex flex-1 flex-wrap items-end gap-3">
            <div class="ta-field" style="flex:2; min-width:200px;">
                <label>Search</label>
                <input type="text" name="search" class="ta-input" value="{{ request('search') }}" placeholder="Email address">
            </div>
            <button class="ta-btn" type="submit">Search</button>
            @if(request('search'))
                <a href="{{ route('admin.newsletter-subscribers.index') }}" class="ta-btn-outline">Clear</a>
            @endif
        </form>
    </div>

    <div class="ta-table-card">
        <div class="ta-table-wrap">
            <table class="ta-table">
                <thead>
                    <tr>
                        <th>Email</th>
                        <th>Subscribed</th>
                        <th class="text-right">Actions</th>
                    </tr>
                </thead>
                <tbody>
                @forelse($subscribers as $subscriber)
                    <tr>
                        <td>
                            <div class="font-semibold text-gray-800 dark:text-white/90">{{ $subscriber->email }}</div>
                        </td>
                        <td class="whitespace-nowrap text-sm text-gray-500">{{ $subscriber->created_at->format('d M Y H:i') }}</td>
                        <td class="text-right">
                            <form method="POST" action="{{ route('admin.newsletter-subscribers.destroy', $subscriber) }}" class="inline">
                                @csrf
                                @method('DELETE')
                                <button class="ta-btn-danger ta-btn-sm" type="submit" onclick="return confirm('Delete this subscriber?')">Delete</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="3" class="py-10 text-center text-sm text-gray-500">No newsletter subscribers found.</td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>
        @if($subscribers->hasPages())
            <div class="border-t border-gray-100 px-4 py-3 dark:border-gray-800">{{ $subscribers->links() }}</div>
        @endif
    </div>
</div>
@endsection
