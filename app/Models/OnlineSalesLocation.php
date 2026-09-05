<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OnlineSalesLocation extends Model
{
    protected $fillable = [
        'stock_location_id',
        'priority',
    ];

    protected $casts = [
        'priority' => 'integer',
    ];

    public function location(): BelongsTo
    {
        return $this->belongsTo(StockLocation::class, 'stock_location_id');
    }
}
