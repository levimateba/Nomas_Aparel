<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;

class BackupController extends Controller
{
    public function index()
    {
        Storage::makeDirectory('backups');

        $backups = collect(Storage::files('backups'))
            ->filter(fn ($file) => str_ends_with($file, '.sqlite') || str_ends_with($file, '.sql'))
            ->sortDesc()
            ->values()
            ->map(function (string $file) {
                $full = Storage::path($file);

                return [
                    'path' => $file,
                    'name' => basename($file),
                    'size' => File::exists($full) ? File::size($full) : 0,
                    'modified' => File::exists($full) ? File::lastModified($full) : null,
                ];
            });

        return view('admin.backups.index', [
            'backups' => $backups,
            'driver' => config('database.default'),
            'stats' => [
                'count' => $backups->count(),
                'total_size' => $backups->sum('size'),
            ],
        ]);
    }

    public function store()
    {
        abort_unless(config('database.default') === 'sqlite', 422, 'SQLite backup is available when DB_CONNECTION=sqlite.');

        $source = config('database.connections.sqlite.database');
        abort_unless(File::exists($source), 404, 'SQLite database file was not found.');

        Storage::makeDirectory('backups');
        $filename = 'backups/store_backup_'.now()->format('Y_m_d_H_i_s').'.sqlite';
        File::copy($source, Storage::path($filename));

        return back()->with('success', 'Backup created.');
    }

    public function download(string $file)
    {
        $this->assertBackupName($file);
        $path = 'backups/'.$file;
        abort_unless(Storage::exists($path), 404);

        return Storage::download($path);
    }

    public function destroy(string $file)
    {
        $this->assertBackupName($file);
        Storage::delete('backups/'.$file);

        return back()->with('success', 'Backup deleted.');
    }

    public function restore(Request $request)
    {
        abort_unless(config('database.default') === 'sqlite', 422, 'SQLite restore is available when DB_CONNECTION=sqlite.');

        $data = $request->validate(['backup' => ['required', 'file']]);
        $uploaded = $data['backup'];

        abort_unless(strtolower((string) $uploaded->getClientOriginalExtension()) === 'sqlite', 422, 'Upload a .sqlite backup file.');

        $source = config('database.connections.sqlite.database');
        $automatic = 'backups/store_backup_before_restore_'.now()->format('Y_m_d_H_i_s').'.sqlite';
        Storage::makeDirectory('backups');

        if (File::exists($source)) {
            File::copy($source, Storage::path($automatic));
        }

        File::copy($uploaded->getRealPath(), $source);

        return back()->with('success', 'Backup restored. Restart the local server if the database connection was already open.');
    }

    private function assertBackupName(string $file): void
    {
        abort_unless((bool) preg_match('/^[A-Za-z0-9._-]+\.(sqlite|sql)$/', $file), 404);
    }
}
