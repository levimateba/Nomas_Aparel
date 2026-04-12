<?php

namespace App\Services\Payments;

use App\Models\Order;

class PaymentGatewayService
{
    public function initialize(Order $order): array
    {
        $method = $order->payment_method;
        $reference = strtoupper($method) . '-' . $order->order_number;

        if ($method === 'cash_on_delivery') {
            return [
                'status' => 'pending',
                'reference' => $reference,
                'message' => 'Cash on delivery selected. Payment due on delivery.',
            ];
        }

        if ($method === 'mobile_money') {
            return [
                'status' => 'pending',
                'reference' => $reference,
                'message' => 'Mobile money prompt will be sent shortly (gateway integration pending).',
            ];
        }

        if ($method === 'bank_transfer') {
            return [
                'status' => 'pending',
                'reference' => $reference,
                'message' => 'Bank transfer instructions will be sent by email.',
            ];
        }

        return [
            'status' => 'pending',
            'reference' => $reference,
            'message' => 'Card payment gateway integration is pending configuration.',
        ];
    }

    public function handleCallback(Order $order, string $status): array
    {
        $normalized = strtolower(trim($status));
        $paymentStatus = in_array($normalized, ['success', 'successful', 'paid', 'completed'], true)
            ? 'paid'
            : (in_array($normalized, ['pending', 'processing'], true) ? 'pending' : 'failed');

        $order->update([
            'payment_status' => $paymentStatus,
            'status' => $paymentStatus === 'paid' ? 'paid' : $order->status,
        ]);

        return [
            'order_number' => $order->order_number,
            'payment_status' => $paymentStatus,
        ];
    }
}
