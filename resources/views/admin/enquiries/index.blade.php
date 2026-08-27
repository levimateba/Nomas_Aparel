@extends('layouts.admin')
@section('title', 'Enquiries')
@section('heading', 'Enquiries')
@section('subheading', 'Messages submitted from the contact form.')

@section('content')
    <div class="admin-kpis">
        <div class="admin-kpi"><span>Total Enquiries</span><strong>{{ $enquiryCount }}</strong></div>
        <div class="admin-kpi"><span>Today</span><strong>{{ $todayCount }}</strong></div>
    </div>
    <form class="admin-toolbar" method="GET" action="{{ route('admin.enquiries.index') }}">
        <div class="admin-filters">
            <div class="field" style="flex:2;">
                <label>Search</label>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Name, email, subject or message">
            </div>
            <button class="btn" type="submit">Search</button>
            @if(request('search'))
                <a class="btn btn-secondary" href="{{ route('admin.enquiries.index') }}">Clear</a>
            @endif
        </div>
    </form>
    <div class="card">
        <div class="table-wrap">
            <table class="admin-table">
                <thead>
                    <tr><th>Sender</th><th>Subject</th><th>Message</th><th>Received</th><th>Actions</th></tr>
                </thead>
                <tbody>
                @forelse($enquiries as $enquiry)
                    <tr>
                        <td>
                            <strong>{{ $enquiry->name }}</strong><br>
                            <span class="muted">{{ $enquiry->email }}</span>
                        </td>
                        <td>{{ $enquiry->subject }}</td>
                        <td>{{ \Illuminate\Support\Str::limit($enquiry->message, 110) }}</td>
                        <td>{{ $enquiry->created_at->format('d M Y H:i') }}</td>
                        <td class="row-actions">
                            <a class="btn btn-secondary" href="{{ route('admin.enquiries.show', $enquiry) }}">View</a>
                            <form method="POST" action="{{ route('admin.enquiries.destroy', $enquiry) }}">
                                @csrf @method('DELETE')
                                <button class="btn btn-danger" type="submit" onclick="return confirm('Delete this enquiry?')">Delete</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="empty-cell">No enquiries found.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
        <div style="margin-top:12px;">{{ $enquiries->links() }}</div>
    </div>
@endsection
