@extends('layouts.admin')
@section('title','Contact Details')
@section('content')
    <h2>Contact Details</h2>
    <a class="btn" href="{{ route('admin.contact.create') }}">Add contact</a>
    <div class="card">
        <table>
            <thead><tr><th>Label</th><th>Value</th><th>Type</th><th>Actions</th></tr></thead>
            <tbody>
            @forelse($contacts as $contact)
                <tr>
                    <td>{{ $contact->label }}</td>
                    <td>{{ $contact->value }}</td>
                    <td>{{ $contact->type }}</td>
                    <td>
                        <a class="btn btn-secondary" href="{{ route('home') }}#contact" target="_blank" rel="noopener">View</a>
                        <a class="btn btn-secondary" href="{{ route('admin.contact.edit', $contact) }}">Edit</a>
                        <form method="POST" action="{{ route('admin.contact.destroy', $contact) }}" style="display:inline">
                            @csrf
                            @method('DELETE')
                            <button class="btn" type="submit" onclick="return confirm('Delete this contact?')">Delete</button>
                        </form>
                    </td>
                </tr>
            @empty
                <tr><td colspan="4">No records yet.</td></tr>
            @endforelse
            </tbody>
        </table>
        {{ $contacts->links() }}
    </div>
@endsection
