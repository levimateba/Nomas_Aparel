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
        'return_requested_at',
        'return_reason',
        'status',
        'payment_method',
        'payment_status',
        'payment_reference',
        'total_amount',
        'source',
        'discount_amount',
        'tax_amount',
        'coupon_code',
        'stock_location_id',
        'shop_customer_id',
        'loyalty_points_earned',
        'loyalty_points_redeemed',
        'loyalty_discount_amount',
    ];

    protected $casts = [
        'total_amount' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'loyalty_discount_amount' => 'decimal:2',
        'loyalty_points_earned' => 'integer',
        'loyalty_points_redeemed' => 'integer',
        'cancellation_requested_at' => 'datetime',
        'return_requested_at' => 'datetime',
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

    public function stockAllocations(): HasMany
    {
        return $this->hasMany(OrderStockAllocation::class);
    }

    public function stockLocation(): BelongsTo
    {
        return $this->belongsTo(StockLocation::class, 'stock_location_id');
    }

    public function shopCustomer(): BelongsTo
    {
        return $this->belongsTo(ShopCustomer::class, 'shop_customer_id');
    }

    public function loyaltyTransactions(): HasMany
    {
        return $this->hasMany(LoyaltyTransaction::class);
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
