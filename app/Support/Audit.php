<?php

namespace App\Support;

use App\Models\AuditLog;
use Illuminate\Database\Eloquent\Model;

class Audit
{
    public static function log(
        string $action,
        ?string $description = null,
        ?Model $subject = null,
        array $metadata = [],
        ?string $module = null,
        ?string $targetLabel = null
    ): void {
        if (! \Illuminate\Support\Facades\Schema::hasTable('audit_logs')) {
            return;
        }

        if (! AuditTrailSettings::isEnabled()) {
            return;
        }

        $payload = [
            'user_id' => auth()->id(),
            'action' => $action,
            'module' => $module,
            'description' => $description,
            'subject_type' => $subject ? $subject::class : null,
            'subject_id' => $subject?->getKey(),
            'metadata' => $metadata ?: null,
            'ip_address' => request()?->ip(),
        ];

        if (\Illuminate\Support\Facades\Schema::hasColumn('audit_logs', 'target_label')) {
            $payload['target_label'] = $targetLabel ?: self::defaultTargetLabel($subject);
        }

        AuditLog::query()->create($payload);
    }

    private static function defaultTargetLabel(?Model $subject): ?string
    {
        if (! $subject) {
            return null;
        }

        $base = class_basename($subject);
        $label = $subject->name
            ?? $subject->order_number
            ?? $subject->purchase_number
            ?? $subject->po_number
            ?? $subject->getKey();

        return $base.': '.$label;
    }
}
