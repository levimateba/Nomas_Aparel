@extends('layouts.admin')
@section('title','User Groups')
@section('content')
    <div class="page-head">
        <h2>User Groups</h2>
        <a class="btn" href="{{ route('admin.user-groups.create') }}">Add group</a>
    </div>
    <div class="card">
        <table>
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Users</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
            @forelse($groups as $group)
                <tr>
                    <td>{{ $group->name }}</td>
                    <td>{{ $group->users_count }}</td>
                    <td>
                        <a class="btn btn-secondary" href="{{ route('admin.user-groups.edit', $group) }}">Edit</a>
                        <form method="POST" action="{{ route('admin.user-groups.destroy', $group) }}">
                            @csrf
                            @method('DELETE')
                            <button class="btn" type="submit" onclick="return confirm('Delete this group?')">Delete</button>
                        </form>
                    </td>
                </tr>
            @empty
                <tr><td colspan="3">No groups yet.</td></tr>
            @endforelse
            </tbody>
        </table>
        {{ $groups->links() }}
    </div>
@endsection
