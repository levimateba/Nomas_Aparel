@extends('layouts.admin')
@section('title','Create Blog Post')
@section('content')
    <h2>Create Blog Post</h2>
    <div class="card">
        <form method="POST" action="{{ route('admin.blog.store') }}" enctype="multipart/form-data">
            @csrf
            <label>Title</label>
            <input type="text" name="title" value="{{ old('title') }}" required>
            
            <label>Category</label>
            <input type="text" name="category" value="{{ old('category') }}" placeholder="e.g., AI, Technology, Development">
            
            <label>Author</label>
            <input type="text" name="author" value="{{ old('author', auth()->user()?->name) }}">
            
            <label>Excerpt (Short Summary)</label>
            <textarea name="excerpt" rows="3" placeholder="Brief summary for blog cards">{{ old('excerpt') }}</textarea>
            
            <label>Content</label>
            <textarea name="content" rows="8" required>{{ old('content') }}</textarea>
            
            <label>Image</label>
            <input type="file" name="image" accept="image/*">
            <small style="display: block; margin-top: 5px; color: #666;">Max file size: 2MB (JPEG, PNG, GIF)</small>
            
            <label>Published Date</label>
            <input type="datetime-local" name="published_at" value="{{ old('published_at', now()->format('Y-m-d\TH:i')) }}">
            <label style="margin-top: 15px;"><input type="checkbox" name="published" value="1" {{ old('published', true) ? 'checked' : '' }}> Publish now</label>
            
            <button type="submit">Create Post</button>
        </form>
    </div>
@endsection
