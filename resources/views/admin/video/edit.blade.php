@extends('layouts.admin')
@section('title','Edit Video')
@section('content')
    <h2>Edit Video</h2>
    <div class="card">
        <form method="POST" action="{{ route('admin.video.update', $video) }}" enctype="multipart/form-data">
            @csrf
            @method('PUT')
            <label>Title</label>
            <input type="text" name="title" value="{{ old('title', $video->title) }}" required>
            <label>Description</label>
            <textarea name="description" rows="4">{{ old('description', $video->description) }}</textarea>
            @if($video->video_path)
                <video src="{{ $video->video_path }}" controls style="width: 240px; border-radius: 10px; margin: 10px 0;"></video>
            @endif
            @if($video->video_url)
                <p style="margin: 8px 0; color: #666;">Current URL: <a href="{{ $video->video_url }}" target="_blank" rel="noopener">{{ $video->video_url }}</a></p>
            @endif
            <input type="file" name="video_path" accept="video/mp4,video/webm,video/ogg">
            <label>Video URL (optional)</label>
            <input type="url" name="video_url" value="{{ old('video_url', $video->video_url) }}" placeholder="https://...">
            <button type="submit">Update</button>
        </form>
    </div>
@endsection
