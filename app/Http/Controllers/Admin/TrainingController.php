<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class TrainingController extends Controller
{
    public function index()
    {
        $path = base_path('docs/CASHIER_TRAINING.md');
        $markdown = File::exists($path)
            ? File::get($path)
            : "# Cashier Training\n\nTraining guide not found.";

        return view('admin.training.index', [
            'html' => $this->toHtml($markdown),
        ]);
    }

    private function toHtml(string $markdown): string
    {
        $escaped = e($markdown);
        $lines = preg_split("/\r\n|\n|\r/", $escaped) ?: [];
        $html = [];
        $inList = false;

        foreach ($lines as $line) {
            if (Str::startsWith($line, '### ')) {
                if ($inList) {
                    $html[] = '</ul>';
                    $inList = false;
                }
                $html[] = '<h3>'.Str::after($line, '### ').'</h3>';
                continue;
            }
            if (Str::startsWith($line, '## ')) {
                if ($inList) {
                    $html[] = '</ul>';
                    $inList = false;
                }
                $html[] = '<h2>'.Str::after($line, '## ').'</h2>';
                continue;
            }
            if (Str::startsWith($line, '# ')) {
                if ($inList) {
                    $html[] = '</ul>';
                    $inList = false;
                }
                $html[] = '<h1>'.Str::after($line, '# ').'</h1>';
                continue;
            }
            if (Str::startsWith($line, '- ') || Str::startsWith($line, '* ')) {
                if (! $inList) {
                    $html[] = '<ul>';
                    $inList = true;
                }
                $item = preg_replace('/^\-\s+|^\*\s+/', '', $line) ?? $line;
                $item = preg_replace('/\*\*(.+?)\*\*/', '<strong>$1</strong>', $item) ?? $item;
                $html[] = '<li>'.$item.'</li>';
                continue;
            }
            if (trim($line) === '' || trim($line) === '---') {
                if ($inList) {
                    $html[] = '</ul>';
                    $inList = false;
                }
                continue;
            }
            if ($inList) {
                $html[] = '</ul>';
                $inList = false;
            }
            $text = preg_replace('/\*\*(.+?)\*\*/', '<strong>$1</strong>', $line) ?? $line;
            $html[] = '<p>'.$text.'</p>';
        }

        if ($inList) {
            $html[] = '</ul>';
        }

        return implode("\n", $html);
    }
}
