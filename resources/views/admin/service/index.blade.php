@extends('layouts.admin')
@php use Illuminate\Support\Str; @endphp
@section('title','Services')
@section('content')
    <h2>Services</h2>
    <a class="btn" href="{{ route('admin.service.create') }}">New service</a>
    <form method="GET" action="{{ route('admin.service.index') }}" style="margin: 14px 0; display:flex; gap:10px; align-items:center; flex-wrap:wrap;">
        <select name="category" style="max-width: 360px;">
            <option value="">All Categories</option>
            @foreach($serviceCategories as $category)
                <option value="{{ $category }}" {{ request('category') === $category ? 'selected' : '' }}>{{ $category }}</option>
            @endforeach
        </select>
        <button class="btn btn-secondary" type="submit">Filter</button>
        @if(request('category'))
            <a class="btn btn-secondary" href="{{ route('admin.service.index') }}">Clear</a>
        @endif
    </form>
    <div class="card">
        <table>
            <thead><tr><th>Title</th><th>Category</th><th>Description</th><th>Actions</th></tr></thead>
            <tbody>
            @forelse($services as $service)
                <tr>
                    <td>{{ $service->title }}</td>
                    <td>{{ $service->category ?: 'Uncategorized' }}</td>
                    <td>{{ Str::limit($service->description, 70) }}</td>
                    <td>
                        <a class="btn btn-secondary" href="{{ route('home') }}#services" target="_blank" rel="noopener">View</a>
                        <a class="btn btn-secondary" href="{{ route('admin.service.edit', $service) }}">Edit</a>
                        <form method="POST" action="{{ route('admin.service.destroy', $service) }}" style="display:inline">
                            @csrf
                            @method('DELETE')
                            <button class="btn" type="submit" onclick="return confirm('Delete this service?')">Delete</button>
                        </form>
                    </td>
                </tr>
            @empty
                <tr><td colspan="4">No records yet.</td></tr>
            @endforelse
            </tbody>
        </table>
        {{ $services->links() }}
    </div>
@endsection
