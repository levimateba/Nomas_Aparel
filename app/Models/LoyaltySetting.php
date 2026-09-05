<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Schema;

class LoyaltySetting extends Model
{
    protected $fillable = [
        'enabled',
        'amount_per_point',
        'points_awarded',
        'minimum_purchase',
        'redemption_enabled',
        'redemption_points',
        'redemption_value',
        'allow_earn_on_discounted',
        'allow_redemption_at_pos',
        'show_estimated_points_on_pos',
        'show_balance_after_sale',
        'show_on_receipt',
        'reverse_points_on_refund',
        'points_expiration_enabled',
        'points_expiration_days',
    ];

    protected $casts = [
        'enabled' => 'boolean',
        'amount_per_point' => 'decimal:2',
        'points_awarded' => 'integer',
        'minimum_purchase' => 'decimal:2',
        'redemption_enabled' => 'boolean',
        'redemption_points' => 'integer',
        'redemption_value' => 'decimal:2',
        'allow_earn_on_discounted' => 'boolean',
        'allow_redemption_at_pos' => 'boolean',
        'show_estimated_points_on_pos' => 'boolean',
        'show_balance_after_sale' => 'boolean',
        'show_on_receipt' => 'boolean',
        'reverse_points_on_refund' => 'boolean',
        'points_expiration_enabled' => 'boolean',
        'points_expiration_days' => 'integer',
    ];

    public static function current(): self
    {
        if (! Schema::hasTable('loyalty_settings')) {
            return new self([
                'enabled' => false,
                'amount_per_point' => 100,
                'points_awarded' => 1,
                'minimum_purchase' => 100,
                'redemption_enabled' => true,
                'redemption_points' => 100,
                'redemption_value' => 10,
                'allow_earn_on_discounted' => true,
                'allow_redemption_at_pos' => true,
                'show_estimated_points_on_pos' => true,
                'show_balance_after_sale' => true,
                'show_on_receipt' => true,
                'reverse_points_on_refund' => true,
                'points_expiration_enabled' => false,
                'points_expiration_days' => 365,
            ]);
        }

        $row = static::query()->first();
        if ($row) {
            return $row;
        }

        return static::query()->create([
            'enabled' => false,
            'amount_per_point' => 100,
            'points_awarded' => 1,
            'minimum_purchase' => 100,
            'redemption_enabled' => true,
            'redemption_points' => 100,
            'redemption_value' => 10,
            'allow_earn_on_discounted' => true,
            'allow_redemption_at_pos' => true,
            'show_estimated_points_on_pos' => true,
            'show_balance_after_sale' => true,
            'show_on_receipt' => true,
            'reverse_points_on_refund' => true,
            'points_expiration_enabled' => false,
            'points_expiration_days' => 365,
        ]);
    }
}
