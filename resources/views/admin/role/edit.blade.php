@extends('layouts.admin')
@section('title', 'Edit Role')
@section('content')
    <div class="page-head">
        <h2>Edit Role</h2>
        <a class="btn btn-secondary" href="{{ route('admin.roles.index') }}">Back</a>
    </div>
    <div class="card">
        <form method="POST" action="{{ route('admin.roles.update', $role) }}">
            @csrf
            @method('PUT')
            <label>Name</label>
            <input type="text" name="name" value="{{ old('name', $role->name) }}" required>
            <label>Description</label>
            <textarea name="description" rows="3">{{ old('description', $role->description) }}</textarea>
            <button type="submit">Update role</button>
        </form>
    </div>
@endsection
