<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InventoryStock extends Model
{
    protected $fillable = [
        'product_id',
        'product_variant_id',
        'stock_location_id',
        'quantity',
        'reorder_level',
        'reorder_quantity',
    ];

    protected $casts = [
        'product_variant_id' => 'integer',
        'quantity' => 'integer',
        'reorder_level' => 'integer',
        'reorder_quantity' => 'integer',
    ];

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function variant(): BelongsTo
    {
        return $this->belongsTo(ProductVariant::class, 'product_variant_id');
    }

    public function location(): BelongsTo
    {
        return $this->belongsTo(StockLocation::class, 'stock_location_id');
    }

    public function variantKey(): int
    {
        return (int) ($this->product_variant_id ?? 0);
    }

    public function isLow(): bool
    {
        $level = (int) $this->reorder_level;

        return $level > 0 && (int) $this->quantity > 0 && (int) $this->quantity <= $level;
    }

    public function isOut(): bool
    {
        return (int) $this->quantity <= 0;
    }
}
