@extends('layouts.admin')
@section('title', 'Backup & Restore')
@section('heading', 'Backup & Restore')
@section('subheading', 'Create a copy of the database and restore it when needed.')

@section('content')
    <div class="backup-grid">
        @if(auth()->user()?->hasPermission('backup_database'))
        <div class="card">
            <h3 style="margin-top:0;">Create backup</h3>
            @if($driver === 'sqlite')
                <p class="muted">Copies the current SQLite database into storage.</p>
                <form method="POST" action="{{ route('admin.backups.store') }}">
                    @csrf
                    <button type="submit">Create Backup</button>
                </form>
            @else
                <p class="muted">SQLite backup is available when <code>DB_CONNECTION=sqlite</code>. Current driver: {{ $driver }}.</p>
            @endif
        </div>
        @endif
        @if(auth()->user()?->hasPermission('restore_database'))
        <div class="card">
            <h3 style="margin-top:0;">Restore backup</h3>
            @if($driver === 'sqlite')
                <p class="muted">Upload a <code>.sqlite</code> file. An automatic backup is created first.</p>
                <form method="POST" action="{{ route('admin.backups.restore') }}" enctype="multipart/form-data">
                    @csrf
                    <input type="file" name="backup" accept=".sqlite" required>
                    <button class="btn-danger" type="submit" onclick="return confirm('Restore this backup? The current database will be replaced.')">Restore Backup</button>
                </form>
            @else
                <p class="muted">SQLite restore is available when <code>DB_CONNECTION=sqlite</code>.</p>
            @endif
        </div>
        @endif
    </div>

    <div class="card">
        <div class="table-wrap">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>File</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                @forelse($backups as $backup)
                    <tr>
                        <td>{{ basename($backup) }}</td>
                        <td class="row-actions">
                            <a class="btn btn-secondary" href="{{ route('admin.backups.download', basename($backup)) }}">Download</a>
                            <form method="POST" action="{{ route('admin.backups.destroy', basename($backup)) }}">
                                @csrf @method('DELETE')
                                <button class="btn btn-danger" type="submit" onclick="return confirm('Delete this backup?')">Delete</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="2" class="empty-cell">No backups yet.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection
