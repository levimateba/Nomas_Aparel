@extends('layouts.admin')
@section('title', 'Users')
@section('heading', 'Users')
@section('subheading', 'Manage staff accounts, customer accounts, and assigned roles.')

@section('content')
    <div class="admin-kpis">
        <div class="admin-kpi"><span>Total users</span><strong>{{ $stats['total'] }}</strong></div>
        <div class="admin-kpi"><span>Staff</span><strong>{{ $stats['staff'] }}</strong></div>
        <div class="admin-kpi"><span>Full admins</span><strong>{{ $stats['admins'] }}</strong></div>
    </div>
    <div class="admin-toolbar">
        <div></div>
        <a class="btn" href="{{ route('admin.users.create') }}">+ Add User</a>
    </div>
    <div class="card">
        <div class="table-wrap">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Roles</th>
                        <th>Group</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                @forelse($users as $user)
                    <tr>
                        <td><strong>{{ $user->name }}</strong></td>
                        <td>{{ $user->email }}</td>
                        <td>
                            <div class="role-badges">
                                @forelse($user->rolesCollection() as $role)
                                    <span class="status-pill">{{ $role->name }}</span>
                                @empty
                                    <span class="muted">—</span>
                                @endforelse
                            </div>
                        </td>
                        <td>{{ $user->group?->name ?: '—' }}</td>
                        <td class="row-actions">
                            <a class="btn btn-secondary" href="{{ route('admin.users.edit', $user) }}">Edit</a>
                            <a class="btn btn-secondary" href="{{ route('admin.users.assign-roles', $user) }}">Assign Roles</a>
                            <form method="POST" action="{{ route('admin.users.reset-password', $user) }}" onsubmit="return confirm('Reset this password to password123?')">
                                @csrf
                                <button class="btn btn-secondary" type="submit">Reset Password</button>
                            </form>
                            <form method="POST" action="{{ route('admin.users.destroy', $user) }}">
                                @csrf @method('DELETE')
                                <button class="btn btn-danger" type="submit" onclick="return confirm('Delete this user?')">Delete</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="empty-cell">No users yet.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
        <div style="margin-top:12px;">{{ $users->links() }}</div>
    </div>
@endsection
