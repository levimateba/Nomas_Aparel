@extends('layouts.admin')
@section('title','Add User')
@section('content')
    <div class="page-head">
        <h2>Add User</h2>
        <a class="btn btn-secondary" href="{{ route('admin.users.index') }}">Back</a>
    </div>
    <div class="card">
        <form method="POST" action="{{ route('admin.users.store') }}">
            @csrf
            <div class="admin-form-grid">
                <div>
                    <label>Name</label>
                    <input type="text" name="name" value="{{ old('name') }}" required>
                </div>
                <div>
                    <label>Email</label>
                    <input type="email" name="email" value="{{ old('email') }}" required>
                </div>
                <div>
                    <label>Password</label>
                    <input type="password" name="password" required>
                </div>
                <div>
                    <label>Confirm password</label>
                    <input type="password" name="password_confirmation" required>
                </div>
                <div>
                    <label>Role</label>
                    <select name="role_id" required>
                        @foreach($roles as $role)
                            <option value="{{ $role->id }}" {{ old('role_id') == $role->id ? 'selected' : '' }}>{{ $role->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label>User group</label>
                    <select name="user_group_id">
                        <option value="">None</option>
                        @foreach($groups as $group)
                            <option value="{{ $group->id }}" {{ old('user_group_id') == $group->id ? 'selected' : '' }}>{{ $group->name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <button type="submit">Save user</button>
        </form>
    </div>
@endsection
