<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Schema;

class StockLocation extends Model
{
    public const TYPES = [
        'store' => 'Store',
        'shop' => 'Shop',
        'warehouse' => 'Warehouse',
        'outlet' => 'Outlet',
        'damaged' => 'Damaged',
        'returns' => 'Returns',
        'other' => 'Other',
    ];

    protected $fillable = [
        'name',
        'code',
        'type',
        'description',
        'is_active',
        'sort_order',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'sort_order' => 'integer',
    ];

    public function inventoryStocks(): HasMany
    {
        return $this->hasMany(InventoryStock::class, 'stock_location_id');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public static function orderedActive()
    {
        if (! Schema::hasTable('stock_locations')) {
            return collect();
        }

        return static::query()->active()->orderBy('sort_order')->orderBy('name')->get();
    }

    public static function findByCode(string $code): ?self
    {
        if (! Schema::hasTable('stock_locations')) {
            return null;
        }

        return static::query()->where('code', strtoupper($code))->first();
    }

    public static function mainStore(): ?self
    {
        return static::findByCode('STORE') ?: static::query()->where('type', 'store')->orderBy('sort_order')->first();
    }

    public static function shopFloor(): ?self
    {
        return static::findByCode('SHOP') ?: static::query()->where('type', 'shop')->orderBy('sort_order')->first();
    }

    public function canBeDeleted(): bool
    {
        if ($this->inventoryStocks()->where('quantity', '!=', 0)->exists()) {
            return false;
        }

        if (Schema::hasTable('stock_movements') && StockMovement::query()
            ->where(function ($q) {
                $q->where('from_location_id', $this->id)
                    ->orWhere('to_location_id', $this->id)
                    ->orWhere('stock_location_id', $this->id);
            })->exists()) {
            return false;
        }

        if (Schema::hasTable('stock_transfers') && StockTransfer::query()
            ->where(function ($q) {
                $q->where('from_location_id', $this->id)
                    ->orWhere('to_location_id', $this->id);
            })->exists()) {
            return false;
        }

        return true;
    }
}
