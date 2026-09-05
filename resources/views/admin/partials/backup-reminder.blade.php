@php
    $backupReminder = $backupReminder ?? null;
    if ($backupReminder === null && auth()->user()?->hasPermission('backup_database')) {
        try {
            \Illuminate\Support\Facades\Storage::makeDirectory('backups');
            $files = collect(\Illuminate\Support\Facades\Storage::files('backups'));
            if ($files->isEmpty()) {
                $backupReminder = ['message' => 'No backup found yet. Create one before closing for the day.'];
            } else {
                $latest = $files->sortByDesc(fn ($f) => \Illuminate\Support\Facades\Storage::lastModified($f))->first();
                $age = now()->timestamp - \Illuminate\Support\Facades\Storage::lastModified($latest);
                if ($age > 86400) {
                    $days = max(1, (int) floor($age / 86400));
                    $backupReminder = [
                        'message' => 'Your last backup was '.$days.' day'.($days === 1 ? '' : 's').' ago. Create one before closing for the day.',
                    ];
                }
            }
        } catch (\Throwable) {
            $backupReminder = null;
        }
    }
@endphp
@if(!empty($backupReminder))
<div class="card" style="padding:14px 16px;margin-bottom:14px;border-color:#fcd34d;background:#fffbeb;display:flex;justify-content:space-between;gap:12px;flex-wrap:wrap;align-items:center;">
    <div><strong>Backup reminder:</strong> {{ $backupReminder['message'] }}</div>
    <a class="btn" href="{{ route('admin.backups.index') }}">Backup now</a>
</div>
@endif
