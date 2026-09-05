<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Schema;

class Product extends Model
{
    public const TYPE_PRODUCT = 'product';
    public const TYPE_SERVICE = 'service';

    public const AUDIENCES = [
        'men' => 'Men',
        'women' => 'Women',
        'unisex' => 'Unisex',
        'boys' => 'Boys',
        'girls' => 'Girls',
        'kids' => 'Kids',
        'baby' => 'Baby',
    ];

    protected $fillable = [
        'category_id',
        'vendor_id',
        'brand_id',
        'supplier_id',
        'product_style_id',
        'name',
        'slug',
        'sku',
        'barcode',
        'description',
        'price',
        'buying_price',
        'sale_price',
        'wholesale_price',
        'tax_rate',
        'stock',
        'reorder_level',
        'reorder_quantity',
        'unit',
        'purchase_unit',
        'purchase_unit_qty',
        'product_type',
        'has_variants',
        'target_audience',
        'search_keywords',
        'shelf_location',
        'batch_lot',
        'tracks_expiry',
        'expiry_date',
        'has_product_options',
        'product_options',
        'care_instructions',
        'weight',
        'length',
        'width',
        'height',
        'requires_shipping',
        'shipping_class',
        'free_shipping',
        'store_visibility',
        'is_featured',
        'is_new_arrival',
        'is_best_seller',
        'allow_online_purchase',
        'display_stock',
        'allow_backorders',
        'meta_title',
        'meta_description',
        'meta_keywords',
        'seo_noindex',
        'image_url',
        'is_active',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'buying_price' => 'decimal:2',
        'sale_price' => 'decimal:2',
        'wholesale_price' => 'decimal:2',
        'tax_rate' => 'decimal:2',
        'weight' => 'decimal:3',
        'length' => 'decimal:2',
        'width' => 'decimal:2',
        'height' => 'decimal:2',
        'is_active' => 'boolean',
        'tracks_expiry' => 'boolean',
        'has_product_options' => 'boolean',
        'has_variants' => 'boolean',
        'requires_shipping' => 'boolean',
        'free_shipping' => 'boolean',
        'is_featured' => 'boolean',
        'is_new_arrival' => 'boolean',
        'is_best_seller' => 'boolean',
        'allow_online_purchase' => 'boolean',
        'display_stock' => 'boolean',
        'allow_backorders' => 'boolean',
        'seo_noindex' => 'boolean',
        'product_options' => 'array',
        'expiry_date' => 'date',
    ];

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function vendor(): BelongsTo
    {
        return $this->belongsTo(Vendor::class);
    }

    public function brand(): BelongsTo
    {
        return $this->belongsTo(ProductBrand::class, 'brand_id');
    }

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }

    public function style(): BelongsTo
    {
        return $this->belongsTo(ProductStyle::class, 'product_style_id');
    }

    public function additionalBarcodes(): HasMany
    {
        return $this->hasMany(ProductBarcode::class);
    }

    public function variants(): HasMany
    {
        return $this->hasMany(ProductVariant::class)->orderBy('sort_order');
    }

    public function activeVariants(): HasMany
    {
        return $this->variants()->where('is_active', true);
    }

    public function images(): HasMany
    {
        return $this->hasMany(ProductImage::class)->orderBy('sort_order');
    }

    public function specifications(): HasMany
    {
        return $this->hasMany(ProductSpecification::class)->orderBy('sort_order');
    }

    public function reviews(): HasMany
    {
        return $this->hasMany(ProductReview::class);
    }

    public function questions(): HasMany
    {
        return $this->hasMany(ProductQuestion::class);
    }

    public function inventoryStocks(): HasMany
    {
        return $this->hasMany(InventoryStock::class);
    }

    public function stockMovements(): HasMany
    {
        return $this->hasMany(StockMovement::class);
    }

    public function isService(): bool
    {
        return ($this->product_type ?? self::TYPE_PRODUCT) === self::TYPE_SERVICE;
    }

    public function usesVariants(): bool
    {
        return (bool) ($this->has_variants ?? false);
    }

    public function totalAvailableStock(): int
    {
        if ($this->usesVariants() && Schema::hasTable('product_variants')) {
            return (int) $this->activeVariants()->sum('stock');
        }

        return max(0, (int) $this->stock);
    }

    public function stockStatusLabel(): string
    {
        if ($this->isService()) {
            return 'Service';
        }
        $qty = $this->totalAvailableStock();
        $reorder = (int) ($this->reorder_level ?? 0);
        if ($qty <= 0) {
            return 'Out of stock';
        }
        if ($reorder > 0 && $qty <= $reorder) {
            return 'Low stock';
        }
        if ($reorder === 0 && $qty <= 5) {
            return 'Low stock';
        }

        return 'In stock';
    }

    public function syncAdditionalBarcodes(array $codes): void
    {
        if (! Schema::hasTable('product_barcodes')) {
            return;
        }

        $codes = collect($codes)
            ->map(fn ($c) => trim((string) $c))
            ->filter()
            ->unique()
            ->values();

        $this->additionalBarcodes()->delete();
        foreach ($codes as $code) {
            if ($this->barcode && strcasecmp($this->barcode, $code) === 0) {
                continue;
            }
            $this->additionalBarcodes()->create(['barcode' => $code]);
        }
    }

    public function syncSpecifications(array $rows): void
    {
        if (! Schema::hasTable('product_specifications')) {
            return;
        }

        $this->specifications()->delete();
        foreach (array_values($rows) as $i => $row) {
            $name = trim((string) ($row['name'] ?? ''));
            $value = trim((string) ($row['value'] ?? ''));
            if ($name === '' || $value === '') {
                continue;
            }
            $this->specifications()->create([
                'name' => $name,
                'value' => $value,
                'sort_order' => $i,
            ]);
        }
    }

    public function syncGalleryImages(array $urls, ?string $primaryUrl = null): void
    {
        if (! Schema::hasTable('product_images')) {
            return;
        }

        $this->images()->whereNull('product_variant_id')->delete();
        foreach (array_values($urls) as $i => $url) {
            $url = trim((string) $url);
            if ($url === '') {
                continue;
            }
            $this->images()->create([
                'image_url' => $url,
                'is_primary' => $primaryUrl ? $url === $primaryUrl : $i === 0,
                'sort_order' => $i,
            ]);
        }
    }

    /**
     * @return array{product: self, variant: ?ProductVariant}|null
     */
    public static function resolveActiveScan(string $code): ?array
    {
        $code = trim($code);
        if ($code === '') {
            return null;
        }

        if (Schema::hasTable('product_variants')) {
            $variant = ProductVariant::query()
                ->with('product')
                ->where('is_active', true)
                ->where(function ($q) use ($code) {
                    $q->where('barcode', $code)->orWhere('sku', $code);
                })
                ->whereHas('product', fn ($p) => $p->where('is_active', true))
                ->first();
            if ($variant) {
                return ['product' => $variant->product, 'variant' => $variant];
            }
        }

        $product = static::findActiveByScan($code);
        if (! $product) {
            return null;
        }

        return ['product' => $product, 'variant' => null];
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

        if (Schema::hasTable('product_barcodes')) {
            $viaExtra = (clone $query)->whereHas('additionalBarcodes', fn ($q) => $q->where('barcode', $code))->first();
            if ($viaExtra) {
                return $viaExtra;
            }
        }

        if (Schema::hasColumn('products', 'search_keywords')) {
            $viaAlias = (clone $query)->where('search_keywords', 'like', '%'.$code.'%')->first();
            if ($viaAlias) {
                return $viaAlias;
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

    public function primaryImageUrl(): ?string
    {
        if ($this->relationLoaded('images')) {
            $primary = $this->images->firstWhere('is_primary', true) ?: $this->images->first();
            if ($primary) {
                return $primary->image_url;
            }
        }

        return $this->image_url;
    }

    public function displayImageUrl(): string
    {
        $url = $this->primaryImageUrl();
        if (! $url) {
            return asset('images/products/placeholder.svg');
        }

        if (str_starts_with($url, 'http://') || str_starts_with($url, 'https://')) {
            return $url;
        }

        return asset($url);
    }
}
