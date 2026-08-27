@extends('layouts.admin')
@section('title', 'Assign Roles')
@section('heading', 'Assign Roles')
@section('subheading', 'Choose which roles '.$user->name.' should have.')

@section('content')
    <div class="page-head">
        <h2>Assign roles to {{ $user->name }}</h2>
        <a class="btn btn-secondary" href="{{ route('admin.users.index') }}">Back to Users</a>
    </div>
    <div class="card">
        <form method="POST" action="{{ route('admin.users.update-roles', $user) }}">
            @csrf
            <div class="perm-grid">
                @foreach($roles as $role)
                    <label>
                        <input type="checkbox" name="roles[]" value="{{ $role->id }}" id="role-{{ $role->id }}"
                            {{ in_array($role->id, old('roles', $userRoles)) ? 'checked' : '' }}>
                        {{ $role->name }}
                    </label>
                @endforeach
            </div>
            <button type="submit">Update Roles</button>
            <a class="btn btn-secondary" href="{{ route('admin.users.index') }}">Cancel</a>
        </form>
    </div>
@endsection
