<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Schema;

class Product extends Model
{
    protected $fillable = [
        'category_id',
        'vendor_id',
        'name',
        'slug',
        'sku',
        'barcode',
        'description',
        'price',
        'sale_price',
        'stock',
        'image_url',
        'is_active',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'sale_price' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function vendor(): BelongsTo
    {
        return $this->belongsTo(Vendor::class);
    }

    public function reviews(): HasMany
    {
        return $this->hasMany(ProductReview::class);
    }

    public function questions(): HasMany
    {
        return $this->hasMany(ProductQuestion::class);
    }

    public static function findActiveByScan(string $code): ?self
    {
        $code = trim($code);
        if ($code === '') {
            return null;
        }

        $query = static::query()->where('is_active', true);

        if (Schema::hasColumn('products', 'barcode')) {
            $match = (clone $query)->where('barcode', $code)->first();
            if ($match) {
                return $match;
            }
        }

        return $query->where('sku', $code)->first();
    }

    public function currentPrice(): float
    {
        $sale = (float) ($this->sale_price ?? 0);
        $price = (float) $this->price;

        return ($sale > 0 && $sale < $price) ? $sale : $price;
    }

    public function hasSale(): bool
    {
        $sale = (float) ($this->sale_price ?? 0);
        $price = (float) $this->price;

        return $sale > 0 && $sale < $price;
    }

    public function discountPercent(): int
    {
        if (! $this->hasSale() || (float) $this->price <= 0) {
            return 0;
        }

        return (int) round((1 - ((float) $this->sale_price / (float) $this->price)) * 100);
    }
}
