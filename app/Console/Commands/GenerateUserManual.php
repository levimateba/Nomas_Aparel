<?php

namespace App\Console\Commands;

use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class GenerateUserManual extends Command
{
    protected $signature = 'manual:generate
                            {--output=docs/Nomas-Apparel-User-Manual.pdf : Relative path from project root}';

    protected $description = 'Generate the Nomas Apparel multi-role system user manual PDF';

    public function handle(): int
    {
        $relative = ltrim((string) $this->option('output'), '/');
        $path = base_path($relative);
        File::ensureDirectoryExists(dirname($path));

        $pdf = Pdf::loadView('manuals.system-user-manual')
            ->setPaper('a4')
            ->setOption('isRemoteEnabled', true)
            ->setOption('defaultFont', 'DejaVu Sans');

        $pdf->save($path);

        $publicCopy = public_path('docs/Nomas-Apparel-User-Manual.pdf');
        File::ensureDirectoryExists(dirname($publicCopy));
        File::copy($path, $publicCopy);

        $this->info('User manual generated:');
        $this->line(' - '.$path);
        $this->line(' - '.$publicCopy);
        $this->line('Download: '.url('/docs/Nomas-Apparel-User-Manual.pdf'));

        return self::SUCCESS;
    }
}
