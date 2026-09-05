<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\StockLocation;
use App\Models\StockMovement;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;

class StockMovementController extends Controller
{
    public function index(Request $request)
    {
        $query = StockMovement::query()->latest();

        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date('date_from'));
        }
        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date('date_to'));
        }
        if ($request->filled('product_id')) {
            $query->where('product_id', $request->integer('product_id'));
        }
        if ($request->filled('type')) {
            $query->where('type', strtoupper($request->string('type')->toString()));
        }
        if ($request->filled('from_location_id')) {
            $query->where('from_location_id', $request->integer('from_location_id'));
        }
        if ($request->filled('to_location_id')) {
            $query->where('to_location_id', $request->integer('to_location_id'));
        }
        if ($request->filled('user_id')) {
            $query->where('user_id', $request->integer('user_id'));
        }
        if ($request->filled('direction')) {
            $direction = strtolower($request->string('direction')->toString());
            if ($direction === 'in') {
                $query->where('quantity', '>', 0);
            } elseif ($direction === 'out') {
                $query->where('quantity', '<', 0);
            }
        }
        if ($request->filled('category_id') && Schema::hasColumn('products', 'category_id')) {
            $categoryId = $request->integer('category_id');
            $query->whereHas('product', fn ($p) => $p->where('category_id', $categoryId));
        }
        if ($request->filled('q')) {
            $term = '%'.trim($request->string('q')->toString()).'%';
            $query->where(function ($q) use ($term) {
                $q->where('reason', 'like', $term)
                    ->orWhere('notes', 'like', $term)
                    ->orWhereHas('product', fn ($p) => $p->where('name', 'like', $term)->orWhere('sku', 'like', $term)->orWhere('barcode', 'like', $term));
            });
        }

        $perPage = (int) $request->input('per_page', 10);
        if (! in_array($perPage, [10, 20, 50, 100], true)) {
            $perPage = 10;
        }

        $movements = $query->with(['product.images', 'variant', 'fromLocation', 'toLocation', 'location', 'user'])
            ->paginate($perPage)
            ->withQueryString();
        $locations = StockLocation::orderedActive();
        $users = User::query()->orderBy('name')->limit(200)->get(['id', 'name']);
        $categories = Schema::hasTable('categories')
            ? \App\Models\Category::query()->orderBy('name')->get(['id', 'name'])
            : collect();

        $total = (int) StockMovement::query()->count();
        $increases = (int) StockMovement::query()->where('quantity', '>', 0)->count();
        $decreases = (int) StockMovement::query()->where('quantity', '<', 0)->count();

        $stockValue = (float) Product::query()
            ->where('is_active', true)
            ->selectRaw('COALESCE(SUM(stock * COALESCE(NULLIF(buying_price, 0), price, 0)), 0) as value')
            ->value('value');

        $prevMonth = StockMovement::query()
            ->whereBetween('created_at', [now()->subMonth()->startOfMonth(), now()->subMonth()->endOfMonth()])
            ->count();
        $thisMonth = StockMovement::query()
            ->whereBetween('created_at', [now()->startOfMonth(), now()->endOfMonth()])
            ->count();
        $prevInc = StockMovement::query()
            ->where('quantity', '>', 0)
            ->whereBetween('created_at', [now()->subMonth()->startOfMonth(), now()->subMonth()->endOfMonth()])
            ->count();
        $thisInc = StockMovement::query()
            ->where('quantity', '>', 0)
            ->whereBetween('created_at', [now()->startOfMonth(), now()->endOfMonth()])
            ->count();
        $prevDec = StockMovement::query()
            ->where('quantity', '<', 0)
            ->whereBetween('created_at', [now()->subMonth()->startOfMonth(), now()->subMonth()->endOfMonth()])
            ->count();
        $thisDec = StockMovement::query()
            ->where('quantity', '<', 0)
            ->whereBetween('created_at', [now()->startOfMonth(), now()->endOfMonth()])
            ->count();

        $stats = [
            'total' => $total,
            'increases' => $increases,
            'decreases' => $decreases,
            'stock_value' => $stockValue,
            'trend_total' => $this->trendPercent($thisMonth, $prevMonth),
            'trend_increases' => $this->trendPercent($thisInc, $prevInc),
            'trend_decreases' => $this->trendPercent($thisDec, $prevDec),
        ];

        return view('admin.inventory.movements.index', compact(
            'movements',
            'locations',
            'perPage',
            'users',
            'categories',
            'stats'
        ));
    }

    private function trendPercent(int|float $current, int|float $previous): ?float
    {
        if ($previous <= 0) {
            return $current > 0 ? 100.0 : null;
        }

        return round((($current - $previous) / $previous) * 100, 1);
    }
}
