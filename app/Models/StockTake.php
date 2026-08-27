<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class StockTake extends Model
{
    public const STATUS_DRAFT = 'draft';
    public const STATUS_COUNTING = 'counting';
    public const STATUS_REVIEW = 'review';
    public const STATUS_APPROVED = 'approved';
    public const STATUS_CANCELLED = 'cancelled';

    protected $fillable = [
        'reference',
        'user_id',
        'completed_by',
        'type',
        'status',
        'category_id',
        'vendor_id',
        'filter_stock_status',
        'notes',
        'stocktake_date',
        'started_at',
        'completed_at',
        'positive_variance_value',
        'negative_variance_value',
    ];

    protected $casts = [
        'stocktake_date' => 'date',
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
        'positive_variance_value' => 'decimal:2',
        'negative_variance_value' => 'decimal:2',
    ];

    public function items(): HasMany
    {
        return $this->hasMany(StockTakeItem::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function completedByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'completed_by');
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function vendor(): BelongsTo
    {
        return $this->belongsTo(Vendor::class);
    }

    public function isEditable(): bool
    {
        return in_array($this->status, [self::STATUS_DRAFT, self::STATUS_COUNTING, self::STATUS_REVIEW], true);
    }

    public function isOpen(): bool
    {
        return $this->isEditable();
    }

    public function canApprove(): bool
    {
        return $this->status === self::STATUS_REVIEW;
    }
}
