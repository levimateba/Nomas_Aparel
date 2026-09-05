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

        // Paid / shipped / delivered orders cannot be cancelled — customer must request a return.
        if (! in_array($order->status, ['pending', 'processing'], true)) {
            return back()->withErrors([
                'status' => 'Paid orders cannot be cancelled. Please request a return instead.',
            ]);
        }

        if ($order->cancellation_requested_at) {
            return back()->withErrors(['status' => 'A cancellation request was already submitted for this order.']);
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

    public function requestReturn(Request $request, Order $order): RedirectResponse
    {
        abort_unless((int) $order->user_id === (int) auth()->id(), 403);

        if (! in_array($order->status, ['paid', 'shipped', 'delivered'], true)) {
            return back()->withErrors([
                'status' => 'A return can only be requested for paid, shipped, or delivered orders.',
            ]);
        }

        if ($order->return_requested_at) {
            return back()->withErrors(['status' => 'A return request was already submitted for this order.']);
        }

        $data = $request->validate([
            'return_reason' => 'required|string|max:1000',
        ]);

        $order->update([
            'status' => 'return_requested',
            'return_requested_at' => now(),
            'return_reason' => $data['return_reason'],
        ]);

        try {
            $adminEmail = env('MAIL_FROM_ADDRESS');
            if ($adminEmail) {
                Mail::html(
                    'Return requested for <strong>' . e($order->order_number) . '</strong> by ' . e($order->customer_name) . '. Reason: ' . e($data['return_reason']),
                    fn ($message) => $message->to($adminEmail)->subject('Return Request ' . $order->order_number)
                );
            }
        } catch (\Throwable $exception) {
        }

        return back()->with('success', 'Return request submitted. Our team will contact you shortly.');
    }
}
