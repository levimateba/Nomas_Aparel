@extends('layouts.admin')
@section('title','Users')
@section('content')
    <h2>Users</h2>
    <a class="btn" href="{{ route('admin.users.create') }}">Add user</a>
    <div class="card">
        <table>
            <thead><tr><th>Name</th><th>Email</th><th>Roles</th><th>User Group</th><th>Actions</th></tr></thead>
            <tbody>
            @forelse($users as $user)
                <tr>
                    <td>{{ $user->name }}</td>
                    <td>{{ $user->email }}</td>
                    <td>
                        <form method="POST" action="{{ route('admin.users.role.update', $user) }}">
                            @csrf
                            @method('PATCH')
                            <select name="role_id" onchange="this.form.submit()" style="padding:8px 10px;border-radius:10px;border:1px solid #d6dde3;min-width:140px;">
                                @foreach($roles as $role)
                                    <option value="{{ $role->id }}" {{ $user->role_id === $role->id ? 'selected' : '' }}>{{ $role->name }}</option>
                                @endforeach
                            </select>
                        </form>
                    </td>
                    <td>{{ $user->group?->name ?: 'Not assigned' }}</td>
                    <td>
                        <a class="btn btn-secondary" href="{{ route('admin.users.edit', $user) }}">Edit</a>
                        <form method="POST" action="{{ route('admin.users.destroy', $user) }}" style="display:inline">
                            @csrf
                            @method('DELETE')
                            <button class="btn" type="submit" onclick="return confirm('Delete this user?')">Delete</button>
                        </form>
                    </td>
                </tr>
            @empty
                <tr><td colspan="5">No users yet.</td></tr>
            @endforelse
            </tbody>
        </table>
        {{ $users->links() }}
    </div>
@endsection
