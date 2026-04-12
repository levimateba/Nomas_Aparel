@extends('layouts.admin')
@php use Illuminate\Support\Str; @endphp
@section('title','About Sections')
@section('content')
    <h2>About Sections</h2>
    <a class="btn" href="{{ route('admin.about.create') }}">Create About Section</a>
    <div class="card">
        <table>
            <thead><tr><th>Title</th><th>Content</th><th>Actions</th></tr></thead>
            <tbody>
            @forelse($aboutSections as $section)
                <tr>
                    <td>{{ $section->title }}</td>
                    <td>{{ Str::limit($section->content, 70) }}</td>
                    <td>
                        <a class="btn btn-secondary" href="{{ route('home') }}#about" target="_blank" rel="noopener">View</a>
                        <a class="btn btn-secondary" href="{{ route('admin.about.edit', $section) }}">Edit</a>
                        <form method="POST" action="{{ route('admin.about.destroy', $section) }}" style="display:inline">
                            @csrf
                            @method('DELETE')
                            <button class="btn" type="submit" onclick="return confirm('Delete this section?')">Delete</button>
                        </form>
                    </td>
                </tr>
            @empty
                <tr><td colspan="3">No records yet.</td></tr>
            @endforelse
            </tbody>
        </table>
        {{ $aboutSections->links() }}
    </div>
@endsection
