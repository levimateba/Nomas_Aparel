@extends('layouts.admin')
@section('title', 'Permissions')
@section('heading', 'Permissions')
@section('subheading', 'Grouped catalogue of access controls used across the admin portal.')

@section('content')
    <div class="admin-kpis">
        <div class="admin-kpi"><span>Total permissions</span><strong>{{ $permissions->total() }}</strong></div>
        <div class="admin-kpi"><span>Catalogue groups</span><strong>{{ count($permissionGroups) }}</strong></div>
        <div class="admin-kpi"><span>Uncatalogued</span><strong>{{ $uncataloguedCount }}</strong></div>
    </div>
    <div class="admin-toolbar">
        <div></div>
        <a class="btn" href="{{ route('admin.permissions.create') }}">+ New Permission</a>
    </div>
    <div class="card">
        <div class="table-wrap">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>Label</th>
                        <th>Permission key</th>
                        <th>Group</th>
                        <th>Description</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                @forelse($permissions as $permission)
                    @php
                        $meta = \App\Support\PermissionCatalog::meta($permission->name);
                    @endphp
                    <tr>
                        <td><strong>{{ $meta['label'] ?? \App\Support\PermissionCatalog::label($permission->name) }}</strong></td>
                        <td><code class="perm-key">{{ $permission->name }}</code></td>
                        <td>{{ $meta['group'] ?? 'Uncatalogued' }}</td>
                        <td>{{ $meta['description'] ?? ($permission->description ?: '—') }}</td>
                        <td class="row-actions">
                            <a class="btn btn-secondary" href="{{ route('admin.permissions.edit', $permission) }}">Edit</a>
                            <form method="POST" action="{{ route('admin.permissions.destroy', $permission) }}">
                                @csrf @method('DELETE')
                                <button class="btn btn-danger" type="submit" onclick="return confirm('Delete this permission?')">Delete</button>
                            </form>
                            @unless($meta)
                                <span class="status-pill warn">Not in catalogue</span>
                            @endunless
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="empty-cell">No permissions yet.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
        <div style="margin-top:12px;">{{ $permissions->links() }}</div>
    </div>
@endsection
