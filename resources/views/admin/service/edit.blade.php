@extends('layouts.admin')
@section('title','Edit Service')
@section('content')
    <h2>Edit Service</h2>
    <div class="card">
        <form method="POST" action="{{ route('admin.service.update', $service) }}">
            @csrf
            @method('PUT')
            <label>Title</label>
            <input type="text" name="title" value="{{ old('title', $service->title) }}" required>
            <label>Description</label>
            <textarea name="description" rows="5" required>{{ old('description', $service->description) }}</textarea>
            <label>Icon (Font Awesome)</label>
            <input type="text" name="icon" value="{{ old('icon', $service->icon) }}" placeholder="fa-code, fa-mobile-alt, fa-cogs, fa-star, etc.">
            <small style="display: block; margin-top: 5px; color: #666;">Examples: fa-code, fa-mobile-alt, fa-cogs, fa-server. See <a href="https://fontawesome.com/icons" target="_blank">Font Awesome Icons</a></small>
            <button type="submit">Update</button>
        </form>
    </div>
@endsection