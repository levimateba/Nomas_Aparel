@extends('layouts.admin')
@section('title','Edit Permission')
@section('content')
    <h2>Edit Permission</h2>
    <div class="card">
        <form method="POST" action="{{ route('admin.permissions.update', $permission) }}">
            @csrf
            @method('PUT')
            <label>Name</label>
            <input type="text" name="name" value="{{ old('name', $permission->name) }}" required>
            <label>Description</label>
            <textarea name="description" rows="4">{{ old('description', $permission->description) }}</textarea>
            <button type="submit">Update Permission</button>
        </form>
    </div>
@endsection
