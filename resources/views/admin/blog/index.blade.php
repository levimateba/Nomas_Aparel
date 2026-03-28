@extends('layouts.admin')
@section('title','Blog Posts')
@section('content')
    <h2>Blog Posts</h2>
    <a class="btn" href="{{ route('admin.blog.create') }}">New post</a>
    <div class="card">
        <table>
            <thead><tr><th>Title</th><th>Category</th><th>Author</th><th>Published</th><th>Actions</th></tr></thead>
            <tbody>
            @forelse($posts as $post)
                <tr>
                    <td>{{ $post->title }}</td>
                    <td>{{ $post->category ?: 'Uncategorized' }}</td>
                    <td>{{ $post->author ?: 'Admin' }}</td>
                    <td>{{ $post->published ? 'Yes' : 'No' }}</td>
                    <td>
                        <a class="btn btn-secondary" href="{{ route('blog.show', $post->slug) }}" target="_blank" rel="noopener">View</a>
                        <a class="btn btn-secondary" href="{{ route('admin.blog.edit', $post) }}">Edit</a>
                        <form method="POST" action="{{ route('admin.blog.destroy', $post) }}" style="display:inline">
                            @csrf
                            @method('DELETE')
                            <button class="btn" type="submit" onclick="return confirm('Delete this post?')">Delete</button>
                        </form>
                    </td>
                </tr>
            @empty
                <tr><td colspan="5">No posts yet.</td></tr>
            @endforelse
            </tbody>
        </table>
        {{ $posts->links() }}
    </div>
@endsection
