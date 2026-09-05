@extends('layouts.admin')
@section('title', 'Audit Logs')
@section('heading', 'Audit Logs')
@section('subheading', 'Track user actions and system changes across the platform.')

@section('content')
<div class="ta-page">
@if(!empty($backupReminder))
<x-admin.list-card>
    <div class="flex flex-wrap items-center justify-between gap-3">
        <div><strong>Backup reminder:</strong> {{ $backupReminder['message'] }}</div>
        <a class="ta-btn" href="{{ route('admin.backups.index') }}">Backup now</a>
    </div>
</x-admin.list-card>
@endif

<x-admin.list-card title="Audit trail">
    <x-slot:actions>
        <div class="ta-actions">
            <a class="ta-btn-outline ta-btn-sm" href="{{ route('admin.audit-logs.export', array_merge(request()->query(), ['type' => 'pdf'])) }}">Export PDF</a>
            <a class="ta-btn-outline ta-btn-sm" href="{{ route('admin.audit-logs.export', array_merge(request()->query(), ['type' => 'excel'])) }}">Export Excel</a>
            @if(auth()->user()?->isFullAdmin() || auth()->user()?->hasPermission('view_audit_logs'))
            <form method="POST" action="{{ route('admin.audit-logs.toggle') }}" style="margin:0;">
                @csrf
                <button type="submit" class="ta-btn ta-btn-sm">
                    {{ $trailEnabled ? 'Turn Off Audit Trail' : 'Turn On Audit Trail' }}
                </button>
            </form>
            @endif
        </div>
    </x-slot:actions>
    @if($trailEnabled)
        <p class="ta-muted" style="margin:0;">New system actions are being recorded.</p>
    @else
        <p class="ta-muted" style="margin:0;">Audit trail is off{{ $disabledByName ? ' (by '.$disabledByName.')' : '' }}{{ $disabledAt ? ' since '.$disabledAt : '' }}.</p>
    @endif
</x-admin.list-card>

<div class="ta-toolbar">
    <form method="GET" style="display:contents;">
        <div class="ta-field" style="flex:2;">
            <label>Search</label>
            <input type="text" name="q" value="{{ request('q') }}" placeholder="Search by action, user, or record">
        </div>
        <button class="ta-btn" type="submit">Search</button>
        <a class="ta-btn-outline" href="{{ route('admin.audit-logs.index') }}">Reset</a>
    </form>
</div>

<div class="ta-table-card">
    @if(auth()->user()?->isFullAdmin())
        <form method="POST" action="{{ route('admin.audit-logs.clear') }}" class="ta-bulk" onsubmit="return confirm('Delete ALL audit logs?')">
            @csrf
            <button type="submit" class="ta-btn-danger ta-btn-sm">Delete all logs</button>
        </form>
    @endif

    <form method="POST" action="{{ route('admin.audit-logs.bulk-destroy') }}" id="audit-bulk-form">
        @csrf
        @method('DELETE')
        <div class="ta-bulk">
            <button type="button" class="ta-btn-outline ta-btn-sm" id="audit-select-all">Select all on page</button>
            <button type="button" class="ta-btn-outline ta-btn-sm" id="audit-clear-sel">Clear selection</button>
            @if(auth()->user()?->isFullAdmin())
                <button type="submit" class="ta-btn-danger ta-btn-sm" onclick="return confirm('Delete selected logs?')">Delete selected</button>
            @endif
        </div>

        <div class="ta-table-wrap">
            <table class="ta-table">
                <thead>
                    <tr>
                        <th><input type="checkbox" id="audit-check-all"></th>
                        <th>No.</th>
                        <th>Date</th>
                        <th>User</th>
                        <th>Action</th>
                        <th>Target</th>
                        <th>Description</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($logs as $i => $log)
                        <tr>
                            <td><input type="checkbox" name="ids[]" value="{{ $log->id }}" class="audit-row-check"></td>
                            <td>{{ $logs->firstItem() + $i }}</td>
                            <td style="white-space:nowrap;">{{ $log->created_at->format('d M Y H:i:s') }}</td>
                            <td>{{ $log->user?->name ?: 'System' }}</td>
                            <td><code>{{ $log->action }}</code></td>
                            <td>{{ $log->target_label ?: '—' }}</td>
                            <td>{{ $log->description }}</td>
                            <td>
                                @if(auth()->user()?->isFullAdmin())
                                    <div class="ta-actions">
                                        <form method="POST" action="{{ route('admin.audit-logs.destroy', $log) }}" onsubmit="return confirm('Delete this log?')">
                                            @csrf @method('DELETE')
                                            <button type="submit" class="ta-btn-danger ta-btn-sm">Delete</button>
                                        </form>
                                    </div>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="8" class="ta-empty">No audit entries yet.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </form>
    <div class="ta-table-footer">{{ $logs->links() }}</div>
</div>
</div>
<script>
(function(){
    const all = document.getElementById('audit-check-all');
    const boxes = () => Array.from(document.querySelectorAll('.audit-row-check'));
    const setAll = (v) => boxes().forEach(b => b.checked = v);
    all && all.addEventListener('change', () => setAll(all.checked));
    document.getElementById('audit-select-all')?.addEventListener('click', () => { setAll(true); if(all) all.checked = true; });
    document.getElementById('audit-clear-sel')?.addEventListener('click', () => { setAll(false); if(all) all.checked = false; });
})();
</script>
@endsection
