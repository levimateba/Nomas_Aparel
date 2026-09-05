@extends('layouts.admin')
@section('title', 'Roles')

@section('page_header')
<div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
    <div>
        <nav class="mb-2 flex flex-wrap items-center gap-1.5 text-sm text-gray-400">
            <a href="{{ route('admin.dashboard') }}" class="hover:text-brand-500">Dashboard</a>
            <span>/</span>
            <span>Admin</span>
            <span>/</span>
            <span class="text-gray-600 dark:text-gray-300">Roles</span>
        </nav>
        <h1 class="text-2xl font-bold text-gray-800 dark:text-white/90">Roles</h1>
        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Define roles and assign permissions to control access.</p>
    </div>
    <div class="flex flex-wrap gap-2">
        <a class="ta-btn" href="{{ route('admin.roles.create') }}"><span class="text-lg leading-none">+</span> New Role</a>
    </div>
</div>
@endsection

@section('content')
<div class="ta-page">
    <div class="ta-kpis ta-kpis-3">
        <div class="ta-kpi">
            <div class="ta-kpi-icon is-purple">
                <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
            </div>
            <div>
                <div class="ta-kpi-label">Total Roles</div>
                <div class="ta-kpi-value">{{ number_format($stats['total']) }}</div>
            </div>
        </div>
        <div class="ta-kpi">
            <div class="ta-kpi-icon is-brand">
                <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"/></svg>
            </div>
            <div>
                <div class="ta-kpi-label">Permissions Linked</div>
                <div class="ta-kpi-value">{{ number_format($stats['permission_links']) }}</div>
            </div>
        </div>
        <div class="ta-kpi">
            <div class="ta-kpi-icon is-success">
                <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
            </div>
            <div>
                <div class="ta-kpi-label">With Permissions</div>
                <div class="ta-kpi-value">{{ number_format($stats['with_permissions']) }}</div>
            </div>
        </div>
    </div>

    <div class="ta-table-card">
        <div class="ta-table-wrap">
            <table class="ta-table">
                <thead>
                    <tr>
                        <th>Role</th>
                        <th>Permissions</th>
                        <th class="text-right">Actions</th>
                    </tr>
                </thead>
                <tbody>
                @forelse($roles as $role)
                    <tr>
                        <td>
                            <div class="ta-name">{{ $role->name }}</div>
                            <div class="ta-muted">{{ $role->slug }}</div>
                            @if($role->description)
                                <div class="mt-1 text-sm text-gray-500">{{ $role->description }}</div>
                            @endif
                        </td>
                        <td>
                            <div class="flex flex-wrap gap-1.5">
                                @forelse($role->permissions->take(8) as $permission)
                                    <span class="ta-pill ta-pill-info">{{ \App\Support\PermissionCatalog::label($permission->name) }}</span>
                                @empty
                                    <span class="ta-muted">No permissions assigned</span>
                                @endforelse
                                @if($role->permissions->count() > 8)
                                    <span class="ta-pill ta-pill-muted">+{{ $role->permissions->count() - 8 }} more</span>
                                @endif
                            </div>
                        </td>
                        <td>
                            <div class="ta-actions justify-end">
                                <a class="ta-btn ta-btn-sm" href="{{ route('admin.roles.assign-permissions', $role) }}">Permissions</a>
                                <a class="ta-btn-outline ta-btn-sm" href="{{ route('admin.roles.edit', $role) }}">Edit</a>
                                <form method="POST" action="{{ route('admin.roles.destroy', $role) }}">
                                    @csrf @method('DELETE')
                                    <button class="ta-btn-danger ta-btn-sm" type="submit" onclick="return confirm('Delete this role?')">Delete</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="3" class="ta-empty">No roles yet.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
        <div class="ta-table-footer">{{ $roles->links() }}</div>
    </div>
</div>
@endsection
