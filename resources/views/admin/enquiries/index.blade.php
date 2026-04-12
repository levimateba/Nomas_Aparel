@extends('layouts.admin')

@section('title', 'Enquiries')

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
    .enquiry-snippet {
        color: #66737f;
        font-size: 0.85rem;
        line-height: 1.5;
        max-width: 420px;
    }
</style>

<div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:20px;gap:12px;flex-wrap:wrap;">
    <div>
        <h2 style="margin:0;">Enquiries</h2>
        <p style="margin:4px 0 0;color:#777;font-size:0.9rem;">Messages submitted from the Contact Us form.</p>
    </div>
    <div style="display:flex;gap:8px;flex-wrap:wrap;">
        <span style="background:rgba(245,158,11,0.12);color:#b45309;font-weight:700;padding:8px 18px;border-radius:999px;font-size:0.9rem;">
            {{ $enquiryCount }} total
        </span>
        <span style="background:rgba(22,87,82,0.08);color:#165752;font-weight:700;padding:8px 18px;border-radius:999px;font-size:0.9rem;">
            {{ $todayCount }} today
        </span>
    </div>
</div>

<form method="GET" action="{{ route('admin.enquiries.index') }}" class="search-bar">
    <input type="text" name="search" value="{{ request('search') }}" placeholder="Search by name, email, subject or message…">
    <button type="submit" style="padding:10px 22px;">Search</button>
    @if(request('search'))
        <a href="{{ route('admin.enquiries.index') }}" class="btn btn-secondary" style="text-decoration:none;padding:10px 18px;">Clear</a>
    @endif
</form>

<div class="card" style="padding:0;overflow-x:auto;overflow-y:hidden;-webkit-overflow-scrolling:touch;">
    <table>
        <thead style="background:rgba(22,69,110,0.04);">
            <tr>
                <th style="padding:14px 16px;">Sender</th>
                <th>Subject</th>
                <th>Message</th>
                <th>Received</th>
                <th style="width:120px;">Actions</th>
            </tr>
        </thead>
        <tbody>
        @forelse($enquiries as $enquiry)
            <tr>
                <td style="padding:14px 16px;">
                    <strong style="display:block;">{{ $enquiry->name }}</strong>
                    <a href="mailto:{{ $enquiry->email }}" style="font-size:0.84rem;color:var(--primary-green);text-decoration:none;">{{ $enquiry->email }}</a>
                </td>
                <td style="font-size:0.9rem;font-weight:600;color:#1f2937;">{{ $enquiry->subject }}</td>
                <td>
                    <div class="enquiry-snippet">{{ \Illuminate\Support\Str::limit($enquiry->message, 110) }}</div>
                </td>
                <td style="font-size:0.85rem;color:#888;">{{ $enquiry->created_at->format('M d, Y H:i') }}</td>
                <td>
                    <a class="btn btn-secondary" href="{{ route('admin.enquiries.show', $enquiry) }}" style="padding:7px 14px;font-size:0.85rem;">View</a>
                    <form method="POST" action="{{ route('admin.enquiries.destroy', $enquiry) }}" style="display:inline;">
                        @csrf @method('DELETE')
                        <button class="btn" style="background:linear-gradient(135deg,#c0392b,#e74c3c);padding:7px 14px;font-size:0.85rem;"
                            type="submit" onclick="return confirm('Delete this enquiry?')">Del</button>
                    </form>
                </td>
            </tr>
        @empty
            <tr><td colspan="5" style="text-align:center;color:#aaa;padding:40px;">No enquiries found.</td></tr>
        @endforelse
        </tbody>
    </table>
    <div style="padding:16px;">{{ $enquiries->links() }}</div>
</div>
@endsection
