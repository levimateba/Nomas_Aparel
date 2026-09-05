<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class VariantAttributeValue extends Model
{
    protected $fillable = [
        'variant_attribute_id', 'value', 'code', 'hex_color', 'sort_order', 'is_active',
    ];

    protected $casts = ['is_active' => 'boolean'];

    public function attribute(): BelongsTo
    {
        return $this->belongsTo(VariantAttribute::class, 'variant_attribute_id');
    }
}
