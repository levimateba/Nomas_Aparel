<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Services\Payments\PaymentGatewayService;
use Illuminate\Http\Request;

class PaymentController extends Controller
{
    public function callback(Request $request)
    {
        $reference = (string) $request->input('reference');
        $status = (string) $request->input('status', 'failed');

        $order = Order::query()->where('payment_reference', $reference)->first();
        if (! $order) {
            return response()->json(['ok' => false, 'message' => 'Order not found'], 404);
        }

        $result = app(PaymentGatewayService::class)->handleCallback($order, $status);

        return response()->json(['ok' => true, 'result' => $result]);
    }

    public function return(Request $request)
    {
        $reference = (string) $request->input('reference');
        $order = Order::query()->where('payment_reference', $reference)->first();

        return view('frontend.checkout.payment-return', [
            'order' => $order,
        ]);
    }
}
