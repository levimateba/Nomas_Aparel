@extends('layouts.admin')
@section('title','Edit User Group')
@section('content')
    <h2>Edit User Group</h2>
    <div class="card">
        <form method="POST" action="{{ route('admin.user-groups.update', $group) }}">
            @csrf
            @method('PUT')
            <label>Name</label>
            <input type="text" name="name" value="{{ old('name', $group->name) }}" required>
            <label>Description</label>
            <textarea name="description" rows="4">{{ old('description', $group->description) }}</textarea>
            <button type="submit">Update Group</button>
        </form>
    </div>
@endsection
