<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\Setting;
use App\Support\AuditTrailSettings;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AuditLogController extends Controller
{
    public function index(Request $request): View
    {
        abort_unless(auth()->user()?->hasPermission('view_audit_logs'), 403);

        $logs = $this->filteredQuery($request)
            ->with('user')
            ->latest()
            ->paginate(30)
            ->withQueryString();

        return view('admin.audit-logs.index', [
            'logs' => $logs,
            'trailEnabled' => AuditTrailSettings::isEnabled(),
            'disabledByName' => AuditTrailSettings::disabledByName(),
            'disabledAt' => AuditTrailSettings::disabledAt(),
            'backupReminder' => $this->backupReminder(),
        ]);
    }

    public function toggle(Request $request): RedirectResponse
    {
        abort_unless(auth()->user()?->isFullAdmin() || auth()->user()?->hasPermission('view_audit_logs'), 403);

        $enabled = ! AuditTrailSettings::isEnabled();
        AuditTrailSettings::setEnabled($enabled);

        return back()->with('success', $enabled ? 'Audit trail turned on.' : 'Audit trail turned off.');
    }

    public function destroy(AuditLog $auditLog): RedirectResponse
    {
        abort_unless(auth()->user()?->hasPermission('view_audit_logs') && auth()->user()?->isFullAdmin(), 403);
        $auditLog->delete();

        return back()->with('success', 'Audit entry deleted.');
    }

    public function bulkDestroy(Request $request): RedirectResponse
    {
        abort_unless(auth()->user()?->isFullAdmin(), 403);
        $data = $request->validate([
            'ids' => ['required', 'array', 'min:1'],
            'ids.*' => ['integer', 'exists:audit_logs,id'],
        ]);
        AuditLog::query()->whereIn('id', $data['ids'])->delete();

        return back()->with('success', count($data['ids']).' audit entries deleted.');
    }

    public function clear(Request $request): RedirectResponse
    {
        abort_unless(auth()->user()?->isFullAdmin(), 403);
        AuditLog::query()->delete();

        return back()->with('success', 'Audit logs cleared.');
    }

    public function export(Request $request): StreamedResponse|Response
    {
        abort_unless(auth()->user()?->hasPermission('view_audit_logs'), 403);
        $type = strtolower((string) $request->query('type', 'pdf'));
        $logs = $this->filteredQuery($request)->with('user')->latest()->limit(1000)->get();

        if ($type === 'excel' || $type === 'csv') {
            $filename = 'audit-logs-'.now()->format('Ymd_His').'.csv';

            return response()->streamDownload(function () use ($logs) {
                $out = fopen('php://output', 'w');
                fprintf($out, chr(0xEF).chr(0xBB).chr(0xBF));
                fputcsv($out, ['No.', 'Date', 'User', 'Action', 'Target', 'Description', 'Module']);
                foreach ($logs->values() as $i => $log) {
                    fputcsv($out, [
                        $i + 1,
                        $log->created_at?->format('d M Y H:i:s'),
                        $log->user?->name ?: 'System',
                        $log->action,
                        $log->target_label ?: '—',
                        $log->description,
                        $log->module,
                    ]);
                }
                fclose($out);
            }, $filename, ['Content-Type' => 'text/csv; charset=UTF-8']);
        }

        $pdf = Pdf::loadView('admin.audit-logs.pdf', [
            'logs' => $logs,
            'settings' => Setting::get_settings(),
            'generatedAt' => now(),
        ])->setPaper('a4', 'landscape');

        return $pdf->download('audit-logs-'.now()->format('Ymd_His').'.pdf');
    }

    private function filteredQuery(Request $request)
    {
        return AuditLog::query()
            ->when($request->filled('module'), fn ($q) => $q->where('module', $request->string('module')))
            ->when($request->filled('q'), function ($q) use ($request) {
                $term = '%'.trim((string) $request->input('q')).'%';
                $q->where(function ($inner) use ($term) {
                    $inner->where('action', 'like', $term)
                        ->orWhere('description', 'like', $term)
                        ->orWhere('target_label', 'like', $term)
                        ->orWhereHas('user', fn ($u) => $u->where('name', 'like', $term));
                });
            });
    }

    private function backupReminder(): ?array
    {
        if (! auth()->user()?->hasPermission('backup_database')) {
            return null;
        }

        try {
            \Illuminate\Support\Facades\Storage::makeDirectory('backups');
            $files = collect(\Illuminate\Support\Facades\Storage::files('backups'));
            if ($files->isEmpty()) {
                return [
                    'message' => 'No backup found yet. Create one before closing for the day.',
                    'stale' => true,
                ];
            }
            $latest = $files->sortByDesc(fn ($f) => \Illuminate\Support\Facades\Storage::lastModified($f))->first();
            $age = now()->timestamp - \Illuminate\Support\Facades\Storage::lastModified($latest);
            if ($age > 86400) {
                $days = max(1, (int) floor($age / 86400));

                return [
                    'message' => 'Your last backup was '.$days.' day'.($days === 1 ? '' : 's').' ago. Create one before closing for the day.',
                    'stale' => true,
                ];
            }
        } catch (\Throwable) {
            return null;
        }

        return null;
    }
}
