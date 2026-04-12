@extends('layouts.admin')
@section('title','Gallery')
@section('content')
    <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:16px;">
        <h2 style="margin:0;">Gallery</h2>
        <a class="btn" href="{{ route('admin.gallery.create') }}">+ Add Image</a>
    </div>
    @if(session('success'))
        <div class="alert">{{ session('success') }}</div>
    @endif
    <div class="card">
        <table>
            <thead>
                <tr>
                    <th style="width:110px;">Preview</th>
                    <th>Title</th>
                    <th>Category</th>
                    <th>Short Description</th>
                    <th style="width:130px;">Actions</th>
                </tr>
            </thead>
            <tbody>
            @forelse($items as $item)
                <tr>
                    <td>
                        <img src="{{ $item->image }}" alt="{{ $item->title }}"
                            style="width:100px;height:70px;object-fit:cover;border-radius:8px;box-shadow:0 2px 8px rgba(22,69,110,0.12);">
                    </td>
                    <td>
                        <strong>{{ $item->title }}</strong>
                        @if($item->description)
                            <div style="font-size:0.82rem;color:#888;margin-top:4px;">{{ \Illuminate\Support\Str::limit($item->description, 70) }}</div>
                        @endif
                    </td>
                    <td>
                        @if($item->category)
                            <span style="display:inline-block;padding:4px 12px;border-radius:999px;background:rgba(22,87,82,0.1);color:#165752;font-size:0.78rem;font-weight:700;letter-spacing:0.06em;text-transform:uppercase;">
                                {{ $item->category }}
                            </span>
                        @else
                            <span style="color:#bbb;font-size:0.85rem;">—</span>
                        @endif
                    </td>
                    <td style="font-size:0.9rem;color:#555;">{{ $item->short_description ?: '—' }}</td>
                    <td>
                        <a class="btn btn-secondary" href="{{ route('admin.gallery.edit', $item) }}">Edit</a>
                        <form method="POST" action="{{ route('admin.gallery.destroy', $item) }}" style="display:inline;">
                            @csrf
                            @method('DELETE')
                            <button class="btn" style="background:linear-gradient(135deg,#c0392b,#e74c3c);" type="submit"
                                onclick="return confirm('Delete this gallery item?')">Delete</button>
                        </form>
                    </td>
                </tr>
            @empty
                <tr><td colspan="5" style="text-align:center;color:#aaa;padding:30px;">No gallery images yet. <a href="{{ route('admin.gallery.create') }}">Add the first one.</a></td></tr>
            @endforelse
            </tbody>
        </table>
        <div style="margin-top:16px;">{{ $items->links() }}</div>
    </div>
@endsection
