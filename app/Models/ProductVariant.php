<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ProductVariant extends Model
{
    protected $fillable = [
        'product_id', 'name', 'sku', 'barcode', 'buying_price', 'price', 'wholesale_price',
        'sale_price', 'tax_rate', 'stock', 'reorder_level', 'reorder_quantity', 'shelf_location',
        'weight', 'image_url', 'option_signature', 'is_active', 'sort_order',
    ];

    protected $casts = [
        'buying_price' => 'decimal:2',
        'price' => 'decimal:2',
        'wholesale_price' => 'decimal:2',
        'sale_price' => 'decimal:2',
        'tax_rate' => 'decimal:2',
        'weight' => 'decimal:3',
        'is_active' => 'boolean',
    ];

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function attributeValues(): BelongsToMany
    {
        return $this->belongsToMany(
            VariantAttributeValue::class,
            'product_variant_attribute_values',
            'product_variant_id',
            'variant_attribute_value_id'
        )->withPivot('variant_attribute_id')->withTimestamps();
    }

    public function images(): HasMany
    {
        return $this->hasMany(ProductImage::class);
    }

    public function currentPrice(): float
    {
        $sale = (float) ($this->sale_price ?? 0);
        $price = (float) $this->price;

        return ($sale > 0 && $sale < $price) ? $sale : $price;
    }

    public function displayName(): string
    {
        $productName = $this->relationLoaded('product') ? ($this->product?->name ?? '') : '';
        if ($productName === '') {
            return (string) $this->name;
        }

        return $productName.' — '.$this->name;
    }

    public function availableStock(): int
    {
        return max(0, (int) $this->stock);
    }
}
