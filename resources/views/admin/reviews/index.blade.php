@extends('layouts.admin')
@section('title', 'Product Reviews')
@section('content')
    <h2>Product Reviews</h2>
    <form method="GET" action="{{ route('admin.reviews.index') }}" style="margin: 10px 0; display:flex; gap:8px; align-items:center;">
        <select name="status" style="max-width:180px;">
            <option value="">All</option>
            <option value="approved" {{ request('status') === 'approved' ? 'selected' : '' }}>Approved</option>
            <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>Pending</option>
        </select>
        <button class="btn btn-secondary" type="submit">Filter</button>
    </form>
    <div class="card">
        <table>
            <thead>
                <tr>
                    <th>Product</th>
                    <th>Reviewer</th>
                    <th>Rating</th>
                    <th>Comment</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
            @forelse($reviews as $review)
                <tr>
                    <td>{{ $review->product?->name ?: 'N/A' }}</td>
                    <td>{{ $review->name }}<br><small>{{ $review->email }}</small></td>
                    <td>{{ $review->rating }}/5</td>
                    <td>{{ \Illuminate\Support\Str::limit($review->comment, 120) }}</td>
                    <td>{{ $review->approved ? 'Approved' : 'Pending' }}</td>
                    <td>
                        <form method="POST" action="{{ route('admin.reviews.update', $review) }}">
                            @csrf @method('PATCH')
                            <button class="btn btn-secondary" type="submit">{{ $review->approved ? 'Unapprove' : 'Approve' }}</button>
                        </form>
                        <form method="POST" action="{{ route('admin.reviews.destroy', $review) }}">
                            @csrf @method('DELETE')
                            <button class="btn" type="submit" onclick="return confirm('Delete review?')">Delete</button>
                        </form>
                    </td>
                </tr>
            @empty
                <tr><td colspan="6">No reviews yet.</td></tr>
            @endforelse
            </tbody>
        </table>
        {{ $reviews->links() }}
    </div>
@endsection
