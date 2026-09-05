<?php

namespace App\Support;

use App\Models\Setting;

class AuditTrailSettings
{
    public static function isEnabled(): bool
    {
        $settings = Setting::get_settings();

        if (! \Illuminate\Support\Facades\Schema::hasColumn('settings', 'audit_trail_enabled')) {
            return true;
        }

        return (bool) ($settings->audit_trail_enabled ?? true);
    }

    public static function setEnabled(bool $enabled): void
    {
        $settings = Setting::get_settings();
        if (! \Illuminate\Support\Facades\Schema::hasColumn('settings', 'audit_trail_enabled')) {
            return;
        }

        $payload = ['audit_trail_enabled' => $enabled];
        if ($enabled) {
            $payload['audit_trail_disabled_by_name'] = null;
            $payload['audit_trail_disabled_at'] = null;
        } else {
            $payload['audit_trail_disabled_by_name'] = auth()->user()?->name ?? 'Unknown user';
            $payload['audit_trail_disabled_at'] = now();
        }

        $settings->forceFill($payload)->save();
    }

    public static function disabledByName(): ?string
    {
        $name = Setting::get_settings()->audit_trail_disabled_by_name ?? null;

        return filled($name) ? $name : null;
    }

    public static function disabledAt(): ?string
    {
        $at = Setting::get_settings()->audit_trail_disabled_at ?? null;
        if (! $at) {
            return null;
        }

        return $at instanceof \Carbon\CarbonInterface ? $at->toDateTimeString() : (string) $at;
    }
}
