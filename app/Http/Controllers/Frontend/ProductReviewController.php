<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\ProductReview;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

class ProductReviewController extends Controller
{
    public function store(Request $request, Product $product): RedirectResponse
    {
        $data = $request->validate([
            'rating' => 'required|integer|min:1|max:5',
            'comment' => 'required|string|max:2000',
        ]);

        ProductReview::create([
            'product_id' => $product->id,
            'user_id' => auth()->id(),
            'name' => auth()->user()->name ?? 'Customer',
            'email' => auth()->user()->email ?? 'customer@example.com',
            'rating' => $data['rating'],
            'comment' => $data['comment'],
            'approved' => false,
        ]);

        try {
            $adminEmail = env('MAIL_FROM_ADDRESS');
            if ($adminEmail) {
                Mail::html(
                    'New product review submitted for <strong>' . e($product->name) . '</strong> and awaiting approval.',
                    fn ($message) => $message->to($adminEmail)->subject('New Product Review')
                );
            }
        } catch (\Throwable $exception) {
        }

        return back()->with('success', 'Review submitted and pending approval.');
    }
}
