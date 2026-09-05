@extends('layouts.admin')
@section('title', 'User Groups')

@section('page_header')
<div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
    <div>
        <nav class="mb-2 flex flex-wrap items-center gap-1.5 text-sm text-gray-400">
            <a href="{{ route('admin.dashboard') }}" class="hover:text-brand-500">Dashboard</a>
            <span>/</span>
            <span>Admin</span>
            <span>/</span>
            <span class="text-gray-600 dark:text-gray-300">User Groups</span>
        </nav>
        <h1 class="text-2xl font-bold text-gray-800 dark:text-white/90">User Groups</h1>
        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Organize staff into teams or departments.</p>
    </div>
    <div>
        <a class="ta-btn" href="{{ route('admin.user-groups.create') }}"><span class="text-lg leading-none">+</span> Add group</a>
    </div>
</div>
@endsection

@section('content')
<div class="ta-page">
    <div class="ta-kpis ta-kpis-3">
        <div class="ta-kpi">
            <div class="ta-kpi-icon is-purple">
                <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a4 4 0 00-4-4h-1M9 20H4v-2a4 4 0 014-4h1m4-8a4 4 0 11-8 0 4 4 0 018 0zm6 4a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
            </div>
            <div>
                <div class="ta-kpi-label">Total Groups</div>
                <div class="ta-kpi-value">{{ number_format($stats['total']) }}</div>
            </div>
        </div>
        <div class="ta-kpi">
            <div class="ta-kpi-icon is-success">
                <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            </div>
            <div>
                <div class="ta-kpi-label">With Members</div>
                <div class="ta-kpi-value">{{ number_format($stats['with_users']) }}</div>
            </div>
        </div>
        <div class="ta-kpi">
            <div class="ta-kpi-icon is-brand">
                <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
            </div>
            <div>
                <div class="ta-kpi-label">Assigned Users</div>
                <div class="ta-kpi-value">{{ number_format($stats['members']) }}</div>
            </div>
        </div>
    </div>

    <div class="ta-table-card">
        <div class="ta-table-wrap">
            <table class="ta-table">
                <thead>
                    <tr>
                        <th>Group</th>
                        <th>Users</th>
                        <th class="text-right">Actions</th>
                    </tr>
                </thead>
                <tbody>
                @forelse($groups as $group)
                    <tr>
                        <td>
                            <div class="ta-name">{{ $group->name }}</div>
                            @if($group->description)
                                <div class="ta-muted">{{ $group->description }}</div>
                            @endif
                        </td>
                        <td>
                            <span class="ta-pill {{ $group->users_count ? 'ta-pill-success' : 'ta-pill-muted' }}">
                                {{ $group->users_count }} user{{ $group->users_count === 1 ? '' : 's' }}
                            </span>
                        </td>
                        <td>
                            <div class="ta-actions justify-end">
                                <a class="ta-btn-outline ta-btn-sm" href="{{ route('admin.user-groups.edit', $group) }}">Edit</a>
                                <form method="POST" action="{{ route('admin.user-groups.destroy', $group) }}">
                                    @csrf @method('DELETE')
                                    <button class="ta-btn-danger ta-btn-sm" type="submit" onclick="return confirm('Delete this group?')">Delete</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="3" class="ta-empty">No groups yet. <a href="{{ route('admin.user-groups.create') }}" class="font-semibold text-brand-600">Add the first group</a>.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
        <div class="ta-table-footer">{{ $groups->links() }}</div>
    </div>
</div>
@endsection
