@extends('layouts.admin')
@section('title','Edit News or Event')
@section('content')
    <h2>Edit News or Event</h2>
    <div class="card">
        <form method="POST" action="{{ route('admin.news-events.update', $item) }}" enctype="multipart/form-data">
            @csrf
            @method('PUT')
            <label>Title</label>
            <input type="text" name="title" value="{{ old('title', $item->title) }}" required>
            <label>Short Summary</label>
            <textarea name="excerpt" rows="3">{{ old('excerpt', $item->excerpt) }}</textarea>
            <label>Full Description</label>
            <textarea name="content" rows="6">{{ old('content', $item->content) }}</textarea>
            <label>Event Date</label>
            <input type="datetime-local" name="event_date" value="{{ old('event_date', $item->event_date?->format('Y-m-d\TH:i')) }}">
            @if($item->image)
                <div style="margin: 10px 0;">
                    <img src="{{ $item->image }}" alt="{{ $item->title }}" style="width:180px;height:120px;object-fit:cover;border-radius:8px;">
                </div>
            @endif
            <input type="file" name="image" accept="image/*">
            <label style="margin-top: 15px;"><input type="checkbox" name="published" value="1" {{ old('published', $item->published) ? 'checked' : '' }}> Publish now</label>
            <button type="submit">Update</button>
        </form>
    </div>
@endsection
