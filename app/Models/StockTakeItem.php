<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StockTakeItem extends Model
{
    protected $fillable = [
        'stock_take_id',
        'product_id',
        'product_name',
        'sku',
        'barcode',
        'system_qty',
        'counted_qty',
        'unit_cost',
        'variance',
        'variance_value',
        'reason',
        'notes',
    ];

    protected $casts = [
        'unit_cost' => 'decimal:2',
        'variance_value' => 'decimal:2',
    ];

    public function stockTake(): BelongsTo
    {
        return $this->belongsTo(StockTake::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function isCounted(): bool
    {
        return $this->counted_qty !== null;
    }

    public function varianceQty(): ?int
    {
        if (! $this->isCounted()) {
            return null;
        }

        return (int) $this->counted_qty - (int) $this->system_qty;
    }

    public function refreshVariance(): void
    {
        $physical = $this->counted_qty;
        $variance = $physical === null ? 0 : ((int) $physical - (int) $this->system_qty);
        $this->forceFill([
            'variance' => $variance,
            'variance_value' => round($variance * (float) $this->unit_cost, 2),
        ])->save();
    }
}
