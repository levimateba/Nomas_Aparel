@extends('layouts.admin')
@section('title','Add News or Event')
@section('content')
    <h2>Add News or Event</h2>
    <div class="card">
        <form method="POST" action="{{ route('admin.news-events.store') }}" enctype="multipart/form-data">
            @csrf
            <label>Title</label>
            <input type="text" name="title" value="{{ old('title') }}" required>
            <label>Short Summary</label>
            <textarea name="excerpt" rows="3">{{ old('excerpt') }}</textarea>
            <label>Full Description</label>
            <textarea name="content" rows="6">{{ old('content') }}</textarea>
            <label>Event Date</label>
            <input type="datetime-local" name="event_date" value="{{ old('event_date') }}">
            <label>Image</label>
            <input type="file" name="image" accept="image/*">
            <small style="display:block;margin:6px 0 14px;color:#666;">JPEG, PNG, GIF, or WebP up to 10MB.</small>
            <label style="margin-top: 15px;"><input type="checkbox" name="published" value="1" {{ old('published', true) ? 'checked' : '' }}> Publish now</label>
            <button type="submit">Save</button>
        </form>
    </div>
@endsection
