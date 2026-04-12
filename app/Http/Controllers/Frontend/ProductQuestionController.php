<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\ProductQuestion;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

class ProductQuestionController extends Controller
{
    public function store(Request $request, Product $product): RedirectResponse
    {
        $data = $request->validate([
            'question' => 'required|string|max:2000',
        ]);

        ProductQuestion::create([
            'product_id' => $product->id,
            'user_id' => auth()->id(),
            'name' => auth()->user()->name ?? 'Customer',
            'email' => auth()->user()->email ?? 'customer@example.com',
            'question' => $data['question'],
            'approved' => false,
        ]);

        try {
            $adminEmail = env('MAIL_FROM_ADDRESS');
            if ($adminEmail) {
                Mail::html(
                    'New product question submitted for <strong>' . e($product->name) . '</strong> and awaiting response.',
                    fn ($message) => $message->to($adminEmail)->subject('New Product Question')
                );
            }
        } catch (\Throwable $exception) {
        }

        return back()->with('success', 'Question submitted and pending moderation.');
    }
}
