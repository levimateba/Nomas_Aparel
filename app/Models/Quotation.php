<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

// Quote is in the same namespace; no import needed

class Quotation extends Model
{
    protected $fillable = [
        'quotation_number',
        'client_name',
        'client_email',
        'client_phone',
        'client_address',
        'project_title',
        'description',
        'scope_of_work',
        'deliverables',
        'timeline',
        'subtotal',
        'tax',
        'total_amount',
        'status',
        'created_by',
        'quote_id',
    ];

    protected $casts = [
        'subtotal' => 'decimal:2',
        'tax' => 'decimal:2',
        'total_amount' => 'decimal:2',
    ];

    protected static function booted(): void
    {
        static::creating(function (Quotation $quotation): void {
            if (! $quotation->quotation_number) {
                $quotation->quotation_number = self::generateQuotationNumber();
            }
        });
    }

    public static function generateQuotationNumber(): string
    {
        $year = now()->format('Y');
        $prefix = 'ICT-' . $year . '-';

        $latest = self::where('quotation_number', 'like', $prefix . '%')
            ->orderByDesc('quotation_number')
            ->value('quotation_number');

        $next = 1;

        if ($latest) {
            $parts = explode('-', $latest);
            $lastSeq = (int) end($parts);
            $next = $lastSeq + 1;
        }

        return $prefix . str_pad((string) $next, 4, '0', STR_PAD_LEFT);
    }

    public function items()
    {
        return $this->hasMany(QuotationItem::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function quoteRequest()
    {
        return $this->belongsTo(Quote::class, 'quote_id');
    }

    public function getStatusBadgeColorAttribute(): string
    {
        return match ($this->status) {
            'draft' => '#607d8b',
            'approved' => '#2e7d32',
            'sent' => '#1565c0',
            default => '#607d8b',
        };
    }
}
