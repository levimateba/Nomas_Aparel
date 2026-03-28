@extends('layouts.admin')
@section('title','Edit Gallery Image')
@section('content')
    <h2>Edit Gallery Image</h2>
    <div class="card">
        <form method="POST" action="{{ route('admin.gallery.update', $item) }}" enctype="multipart/form-data">
            @csrf
            @method('PUT')
            <label>Short Title</label>
            <input type="text" name="title" value="{{ old('title', $item->title) }}" required>
            <label>Current Image</label>
            <div style="margin-bottom: 10px;">
                <img src="{{ $item->image }}" alt="{{ $item->title }}" style="width:180px;height:120px;object-fit:cover;border-radius:8px;">
            </div>
            <input type="file" name="image" accept="image/*">
            <button type="submit">Update</button>
        </form>
    </div>
@endsection
