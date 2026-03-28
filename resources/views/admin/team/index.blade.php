@extends('layouts.admin')
@section('title','Team')
@section('content')
    <h2>Team</h2>
    <a class="btn" href="{{ route('admin.team.create') }}">Add team member</a>
    <div class="card">
        <table>
            <thead><tr><th>Name</th><th>Position</th><th>Description</th><th>Actions</th></tr></thead>
            <tbody>
            @forelse($members as $member)
                <tr>
                    <td>{{ $member->name }}</td>
                    <td>{{ $member->position }}</td>
                    <td>{{ \Illuminate\Support\Str::limit($member->description, 80) }}</td>
                    <td>
                        <a class="btn btn-secondary" href="{{ route('home') }}#team" target="_blank" rel="noopener">View</a>
                        <a class="btn btn-secondary" href="{{ route('admin.team.edit', $member) }}">Edit</a>
                        <form method="POST" action="{{ route('admin.team.destroy', $member) }}" style="display:inline">
                            @csrf
                            @method('DELETE')
                            <button class="btn" type="submit" onclick="return confirm('Delete this team member?')">Delete</button>
                        </form>
                    </td>
                </tr>
            @empty
                <tr><td colspan="4">No team members yet.</td></tr>
            @endforelse
            </tbody>
        </table>
        {{ $members->links() }}
    </div>
@endsection
