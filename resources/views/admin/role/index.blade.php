@extends('layouts.admin')
@section('title', 'Roles')
@section('heading', 'Roles')
@section('subheading', 'Define roles and assign permissions to control access.')

@section('content')
    <div class="admin-toolbar">
        <div></div>
        <a class="btn" href="{{ route('admin.roles.create') }}">+ New Role</a>
    </div>
    <div class="card">
        <div class="table-wrap">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>Role</th>
                        <th>Permissions</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                @forelse($roles as $role)
                    <tr>
                        <td>
                            <strong>{{ $role->name }}</strong>
                            @if($role->description)
                                <div class="muted">{{ $role->description }}</div>
                            @endif
                        </td>
                        <td>
                            <div class="role-badges">
                                @forelse($role->permissions as $permission)
                                    <span class="status-pill info">{{ \App\Support\PermissionCatalog::label($permission->name) }}</span>
                                @empty
                                    <span class="muted">No permissions assigned</span>
                                @endforelse
                            </div>
                        </td>
                        <td class="row-actions">
                            <a class="btn btn-secondary" href="{{ route('admin.roles.assign-permissions', $role) }}">Assign Permissions</a>
                            <a class="btn btn-secondary" href="{{ route('admin.roles.edit', $role) }}">Edit</a>
                            <form method="POST" action="{{ route('admin.roles.destroy', $role) }}">
                                @csrf @method('DELETE')
                                <button class="btn btn-danger" type="submit" onclick="return confirm('Delete this role?')">Delete</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="3" class="empty-cell">No roles yet.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
        <div style="margin-top:12px;">{{ $roles->links() }}</div>
    </div>
@endsection
