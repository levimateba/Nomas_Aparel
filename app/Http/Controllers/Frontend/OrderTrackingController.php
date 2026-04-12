<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\Request;

class OrderTrackingController extends Controller
{
    public function index()
    {
        return view('frontend.orders.track');
    }

    public function search(Request $request)
    {
        $data = $request->validate([
            'order_number' => 'required|string|max:255',
            'customer_email' => 'required|email|max:255',
        ]);

        $order = Order::query()
            ->with('items')
            ->where('order_number', trim($data['order_number']))
            ->where('customer_email', trim($data['customer_email']))
            ->first();

        if (! $order) {
            return back()->withErrors(['order_number' => 'No matching order found.'])->withInput();
        }

        return view('frontend.orders.track', compact('order'));
    }
}
