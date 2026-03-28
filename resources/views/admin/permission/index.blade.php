@extends('layouts.admin')
@section('title','Permissions')
@section('content')
    <h2>Permissions</h2>
    <a class="btn" href="{{ route('admin.permissions.create') }}">Add permission</a>
    <div class="card">
        <table>
            <thead><tr><th>Name</th><th>Description</th><th>Roles</th><th>Actions</th></tr></thead>
            <tbody>
            @forelse($permissions as $permission)
                <tr>
                    <td>{{ $permission->name }}</td>
                    <td>{{ $permission->description ?: 'No description' }}</td>
                    <td>{{ $permission->roles->pluck('name')->join(', ') ?: 'Not assigned' }}</td>
                    <td>
                        <a class="btn btn-secondary" href="{{ route('admin.permissions.edit', $permission) }}">Edit</a>
                        <form method="POST" action="{{ route('admin.permissions.destroy', $permission) }}" style="display:inline">
                            @csrf
                            @method('DELETE')
                            <button class="btn" type="submit" onclick="return confirm('Delete this permission?')">Delete</button>
                        </form>
                    </td>
                </tr>
            @empty
                <tr><td colspan="4">No permissions yet.</td></tr>
            @endforelse
            </tbody>
        </table>
        {{ $permissions->links() }}
    </div>
@endsection
