<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class StockTransfer extends Model
{
    public const STATUSES = ['draft', 'pending', 'approved', 'completed', 'cancelled'];

    public const REASONS = [
        'Shop Replenishment',
        'Display',
        'Return to Store',
        'Stock Reallocation',
        'Other',
    ];

    protected $fillable = [
        'transfer_number',
        'from_location_id',
        'to_location_id',
        'user_id',
        'approved_by',
        'completed_by',
        'status',
        'reason',
        'notes',
        'approved_at',
        'completed_at',
    ];

    protected $casts = [
        'approved_at' => 'datetime',
        'completed_at' => 'datetime',
    ];

    public function items(): HasMany
    {
        return $this->hasMany(StockTransferItem::class);
    }

    public function fromLocation(): BelongsTo
    {
        return $this->belongsTo(StockLocation::class, 'from_location_id');
    }

    public function toLocation(): BelongsTo
    {
        return $this->belongsTo(StockLocation::class, 'to_location_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function approvedByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function completedByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'completed_by');
    }

    public function totalQuantity(): int
    {
        return (int) $this->items->sum('quantity');
    }

    public function isEditable(): bool
    {
        return in_array($this->status, ['draft', 'pending'], true);
    }
}
