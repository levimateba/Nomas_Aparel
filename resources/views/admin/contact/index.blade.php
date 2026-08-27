@extends('layouts.admin')
@section('title', 'Contacts')
@section('heading', 'Contacts')
@section('subheading', 'Store phone, email, and location details shown on the site.')

@section('content')
    <div class="admin-kpis">
        <div class="admin-kpi"><span>Total Contacts</span><strong>{{ $stats['total'] }}</strong></div>
    </div>
    <div class="admin-toolbar">
        <div></div>
        <a class="btn" href="{{ route('admin.contact.create') }}">+ Add Contact</a>
    </div>
    <div class="card">
        <div class="table-wrap">
            <table class="admin-table">
                <thead>
                    <tr><th>Label</th><th>Value</th><th>Type</th><th>Actions</th></tr>
                </thead>
                <tbody>
                @forelse($contacts as $contact)
                    <tr>
                        <td><strong>{{ $contact->label }}</strong></td>
                        <td>{{ $contact->value }}</td>
                        <td><span class="status-pill info">{{ $contact->type }}</span></td>
                        <td class="row-actions">
                            <a class="btn btn-secondary" href="{{ route('admin.contact.edit', $contact) }}">Edit</a>
                            <form method="POST" action="{{ route('admin.contact.destroy', $contact) }}">
                                @csrf @method('DELETE')
                                <button class="btn btn-danger" type="submit" onclick="return confirm('Delete this contact?')">Delete</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="4" class="empty-cell">No contact details yet.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
        <div style="margin-top:12px;">{{ $contacts->links() }}</div>
    </div>
@endsection
