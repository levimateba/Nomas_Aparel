@extends('layouts.admin')
@section('title','Edit Role')
@section('content')
    <h2>Edit Role</h2>
    <div class="card">
        <form method="POST" action="{{ route('admin.roles.update', $role) }}">
            @csrf
            @method('PUT')
            <label>Name</label>
            <input type="text" name="name" value="{{ old('name', $role->name) }}" required>
            <label>Description</label>
            <textarea name="description" rows="4">{{ old('description', $role->description) }}</textarea>
            <label>Permissions</label>
            <div style="display:grid;gap:8px;margin:8px 0 14px;">
                @foreach($permissions as $permission)
                    <label style="display:flex;align-items:center;gap:8px;font-weight:400;">
                        <input type="checkbox" name="permission_ids[]" value="{{ $permission->id }}" {{ in_array($permission->id, old('permission_ids', $role->permissions->pluck('id')->all())) ? 'checked' : '' }}>
                        {{ $permission->name }}
                    </label>
                @endforeach
            </div>
            <button type="submit">Update Role</button>
        </form>
    </div>
@endsection
