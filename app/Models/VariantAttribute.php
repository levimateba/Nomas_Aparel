<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class VariantAttribute extends Model
{
    protected $fillable = ['name', 'slug', 'type', 'sort_order', 'is_active'];

    protected $casts = ['is_active' => 'boolean'];

    public function values(): HasMany
    {
        return $this->hasMany(VariantAttributeValue::class)->orderBy('sort_order')->orderBy('value');
    }
}
