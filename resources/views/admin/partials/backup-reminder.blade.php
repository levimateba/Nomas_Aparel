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
<div class="mb-4 flex flex-wrap items-center justify-between gap-3 rounded-xl border border-warning-300 bg-warning-50 px-4 py-3 text-sm text-warning-800 dark:border-warning-500/40 dark:bg-warning-500/15 dark:text-warning-200">
    <div>
        <strong class="font-semibold text-warning-900 dark:text-warning-100">Backup reminder:</strong>
        <span class="text-warning-800 dark:text-warning-200"> {{ $backupReminder['message'] }}</span>
    </div>
    <a class="ta-btn ta-btn-sm shrink-0" href="{{ route('admin.backups.index') }}">Backup now</a>
</div>
@endif
