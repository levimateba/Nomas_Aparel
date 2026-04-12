<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Mail;

class AccountOrderController extends Controller
{
    public function index()
    {
        $orders = Order::query()
            ->withCount('items')
            ->where('user_id', auth()->id())
            ->latest()
            ->paginate(15);

        return view('frontend.orders.my-orders', compact('orders'));
    }

    public function show(Order $order)
    {
        abort_unless((int) $order->user_id === (int) auth()->id(), 403);
        $order->load('items');

        return view('frontend.orders.my-order-show', compact('order'));
    }

    public function requestCancel(Request $request, Order $order): RedirectResponse
    {
        abort_unless((int) $order->user_id === (int) auth()->id(), 403);

        if (! in_array($order->status, ['pending', 'processing', 'paid'], true)) {
            return back()->withErrors(['status' => 'This order can no longer be cancelled from your account.']);
        }

        $data = $request->validate([
            'cancellation_reason' => 'required|string|max:1000',
        ]);

        $order->update([
            'status' => 'cancellation_requested',
            'cancellation_requested_at' => now(),
            'cancellation_reason' => $data['cancellation_reason'],
        ]);

        try {
            $adminEmail = env('MAIL_FROM_ADDRESS');
            if ($adminEmail) {
                Mail::html(
                    'Cancellation requested for <strong>' . e($order->order_number) . '</strong> by ' . e($order->customer_name) . '. Reason: ' . e($data['cancellation_reason']),
                    fn ($message) => $message->to($adminEmail)->subject('Cancellation Request ' . $order->order_number)
                );
            }
        } catch (\Throwable $exception) {
        }

        return back()->with('success', 'Cancellation request submitted.');
    }
}
