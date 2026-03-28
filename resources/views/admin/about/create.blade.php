@extends('layouts.admin')
@section('title','Create About')
@section('content')
    <h2>Create About Section</h2>
    <div class="card">
        <form method="POST" action="{{ route('admin.about.store') }}" enctype="multipart/form-data">
            @csrf
            <label>Title</label>
            <input type="text" name="title" value="{{ old('title') }}" required>
            <label>Content</label>
            <textarea name="content" rows="5" required>{{ old('content') }}</textarea>
            <label>Image</label>
            <input type="file" name="image_url" accept="image/*">
            <small style="display:block;margin:6px 0 14px;color:#666;">Max file size: 2MB (JPEG, PNG, GIF)</small>
            <button type="submit">Save</button>
        </form>
    </div>
@endsection
