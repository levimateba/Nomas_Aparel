@extends('layouts.admin')
@section('title', 'Users')
@section('heading', 'Users')
@section('subheading', 'Manage staff accounts, customer accounts, and assigned roles.')

@section('content')
<div class="ta-page">
    <div class="ta-kpis ta-kpis-3">
        <div class="ta-kpi">
            <div class="ta-kpi-icon is-purple">
                <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
            </div>
            <div>
                <div class="ta-kpi-label">Total users</div>
                <div class="ta-kpi-value">{{ $stats['total'] }}</div>
            </div>
        </div>
        <div class="ta-kpi">
            <div class="ta-kpi-icon is-blue">
                <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
            </div>
            <div>
                <div class="ta-kpi-label">Staff</div>
                <div class="ta-kpi-value">{{ $stats['staff'] }}</div>
            </div>
        </div>
        <div class="ta-kpi">
            <div class="ta-kpi-icon is-brand">
                <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
            </div>
            <div>
                <div class="ta-kpi-label">Full admins</div>
                <div class="ta-kpi-value">{{ $stats['admins'] }}</div>
            </div>
        </div>
    </div>

    <div class="ta-toolbar">
        <div class="flex-1"></div>
        <a class="ta-btn" href="{{ route('admin.users.create') }}">+ Add User</a>
    </div>

    <div class="ta-table-card">
        <div class="ta-table-wrap">
            <table class="ta-table">
                <thead>
                    <tr>
                        <th>User</th>
                        <th>Email</th>
                        <th>Job</th>
                        <th>Roles</th>
                        <th>Group</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                @forelse($users as $user)
                    <tr>
                        <td>
                            <div style="display:flex;align-items:center;gap:10px;">
                                <div style="width:36px;height:36px;border-radius:50%;overflow:hidden;background:#f3f4f6;border:1px solid #e5e7eb;display:grid;place-items:center;font-weight:800;font-size:12px;color:#6b7280;flex:0 0 36px;">
                                    @if($user->avatarUrl())
                                        <img src="{{ $user->avatarUrl() }}" alt="" style="width:100%;height:100%;object-fit:cover;">
                                    @else
                                        {{ $user->initials() }}
                                    @endif
                                </div>
                                <div>
                                    <div class="ta-name">{{ $user->name }}</div>
                                    @if($user->employee_code)
                                        <div class="ta-muted">{{ $user->employee_code }}</div>
                                    @endif
                                </div>
                            </div>
                        </td>
                        <td>{{ $user->email }}</td>
                        <td>{{ $user->job_title ?: '—' }}</td>
                        <td>
                            <div class="role-badges">
                                @forelse($user->rolesCollection() as $role)
                                    <span class="ta-pill ta-pill-info">{{ $role->name }}</span>
                                @empty
                                    <span class="ta-muted">—</span>
                                @endforelse
                            </div>
                        </td>
                        <td>{{ $user->group?->name ?: '—' }}</td>
                        <td>
                            <div class="ta-actions">
                                <a class="ta-btn-outline ta-btn-sm" href="{{ route('admin.users.edit', $user) }}">Edit</a>
                                <a class="ta-btn-outline ta-btn-sm" href="{{ route('admin.users.print-card', $user) }}" target="_blank" rel="noopener">Print Card</a>
                                <a class="ta-btn-outline ta-btn-sm" href="{{ route('admin.users.assign-roles', $user) }}">Assign Roles</a>
                                <form method="POST" action="{{ route('admin.users.reset-password', $user) }}" onsubmit="return confirm('Reset this password to password123?')">
                                    @csrf
                                    <button class="ta-btn-outline ta-btn-sm" type="submit">Reset Password</button>
                                </form>
                                <form method="POST" action="{{ route('admin.users.destroy', $user) }}">
                                    @csrf @method('DELETE')
                                    <button class="ta-btn-danger ta-btn-sm" type="submit" onclick="return confirm('Delete this user?')">Delete</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="ta-empty">No users yet.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
        <div class="ta-table-footer">{{ $users->links() }}</div>
    </div>
</div>
@endsection
