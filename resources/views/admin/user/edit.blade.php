@extends('layouts.admin')
@section('title','Edit User')
@section('content')
    <h2>Edit User</h2>
    <div class="card">
        <form method="POST" action="{{ route('admin.users.update', $user) }}">
            @csrf
            @method('PUT')
            <label>Name</label>
            <input type="text" name="name" value="{{ old('name', $user->name) }}" required>
            <label>Email</label>
            <input type="email" name="email" value="{{ old('email', $user->email) }}" required>
            <label>New Password</label>
            <input type="password" name="password">
            <label>Confirm Password</label>
            <input type="password" name="password_confirmation">
            <label>Role</label>
            <select name="role_id" required style="width:100%;padding:10px;border-radius:8px;border:1px solid #d7dde2;margin-bottom:12px;">
                @foreach($roles as $role)
                    <option value="{{ $role->id }}" {{ old('role_id', $user->role_id) == $role->id ? 'selected' : '' }}>{{ $role->name }}</option>
                @endforeach
            </select>
            <label>User Group</label>
            <select name="user_group_id" style="width:100%;padding:10px;border-radius:8px;border:1px solid #d7dde2;margin-bottom:12px;">
                <option value="">No group</option>
                @foreach($groups as $group)
                    <option value="{{ $group->id }}" {{ old('user_group_id', $user->user_group_id) == $group->id ? 'selected' : '' }}>{{ $group->name }}</option>
                @endforeach
            </select>
            <button type="submit">Update User</button>
        </form>
    </div>
@endsection
