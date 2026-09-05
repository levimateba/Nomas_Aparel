@extends('layouts.admin')
@section('title', 'Enquiries')

@section('page_header')
<div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
    <div>
        <nav class="mb-2 flex flex-wrap items-center gap-1.5 text-sm text-gray-400">
            <a href="{{ route('admin.dashboard') }}" class="hover:text-brand-500">Dashboard</a>
            <span>/</span>
            <span>Communication</span>
            <span>/</span>
            <span class="text-gray-600 dark:text-gray-300">Enquiries</span>
        </nav>
        <h1 class="text-2xl font-bold text-gray-800 dark:text-white/90">Enquiries</h1>
        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Messages submitted from the website contact form.</p>
    </div>
</div>
@endsection

@section('content')
<div class="ta-page">
    <div class="ta-kpis">
        <div class="ta-kpi">
            <div class="ta-kpi-icon is-brand">
                <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 8l7.9 5.3a2 2 0 002.2 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
            </div>
            <div>
                <div class="ta-kpi-label">Total Enquiries</div>
                <div class="ta-kpi-value">{{ number_format($enquiryCount) }}</div>
                <div class="ta-kpi-sub">All time</div>
            </div>
        </div>
        <div class="ta-kpi">
            <div class="ta-kpi-icon is-blue">
                <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3M4 11h16M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
            </div>
            <div>
                <div class="ta-kpi-label">Today</div>
                <div class="ta-kpi-value">{{ number_format($todayCount) }}</div>
                <div class="ta-kpi-sub">Received today</div>
            </div>
        </div>
    </div>

    <div class="ta-toolbar">
        <form method="GET" action="{{ route('admin.enquiries.index') }}" class="flex flex-1 flex-wrap items-end gap-3">
            <div class="ta-field" style="flex:2; min-width:200px;">
                <label>Search</label>
                <input type="text" name="search" class="ta-input" value="{{ request('search') }}" placeholder="Name, email, subject or message">
            </div>
            <button class="ta-btn" type="submit">Search</button>
            @if(request('search'))
                <a href="{{ route('admin.enquiries.index') }}" class="ta-btn-outline">Clear</a>
            @endif
        </form>
    </div>

    <div class="ta-table-card">
        <div class="ta-table-wrap">
            <table class="ta-table">
                <thead>
                    <tr>
                        <th>Sender</th>
                        <th>Subject</th>
                        <th>Message</th>
                        <th>Received</th>
                        <th class="text-right">Actions</th>
                    </tr>
                </thead>
                <tbody>
                @forelse($enquiries as $enquiry)
                    <tr>
                        <td>
                            <div class="font-semibold text-gray-800 dark:text-white/90">{{ $enquiry->name }}</div>
                            <div class="text-xs text-gray-500">{{ $enquiry->email }}</div>
                        </td>
                        <td class="font-medium text-gray-800 dark:text-white/90">{{ $enquiry->subject }}</td>
                        <td class="max-w-sm text-sm text-gray-600 dark:text-gray-300">{{ \Illuminate\Support\Str::limit($enquiry->message, 110) }}</td>
                        <td class="whitespace-nowrap text-sm text-gray-500">{{ $enquiry->created_at->format('d M Y H:i') }}</td>
                        <td class="text-right">
                            <div class="inline-flex flex-wrap items-center justify-end gap-2">
                                <a href="{{ route('admin.enquiries.show', $enquiry) }}" class="ta-btn-outline ta-btn-sm">View</a>
                                <form method="POST" action="{{ route('admin.enquiries.destroy', $enquiry) }}">
                                    @csrf
                                    @method('DELETE')
                                    <button class="ta-btn-danger ta-btn-sm" type="submit" onclick="return confirm('Delete this enquiry?')">Delete</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="py-10 text-center text-sm text-gray-500">No enquiries found.</td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>
        @if($enquiries->hasPages())
            <div class="border-t border-gray-100 px-4 py-3 dark:border-gray-800">{{ $enquiries->links() }}</div>
        @endif
    </div>
</div>
@endsection
