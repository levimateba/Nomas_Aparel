@extends('layouts.admin')
@section('title','Edit About')
@section('content')
    <h2>Edit About Section</h2>
    <div class="card">
        <form method="POST" action="{{ route('admin.about.update', $section) }}" enctype="multipart/form-data">
            @csrf
            @method('PUT')
            <label>Title</label>
            <input type="text" name="title" value="{{ old('title', $section->title) }}" required>
            <label>Content</label>
            <textarea name="content" rows="5" required>{{ old('content', $section->content) }}</textarea>
            <label>Image</label>
            @if($section->image_url)
                <div style="margin-bottom: 10px;">
                    <img src="{{ $section->image_url }}" alt="Current image" style="max-width: 200px; max-height: 150px;">
                </div>
            @endif
            <input type="file" name="image_url" accept="image/*">
            <small style="display:block;margin:6px 0 14px;color:#666;">Max file size: 2MB (JPEG, PNG, GIF)</small>
            <button type="submit">Update</button>
        </form>
    </div>
@endsection
