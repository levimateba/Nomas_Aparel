@extends('layouts.admin')
@section('title','Add Video')
@section('content')
    <h2>Add Video</h2>
    <div class="card">
        <form method="POST" action="{{ route('admin.video.store') }}" enctype="multipart/form-data">
            @csrf
            <label>Title</label>
            <input type="text" name="title" value="{{ old('title') }}" required>
            <label>Description</label>
            <textarea name="description" rows="4">{{ old('description') }}</textarea>
            <label>Video File</label>
            <input type="file" name="video_path" accept="video/mp4,video/webm,video/ogg">
            <small style="display:block;margin:6px 0 10px;color:#666;">Upload MP4, WebM, or OGG. Max 20MB.</small>
            <label>Video URL (optional)</label>
            <input type="url" name="video_url" value="{{ old('video_url') }}" placeholder="https://...">
            <small style="display:block;margin:6px 0 14px;color:#666;">You can provide either a video file or a video URL.</small>
            <button type="submit">Save</button>
        </form>
    </div>
@endsection
