@extends('layouts.admin')
@section('title','Edit Blog Post')
@section('content')
    <h2>Edit Blog Post</h2>
    <div class="card">
        <form method="POST" action="{{ route('admin.blog.update', $post) }}" enctype="multipart/form-data">
            @csrf
            @method('PUT')
            <label>Title</label>
            <input type="text" name="title" value="{{ old('title', $post->title) }}" required>
            
            <label>Category</label>
            <input type="text" name="category" value="{{ old('category', $post->category) }}" placeholder="e.g., AI, Technology, Development">
            
            <label>Author</label>
            <input type="text" name="author" value="{{ old('author', $post->author) }}">
            
            <label>Excerpt (Short Summary)</label>
            <textarea name="excerpt" rows="3" placeholder="Brief summary for blog cards">{{ old('excerpt', $post->excerpt) }}</textarea>
            
            <label>Content</label>
            <textarea name="content" rows="8" required>{{ old('content', $post->content) }}</textarea>
            
            <label>Image</label>
            @if($post->image)
                <div style="margin-bottom: 10px;">
                    <img src="{{ $post->image }}" alt="Current image" style="max-width: 200px; max-height: 150px;">
                </div>
            @endif
            <input type="file" name="image" accept="image/*">
            <small style="display: block; margin-top: 5px; color: #666;">Max file size: 2MB (JPEG, PNG, GIF)</small>
            
            <label>Published Date</label>
            <input type="datetime-local" name="published_at" value="{{ old('published_at', $post->published_at ? $post->published_at->format('Y-m-d\TH:i') : now()->format('Y-m-d\TH:i')) }}">
            <label style="margin-top: 15px;"><input type="checkbox" name="published" value="1" {{ old('published', $post->published) ? 'checked' : '' }}> Publish now</label>
            
            <button type="submit">Update Post</button>
        </form>
    </div>
@endsection
