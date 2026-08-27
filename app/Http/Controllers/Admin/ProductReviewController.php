<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ProductReview;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

class ProductReviewController extends Controller
{
    public function index(Request $request)
    {
        $query = ProductReview::query()->with('product')->latest();
        if ($request->filled('status')) {
            $query->where('approved', $request->string('status')->toString() === 'approved');
        }

        $reviews = $query->paginate(20)->withQueryString();
        $stats = [
            'total' => ProductReview::count(),
            'pending' => ProductReview::where('approved', false)->count(),
            'approved' => ProductReview::where('approved', true)->count(),
        ];

        return view('admin.reviews.index', compact('reviews', 'stats'));
    }

    public function update(ProductReview $review): RedirectResponse
    {
        $wasApproved = $review->approved;
        $review->update(['approved' => ! $review->approved]);

        if (!$wasApproved && $review->approved) {
            try {
                Mail::html(
                    'Your review for <strong>' . e($review->product?->name ?? 'our product') . '</strong> has been approved. Thank you!',
                    fn ($message) => $message->to($review->email)->subject('Your Review Was Approved')
                );
            } catch (\Throwable $exception) {
            }
        }

        return back()->with('success', 'Review status updated.');
    }

    public function destroy(ProductReview $review): RedirectResponse
    {
        $review->delete();

        return back()->with('success', 'Review deleted.');
    }
}
