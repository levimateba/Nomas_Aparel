@extends('layouts.admin')
@section('title','Gallery')
@section('content')
    <h2>Gallery</h2>
    <a class="btn" href="{{ route('admin.gallery.create') }}">Add gallery image</a>
    <div class="card">
        <table>
            <thead><tr><th>Preview</th><th>Title</th><th>Actions</th></tr></thead>
            <tbody>
            @forelse($items as $item)
                <tr>
                    <td><img src="{{ $item->image }}" alt="{{ $item->title }}" style="width:100px;height:70px;object-fit:cover;border-radius:8px;"></td>
                    <td>{{ $item->title }}</td>
                    <td>
                        <a class="btn btn-secondary" href="{{ route('admin.gallery.edit', $item) }}">Edit</a>
                        <form method="POST" action="{{ route('admin.gallery.destroy', $item) }}" style="display:inline">
                            @csrf
                            @method('DELETE')
                            <button class="btn" type="submit" onclick="return confirm('Delete this gallery item?')">Delete</button>
                        </form>
                    </td>
                </tr>
            @empty
                <tr><td colspan="3">No gallery items yet.</td></tr>
            @endforelse
            </tbody>
        </table>
        {{ $items->links() }}
    </div>
@endsection
