<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Order extends Model
{
    protected $fillable = [
        'order_number',
        'user_id',
        'cashier_shift_id',
        'customer_name',
        'customer_email',
        'customer_phone',
        'shipping_address',
        'notes',
        'cancellation_requested_at',
        'cancellation_reason',
        'status',
        'payment_method',
        'payment_status',
        'payment_reference',
        'total_amount',
        'source',
        'discount_amount',
        'coupon_code',
    ];

    protected $casts = [
        'total_amount' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'cancellation_requested_at' => 'datetime',
    ];

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function cashierShift(): BelongsTo
    {
        return $this->belongsTo(CashierShift::class);
    }

    public function returns(): HasMany
    {
        return $this->hasMany(OrderReturn::class);
    }

    public function isPos(): bool
    {
        return ($this->source ?? null) === 'pos'
            || str_starts_with((string) $this->order_number, 'POS-');
    }

    public function isReturnable(): bool
    {
        return $this->isPos() && ! in_array($this->status, ['cancelled', 'pending'], true);
    }
}
