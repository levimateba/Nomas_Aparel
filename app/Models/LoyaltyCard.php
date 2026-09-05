<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class LoyaltyCard extends Model
{
    public const STATUS_ACTIVE = 'active';
    public const STATUS_BLOCKED = 'blocked';
    public const STATUS_EXPIRED = 'expired';

    protected $fillable = [
        'shop_customer_id',
        'card_number',
        'points_balance',
        'status',
        'issued_at',
    ];

    protected $casts = [
        'points_balance' => 'integer',
        'issued_at' => 'datetime',
    ];

    public function customer(): BelongsTo
    {
        return $this->belongsTo(ShopCustomer::class, 'shop_customer_id');
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(LoyaltyTransaction::class);
    }

    public function isActive(): bool
    {
        return $this->status === self::STATUS_ACTIVE;
    }
}
