<?php

namespace App\Support;

use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class ReportPeriod
{
    /**
     * @return array{0: string, 1: string, 2: string}
     */
    public static function fromRequest(Request $request, string $defaultPreset = 'today'): array
    {
        $preset = (string) $request->query('preset', '');

        if ($preset === '' && ! $request->filled('from') && ! $request->filled('to') && ! $request->filled('from_date') && ! $request->filled('to_date')) {
            $preset = $defaultPreset;
        }

        [$from, $to] = match ($preset) {
            'today' => [today()->toDateString(), today()->toDateString()],
            'yesterday' => [today()->subDay()->toDateString(), today()->subDay()->toDateString()],
            'week' => [today()->startOfWeek()->toDateString(), today()->toDateString()],
            'month' => [today()->startOfMonth()->toDateString(), today()->toDateString()],
            default => [
                $request->query('from', $request->query('from_date', today()->toDateString())),
                $request->query('to', $request->query('to_date', today()->toDateString())),
            ],
        };

        if ($preset === '') {
            $preset = 'custom';
        }

        return [$from, $to, $preset];
    }

    public static function openingAt(string $from): Carbon
    {
        return Carbon::parse($from)->startOfDay();
    }

    public static function closingAt(string $to): Carbon
    {
        return Carbon::parse($to)->endOfDay();
    }
}
