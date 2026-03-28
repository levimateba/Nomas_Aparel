@extends('layouts.admin')
@section('title','News & Events')
@section('content')
    <h2>News & Events</h2>
    <a class="btn" href="{{ route('admin.news-events.create') }}">Add item</a>
    <div class="card">
        <table>
            <thead><tr><th>Title</th><th>Date</th><th>Published</th><th>Actions</th></tr></thead>
            <tbody>
            @forelse($items as $item)
                <tr>
                    <td>{{ $item->title }}</td>
                    <td>{{ $item->event_date?->format('M d, Y H:i') ?: 'Not set' }}</td>
                    <td>{{ $item->published ? 'Yes' : 'No' }}</td>
                    <td>
                        <a class="btn btn-secondary" href="{{ route('home') }}#news-events" target="_blank" rel="noopener">View</a>
                        <a class="btn btn-secondary" href="{{ route('admin.news-events.edit', $item) }}">Edit</a>
                        <form method="POST" action="{{ route('admin.news-events.destroy', $item) }}" style="display:inline">
                            @csrf
                            @method('DELETE')
                            <button class="btn" type="submit" onclick="return confirm('Delete this item?')">Delete</button>
                        </form>
                    </td>
                </tr>
            @empty
                <tr><td colspan="4">No news or events yet.</td></tr>
            @endforelse
            </tbody>
        </table>
        {{ $items->links() }}
    </div>
@endsection
