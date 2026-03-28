@extends('layouts.admin')
@section('title','Videos')
@section('content')
    <h2>Videos</h2>
    <a class="btn" href="{{ route('admin.video.create') }}">Add video</a>
    <div class="card">
        <table>
            <thead><tr><th>Title</th><th>Description</th><th>Actions</th></tr></thead>
            <tbody>
            @forelse($videos as $video)
                <tr>
                    <td>{{ $video->title }}</td>
                    <td>{{ \Illuminate\Support\Str::limit($video->description, 60) }}</td>
                    <td>
                        <a class="btn btn-secondary" href="{{ route('admin.video.edit', $video) }}">Edit</a>
                        <form method="POST" action="{{ route('admin.video.destroy', $video) }}" style="display:inline">
                            @csrf
                            @method('DELETE')
                            <button class="btn" type="submit" onclick="return confirm('Delete this video?')">Delete</button>
                        </form>
                    </td>
                </tr>
            @empty
                <tr><td colspan="3">No videos yet.</td></tr>
            @endforelse
            </tbody>
        </table>
        {{ $videos->links() }}
    </div>
@endsection
