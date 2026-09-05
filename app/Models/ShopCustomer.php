<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class ShopCustomer extends Model
{
    protected $fillable = [
        'name',
        'phone',
        'email',
        'address',
        'notes',
        'balance',
        'is_active',
    ];

    protected $casts = [
        'balance' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class, 'shop_customer_id');
    }

    public function loyaltyCard(): HasOne
    {
        return $this->hasOne(LoyaltyCard::class, 'shop_customer_id')
            ->where('status', LoyaltyCard::STATUS_ACTIVE);
    }

    public function loyaltyCards(): HasMany
    {
        return $this->hasMany(LoyaltyCard::class, 'shop_customer_id');
    }

    public function loyaltyTransactions(): HasMany
    {
        return $this->hasMany(LoyaltyTransaction::class, 'shop_customer_id');
    }
}
