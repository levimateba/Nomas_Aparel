@extends('layouts.admin')
@section('title','Create Service')
@section('content')
    <h2>Create Service</h2>
    <div class="card">
        <form method="POST" action="{{ route('admin.service.store') }}">
            @csrf
            <label>Title</label>
            <input type="text" name="title" value="{{ old('title') }}" required>
            <label>Description</label>
            <textarea name="description" rows="5" required>{{ old('description') }}</textarea>
            <label>Icon (Font Awesome)</label>
            <input type="text" name="icon" value="{{ old('icon') }}" placeholder="fa-code, fa-mobile-alt, fa-cogs, fa-star, etc.">
            <small style="display: block; margin-top: 5px; color: #666;">Examples: fa-code, fa-mobile-alt, fa-cogs, fa-server. See <a href="https://fontawesome.com/icons" target="_blank">Font Awesome Icons</a></small>
            <button type="submit">Save</button>
        </form>
    </div>
@endsection