@extends('layouts.admin')
@section('title','Add User')
@section('content')
    <h2>Add User</h2>
    <div class="card">
        <form method="POST" action="{{ route('admin.users.store') }}">
            @csrf
            <label>Name</label>
            <input type="text" name="name" value="{{ old('name') }}" required>
            <label>Email</label>
            <input type="email" name="email" value="{{ old('email') }}" required>
            <label>Password</label>
            <input type="password" name="password" required>
            <label>Confirm Password</label>
            <input type="password" name="password_confirmation" required>
            <label>Role</label>
            <select name="role_id" required style="width:100%;padding:10px;border-radius:8px;border:1px solid #d7dde2;margin-bottom:12px;">
                @foreach($roles as $role)
                    <option value="{{ $role->id }}" {{ old('role_id') == $role->id ? 'selected' : '' }}>{{ $role->name }}</option>
                @endforeach
            </select>
            <label>User Group</label>
            <select name="user_group_id" style="width:100%;padding:10px;border-radius:8px;border:1px solid #d7dde2;margin-bottom:12px;">
                <option value="">No group</option>
                @foreach($groups as $group)
                    <option value="{{ $group->id }}" {{ old('user_group_id') == $group->id ? 'selected' : '' }}>{{ $group->name }}</option>
                @endforeach
            </select>
            <button type="submit">Save User</button>
        </form>
    </div>
@endsection
