@extends('layouts.admin')
@section('title','Add Gallery Image')
@section('content')
    <h2>Add Gallery Image</h2>
    <div class="card">
        <form method="POST" action="{{ route('admin.gallery.store') }}" enctype="multipart/form-data">
            @csrf
            <label>Short Title</label>
            <input type="text" name="title" value="{{ old('title') }}" placeholder="e.g. Project Workshops" required>
            <label>Image</label>
            <input type="file" name="image" accept="image/*" required>
            <small style="display:block;margin:6px 0 14px;color:#666;">This image will appear in the homepage gallery with hover effects.</small>
            <button type="submit">Save</button>
        </form>
    </div>
@endsection
