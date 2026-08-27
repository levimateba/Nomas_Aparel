@extends('layouts.admin')
@section('title', 'Reviews')
@section('heading', 'Reviews')
@section('subheading', 'Approve customer feedback before it appears on products.')

@section('content')
    <div class="admin-kpis">
        <div class="admin-kpi"><span>Total Reviews</span><strong>{{ $stats['total'] }}</strong></div>
        <div class="admin-kpi"><span>Pending</span><strong>{{ $stats['pending'] }}</strong></div>
        <div class="admin-kpi"><span>Approved</span><strong>{{ $stats['approved'] }}</strong></div>
    </div>
    <form class="admin-toolbar" method="GET" action="{{ route('admin.reviews.index') }}">
        <div class="admin-filters">
            <div class="field">
                <label>Status</label>
                <select name="status">
                    <option value="">All</option>
                    <option value="approved" @selected(request('status') === 'approved')>Approved</option>
                    <option value="pending" @selected(request('status') === 'pending')>Pending</option>
                </select>
            </div>
            <button class="btn" type="submit">Apply</button>
        </div>
    </form>
    <div class="card">
        <div class="table-wrap">
            <table class="admin-table">
                <thead>
                    <tr><th>Product</th><th>Reviewer</th><th>Rating</th><th>Comment</th><th>Status</th><th>Actions</th></tr>
                </thead>
                <tbody>
                @forelse($reviews as $review)
                    <tr>
                        <td><strong>{{ $review->product?->name ?: 'N/A' }}</strong></td>
                        <td>{{ $review->name }}<br><span class="muted">{{ $review->email }}</span></td>
                        <td>{{ $review->rating }}/5</td>
                        <td>{{ \Illuminate\Support\Str::limit($review->comment, 120) }}</td>
                        <td><span class="status-pill {{ $review->approved ? 'on' : 'warn' }}">{{ $review->approved ? 'Approved' : 'Pending' }}</span></td>
                        <td class="row-actions">
                            <form method="POST" action="{{ route('admin.reviews.update', $review) }}">
                                @csrf @method('PATCH')
                                <button class="btn btn-secondary" type="submit">{{ $review->approved ? 'Unapprove' : 'Approve' }}</button>
                            </form>
                            <form method="POST" action="{{ route('admin.reviews.destroy', $review) }}">
                                @csrf @method('DELETE')
                                <button class="btn btn-danger" type="submit" onclick="return confirm('Delete review?')">Delete</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="empty-cell">No reviews yet.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
        <div style="margin-top:12px;">{{ $reviews->links() }}</div>
    </div>
@endsection
