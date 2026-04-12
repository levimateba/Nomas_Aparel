<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Quote extends Model
{
    protected $fillable = [
        'name',
        'email',
        'phone',
        'company',
        'service_type',
        'budget_range',
        'timeline',
        'project_details',
        'status',
        'admin_notes',
    ];

    protected $casts = [
        'timeline' => 'date',
    ];

    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            'new'       => 'New',
            'reviewing' => 'Reviewing',
            'quoted'    => 'Quoted',
            'accepted'  => 'Accepted',
            'declined'  => 'Declined',
            default     => ucfirst($this->status),
        };
    }

    public function getStatusColorAttribute(): string
    {
        return match ($this->status) {
            'new'       => '#2196f3',
            'reviewing' => '#ff9800',
            'quoted'    => '#9c27b0',
            'accepted'  => '#4caf50',
            'declined'  => '#f44336',
            default     => '#607d8b',
        };
    }

    public function quotation()
    {
        return $this->hasOne(Quotation::class, 'quote_id');
    }
}
