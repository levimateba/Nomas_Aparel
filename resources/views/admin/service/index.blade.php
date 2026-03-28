@extends('layouts.admin')
@php use Illuminate\Support\Str; @endphp
@section('title','Services')
@section('content')
    <h2>Services</h2>
    <a class="btn" href="{{ route('admin.service.create') }}">New service</a>
    <div class="card">
        <table>
            <thead><tr><th>Title</th><th>Description</th><th>Actions</th></tr></thead>
            <tbody>
            @forelse($services as $service)
                <tr>
                    <td>{{ $service->title }}</td>
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
                <tr><td colspan="3">No records yet.</td></tr>
            @endforelse
            </tbody>
        </table>
        {{ $services->links() }}
    </div>
@endsection
