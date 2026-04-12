<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\WishlistItem;
use Illuminate\Http\RedirectResponse;

class WishlistController extends Controller
{
    public function index()
    {
        $items = WishlistItem::query()
            ->with('product')
            ->where('user_id', auth()->id())
            ->latest()
            ->paginate(20);

        return view('frontend.wishlist.index', compact('items'));
    }

    public function store(Product $product): RedirectResponse
    {
        WishlistItem::firstOrCreate([
            'user_id' => auth()->id(),
            'product_id' => $product->id,
        ]);

        return back()->with('success', 'Added to wishlist.');
    }

    public function destroy(Product $product): RedirectResponse
    {
        WishlistItem::query()
            ->where('user_id', auth()->id())
            ->where('product_id', $product->id)
            ->delete();

        return back()->with('success', 'Removed from wishlist.');
    }
}
