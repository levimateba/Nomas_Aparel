@extends('layouts.admin')
@section('title', 'Backup & Restore')

@section('page_header')
<div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
    <div>
        <nav class="mb-2 flex flex-wrap items-center gap-1.5 text-sm text-gray-400">
            <a href="{{ route('admin.dashboard') }}" class="hover:text-brand-500">Dashboard</a>
            <span>/</span>
            <span>Admin</span>
            <span>/</span>
            <span class="text-gray-600 dark:text-gray-300">Backups</span>
        </nav>
        <h1 class="text-2xl font-bold text-gray-800 dark:text-white/90">Backup &amp; Restore</h1>
        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Create a copy of the database and restore it when needed.</p>
    </div>
</div>
@endsection

@section('content')
@php
    $formatBytes = function (int $bytes): string {
        if ($bytes < 1024) return $bytes.' B';
        if ($bytes < 1048576) return number_format($bytes / 1024, 1).' KB';
        return number_format($bytes / 1048576, 2).' MB';
    };
@endphp

<div class="ta-page">
    <div class="ta-kpis ta-kpis-3">
        <div class="ta-kpi">
            <div class="ta-kpi-icon is-brand">
                <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4 7v10c0 2.21 3.582 4 8 4s8-1.79 8-4V7M4 7c0 2.21 3.582 4 8 4s8-1.79 8-4M4 7c0-2.21 3.582-4 8-4s8 1.79 8 4"/></svg>
            </div>
            <div>
                <div class="ta-kpi-label">Database driver</div>
                <div class="ta-kpi-value" style="font-size:1.35rem;">{{ strtoupper($driver) }}</div>
            </div>
        </div>
        <div class="ta-kpi">
            <div class="ta-kpi-icon is-success">
                <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4"/></svg>
            </div>
            <div>
                <div class="ta-kpi-label">Stored backups</div>
                <div class="ta-kpi-value">{{ number_format($stats['count']) }}</div>
            </div>
        </div>
        <div class="ta-kpi">
            <div class="ta-kpi-icon is-blue">
                <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4 7v10c0 2.21 3.582 4 8 4s8-1.79 8-4V7"/></svg>
            </div>
            <div>
                <div class="ta-kpi-label">Total size</div>
                <div class="ta-kpi-value" style="font-size:1.35rem;">{{ $formatBytes((int) $stats['total_size']) }}</div>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 gap-5 lg:grid-cols-2">
        @if(auth()->user()?->hasPermission('backup_database') || auth()->user()?->isFullAdmin())
            <div class="rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03]">
                <div class="border-b border-gray-100 px-5 py-4 dark:border-gray-800">
                    <h2 class="text-base font-semibold text-gray-800 dark:text-white/90">Create backup</h2>
                    <p class="mt-1 text-sm text-gray-500">Copy the current database into secure storage.</p>
                </div>
                <div class="p-5">
                    @if($driver === 'sqlite')
                        <p class="mb-4 text-sm text-gray-600 dark:text-gray-400">Creates a timestamped <code>.sqlite</code> file under storage.</p>
                        <form method="POST" action="{{ route('admin.backups.store') }}">
                            @csrf
                            <button type="submit" class="ta-btn">Create Backup</button>
                        </form>
                    @else
                        <div class="rounded-xl border border-warning-200 bg-warning-50 px-4 py-3 text-sm text-warning-800 dark:border-warning-500/30 dark:bg-warning-500/10 dark:text-warning-200">
                            SQLite backup is available when <code>DB_CONNECTION=sqlite</code>. Current driver: <strong>{{ $driver }}</strong>.
                        </div>
                    @endif
                </div>
            </div>
        @endif

        @if(auth()->user()?->hasPermission('restore_database') || auth()->user()?->isFullAdmin())
            <div class="rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03]">
                <div class="border-b border-gray-100 px-5 py-4 dark:border-gray-800">
                    <h2 class="text-base font-semibold text-gray-800 dark:text-white/90">Restore backup</h2>
                    <p class="mt-1 text-sm text-gray-500">Replace the live database from an uploaded file.</p>
                </div>
                <div class="p-5">
                    @if($driver === 'sqlite')
                        <p class="mb-4 text-sm text-gray-600 dark:text-gray-400">Upload a <code>.sqlite</code> file. An automatic safety backup is created first.</p>
                        <form method="POST" action="{{ route('admin.backups.restore') }}" enctype="multipart/form-data" class="space-y-4">
                            @csrf
                            <div class="ta-field">
                                <label>Backup file</label>
                                <input type="file" name="backup" accept=".sqlite" class="ta-input" required>
                            </div>
                            <button class="ta-btn-danger" type="submit" onclick="return confirm('Restore this backup? The current database will be replaced.')">Restore Backup</button>
                        </form>
                    @else
                        <div class="rounded-xl border border-warning-200 bg-warning-50 px-4 py-3 text-sm text-warning-800 dark:border-warning-500/30 dark:bg-warning-500/10 dark:text-warning-200">
                            SQLite restore is available when <code>DB_CONNECTION=sqlite</code>.
                        </div>
                    @endif
                </div>
            </div>
        @endif
    </div>

    <div class="ta-table-card">
        <div class="border-b border-gray-100 px-5 py-4 dark:border-gray-800">
            <h2 class="text-base font-semibold text-gray-800 dark:text-white/90">Stored backups</h2>
        </div>
        <div class="ta-table-wrap">
            <table class="ta-table">
                <thead>
                    <tr>
                        <th>File</th>
                        <th>Size</th>
                        <th>Created</th>
                        <th class="text-right">Actions</th>
                    </tr>
                </thead>
                <tbody>
                @forelse($backups as $backup)
                    <tr>
                        <td>
                            <div class="ta-name font-mono text-sm">{{ $backup['name'] }}</div>
                        </td>
                        <td>{{ $formatBytes((int) $backup['size']) }}</td>
                        <td>
                            @if($backup['modified'])
                                {{ \Illuminate\Support\Carbon::createFromTimestamp($backup['modified'])->format('d M Y H:i') }}
                            @else
                                —
                            @endif
                        </td>
                        <td>
                            <div class="ta-actions justify-end">
                                <a class="ta-btn-outline ta-btn-sm" href="{{ route('admin.backups.download', $backup['name']) }}">Download</a>
                                <form method="POST" action="{{ route('admin.backups.destroy', $backup['name']) }}">
                                    @csrf @method('DELETE')
                                    <button class="ta-btn-danger ta-btn-sm" type="submit" onclick="return confirm('Delete this backup?')">Delete</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="4" class="ta-empty">No backups yet. Create one above.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
