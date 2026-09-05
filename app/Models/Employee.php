<?php

namespace App\Models;

use App\Support\PublicStorageUrl;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class Employee extends Model
{
    public const EMPLOYMENT_TYPES = [
        'full_time' => 'Full time',
        'part_time' => 'Part time',
        'contract' => 'Contract',
        'casual' => 'Casual',
        'intern' => 'Intern',
    ];

    public const GENDERS = [
        'male' => 'Male',
        'female' => 'Female',
        'other' => 'Other',
        'prefer_not_to_say' => 'Prefer not to say',
    ];

    protected $fillable = [
        'employee_number',
        'first_name',
        'last_name',
        'other_names',
        'photo',
        'gender',
        'date_of_birth',
        'national_id',
        'kra_pin',
        'nhif_number',
        'nssf_number',
        'phone',
        'alt_phone',
        'email',
        'address',
        'city',
        'county',
        'postal_code',
        'department',
        'job_title',
        'employment_type',
        'hire_date',
        'termination_date',
        'basic_salary',
        'bank_name',
        'bank_account',
        'bank_branch',
        'emergency_contact_name',
        'emergency_contact_phone',
        'emergency_contact_relation',
        'notes',
        'is_active',
        'user_id',
    ];

    protected function casts(): array
    {
        return [
            'date_of_birth' => 'date',
            'hire_date' => 'date',
            'termination_date' => 'date',
            'basic_salary' => 'decimal:2',
            'is_active' => 'boolean',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function fullName(): string
    {
        return trim(collect([$this->first_name, $this->other_names, $this->last_name])->filter()->implode(' '));
    }

    public function photoStoragePath(): ?string
    {
        $raw = $this->getRawOriginal('photo');
        if (! $raw) {
            return null;
        }

        if (str_starts_with($raw, '/storage/')) {
            return ltrim(substr($raw, strlen('/storage/')), '/');
        }

        if (str_starts_with($raw, 'storage/')) {
            return ltrim(substr($raw, strlen('storage/')), '/');
        }

        return ltrim($raw, '/');
    }

    public function photoUrl(): ?string
    {
        return PublicStorageUrl::fromPath($this->getRawOriginal('photo'));
    }

    public function hasPhotoFile(): bool
    {
        $path = $this->photoStoragePath();
        if (! $path) {
            return false;
        }

        try {
            return Storage::disk('public')->exists($path);
        } catch (\Throwable) {
            return false;
        }
    }

    public function employmentTypeLabel(): string
    {
        return self::EMPLOYMENT_TYPES[$this->employment_type] ?? ($this->employment_type ?: '—');
    }

    public function isSystemUser(): bool
    {
        return (bool) $this->user_id;
    }

    public static function nextEmployeeNumber(): string
    {
        $last = static::query()->orderByDesc('id')->value('employee_number');
        $seq = 1;
        if ($last && preg_match('/(\d+)$/', (string) $last, $m)) {
            $seq = ((int) $m[1]) + 1;
        }

        return 'EMP-'.str_pad((string) $seq, 4, '0', STR_PAD_LEFT);
    }
}
