@extends('layouts.admin')
@section('title','Edit Permission')
@section('content')
    <div class="page-head">
        <h2>Edit Permission</h2>
        <a class="btn btn-secondary" href="{{ route('admin.permissions.index') }}">Back</a>
    </div>
    <div class="card">
        <form method="POST" action="{{ route('admin.permissions.update', $permission) }}">
            @csrf
            @method('PUT')
            <label>Name</label>
            <input type="text" name="name" value="{{ old('name', $permission->name) }}" required>
            <label>Description</label>
            <textarea name="description" rows="3">{{ old('description', $permission->description) }}</textarea>
            <button type="submit">Update permission</button>
        </form>
    </div>
@endsection
