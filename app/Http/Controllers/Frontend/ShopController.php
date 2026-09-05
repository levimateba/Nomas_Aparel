<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;

class ShopController extends Controller
{
    public function index(Request $request)
    {
        $query = Product::query()
            ->with(['category', 'vendor'])
            ->when(Schema::hasTable('product_reviews'), function ($builder) {
                $builder->withCount(['reviews' => fn ($reviews) => $reviews->where('approved', true)])
                    ->withAvg(['reviews' => fn ($reviews) => $reviews->where('approved', true)], 'rating');
            })
            ->where('is_active', true);

        if ($request->filled('category_id')) {
            $query->where('category_id', $request->integer('category_id'));
        }

        if ($request->filled('q')) {
            $search = $request->string('q')->toString();
            $query->where(function ($builder) use ($search) {
                $builder->where('name', 'like', '%' . $search . '%')
                    ->orWhere('description', 'like', '%' . $search . '%')
                    ->orWhere('sku', 'like', '%' . $search . '%');
                if (Schema::hasColumn('products', 'barcode')) {
                    $builder->orWhere('barcode', 'like', '%' . $search . '%');
                }
            });
        }

        if ($request->boolean('sale')) {
            $query->whereNotNull('sale_price')
                ->whereColumn('sale_price', '<', 'price');
        }

        $sort = $request->string('sort', 'latest')->toString();
        if ($sort === 'name_asc') {
            $query->orderBy('name');
        } elseif ($sort === 'name_desc') {
            $query->orderByDesc('name');
        } else {
            $query->latest();
        }

        $products = $query->paginate(48)->withQueryString();
        $categories = Category::query()->where('is_active', true)->orderBy('id')->get();

        return view('frontend.shop.index', compact('products', 'categories'));
    }

    public function show(Product $product)
    {
        $product->load(['reviews' => function ($query) {
            $query->where('approved', true)->latest();
        }, 'questions' => function ($query) {
            $query->where('approved', true)->latest();
        }, 'vendor', 'activeVariants.attributeValues', 'images', 'specifications', 'style', 'brand']);
        $averageRating = round((float) $product->reviews->avg('rating'), 1);

        $related = Product::query()
            ->where('is_active', true)
            ->whereKeyNot($product->id)
            ->with(['vendor', 'category'])
            ->when(Schema::hasTable('product_reviews'), function ($query) {
                $query->withCount(['reviews' => fn ($reviews) => $reviews->where('approved', true)])
                    ->withAvg(['reviews' => fn ($reviews) => $reviews->where('approved', true)], 'rating');
            })
            ->when($product->category_id, function ($query) use ($product) {
                $query->where('category_id', $product->category_id);
            })
            ->latest()
            ->limit(8)
            ->get();

        $inventory = app(\App\Services\InventoryStockService::class);
        $onlineStockByVariant = [];
        foreach ($product->activeVariants as $variant) {
            $onlineStockByVariant[$variant->id] = $inventory->onlineAvailableStock($product, $variant);
        }
        $onlineStockSimple = $product->usesVariants()
            ? null
            : $inventory->onlineAvailableStock($product, null);

        return view('frontend.shop.show', [
            'product' => $product,
            'relatedProducts' => $related,
            'averageRating' => $averageRating,
            'onlineStockByVariant' => $onlineStockByVariant,
            'onlineStockSimple' => $onlineStockSimple,
        ]);
    }
}
