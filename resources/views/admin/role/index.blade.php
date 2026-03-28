@extends('layouts.admin')
@section('title','Roles')
@section('content')
    <h2>Roles</h2>
    <a class="btn" href="{{ route('admin.roles.create') }}">Add role</a>
    <div class="card">
        <table>
            <thead><tr><th>Name</th><th>Description</th><th>Permissions</th><th>Actions</th></tr></thead>
            <tbody>
            @forelse($roles as $role)
                <tr>
                    <td>{{ $role->name }}</td>
                    <td>{{ $role->description ?: 'No description' }}</td>
                    <td>{{ $role->permissions->pluck('name')->join(', ') ?: 'None' }}</td>
                    <td>
                        <a class="btn btn-secondary" href="{{ route('admin.roles.edit', $role) }}">Edit</a>
                        <form method="POST" action="{{ route('admin.roles.destroy', $role) }}" style="display:inline">
                            @csrf
                            @method('DELETE')
                            <button class="btn" type="submit" onclick="return confirm('Delete this role?')">Delete</button>
                        </form>
                    </td>
                </tr>
            @empty
                <tr><td colspan="4">No roles yet.</td></tr>
            @endforelse
            </tbody>
        </table>
        {{ $roles->links() }}
    </div>
@endsection
