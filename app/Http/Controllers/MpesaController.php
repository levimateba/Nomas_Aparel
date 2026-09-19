<?php

namespace App\Http\Controllers;

use App\Models\MpesaTransaction;
use App\Models\Setting;
use App\Services\CashierShiftService;
use App\Services\Mpesa\DarajaService;
use App\Services\Pos\PosSaleCompleter;
use App\Support\Audit;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use RuntimeException;
use Throwable;

class MpesaController extends Controller
{
    public function __construct(
        private DarajaService $daraja,
        private PosSaleCompleter $saleCompleter
    ) {}

    /**
     * Initiate STK Push for the current POS cart.
     * Does NOT mark the sale paid — wait for callback.
     */
    public function stkPush(Request $request): JsonResponse
    {
        $settings = Setting::get_settings();
        if ($settings->require_open_shift && ! app(CashierShiftService::class)->currentOpen(auth()->id())) {
            return response()->json([
                'success' => false,
                'message' => 'Open a cashier shift before collecting M-PESA payment.',
            ], 422);
        }

        $data = $request->validate([
            'phone' => 'required|string|max:20',
            'customer_name' => 'nullable|string|max:255',
            'customer_email' => 'nullable|email|max:255',
            'notes' => 'nullable|string|max:500',
        ]);

        $cart = session('pos_cart', []);
        if (empty($cart)) {
            return response()->json([
                'success' => false,
                'message' => 'Add at least one product before sending an M-PESA request.',
            ], 422);
        }

        $pos = app(\App\Http\Controllers\Admin\PosController::class);
        $totals = $pos->totals($cart);
        $amount = (float) ($totals['total'] ?? 0);

        if ($amount < 1) {
            return response()->json([
                'success' => false,
                'message' => 'Sale total must be at least KES 1 for M-PESA.',
            ], 422);
        }

        try {
            $phone = $this->daraja->normalizePhone($data['phone']);
        } catch (RuntimeException $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
        }

        $accountReference = 'POS'.Str::upper(Str::random(8));

        $checkoutPayload = [
            'cart' => $cart,
            'totals' => $totals,
            'payment_method' => 'mobile_money',
            'customer_name' => $data['customer_name'] ?? null,
            'customer_phone' => $phone,
            'customer_email' => $data['customer_email'] ?? null,
            'notes' => $data['notes'] ?? null,
            'user_id' => auth()->id(),
            'shop_customer_id' => session('pos_shop_customer_id'),
            'session_keys' => [
                'pos_coupon' => session('pos_coupon'),
                'pos_loyalty_redeem_points' => session('pos_loyalty_redeem_points'),
                'pos_sale_discount' => session('pos_sale_discount'),
                'pos_price_mode' => session('pos_price_mode'),
            ],
        ];

        $txn = MpesaTransaction::create([
            'phone_number' => $phone,
            'amount' => $amount,
            'status' => MpesaTransaction::STATUS_PENDING,
            'account_reference' => $accountReference,
            'transaction_description' => 'POS Payment',
            'checkout_payload' => $checkoutPayload,
            'initiated_by' => auth()->id(),
        ]);

        try {
            $result = $this->daraja->stkPush(
                $amount,
                $phone,
                $accountReference,
                'POS Payment'
            );
        } catch (RuntimeException $e) {
            $txn->update([
                'status' => MpesaTransaction::STATUS_FAILED,
                'result_description' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
                'transaction_id' => $txn->id,
            ], 422);
        }

        $txn->update([
            'merchant_request_id' => $result['merchant_request_id'],
            'checkout_request_id' => $result['checkout_request_id'],
        ]);

        return response()->json([
            'success' => true,
            'message' => $result['customer_message']
                ?: 'M-PESA payment request sent. Please check the customer\'s phone.',
            'checkout_request_id' => $result['checkout_request_id'],
            'merchant_request_id' => $result['merchant_request_id'],
            'transaction_id' => $txn->id,
            'status' => MpesaTransaction::STATUS_PENDING,
            'amount' => $amount,
        ]);
    }

    /**
     * Safaricom Daraja STK callback (public, no auth).
     */
    public function callback(Request $request): JsonResponse
    {
        $payload = $request->all();

        Log::info('M-PESA callback received', [
            'has_body' => isset($payload['Body']),
            'keys' => array_keys($payload),
        ]);

        $callback = data_get($payload, 'Body.stkCallback');
        if (! is_array($callback)) {
            Log::warning('M-PESA callback missing stkCallback body');

            return response()->json(['ResultCode' => 0, 'ResultDesc' => 'Accepted']);
        }

        $checkoutRequestId = $callback['CheckoutRequestID'] ?? null;
        $merchantRequestId = $callback['MerchantRequestID'] ?? null;
        $resultCode = $callback['ResultCode'] ?? null;
        $resultDesc = $callback['ResultDesc'] ?? null;

        if (! $checkoutRequestId) {
            Log::warning('M-PESA callback missing CheckoutRequestID');

            return response()->json(['ResultCode' => 0, 'ResultDesc' => 'Accepted']);
        }

        try {
            DB::transaction(function () use (
                $payload,
                $callback,
                $checkoutRequestId,
                $merchantRequestId,
                $resultCode,
                $resultDesc
            ) {
                /** @var MpesaTransaction|null $txn */
                $txn = MpesaTransaction::query()
                    ->where('checkout_request_id', $checkoutRequestId)
                    ->lockForUpdate()
                    ->first();

                if (! $txn) {
                    Log::warning('M-PESA callback unknown CheckoutRequestID', [
                        'checkout_request_id' => $checkoutRequestId,
                    ]);

                    return;
                }

                // Idempotent: already finalized
                if ($txn->isFinal()) {
                    Log::info('M-PESA callback ignored (already final)', [
                        'checkout_request_id' => $checkoutRequestId,
                        'status' => $txn->status,
                    ]);

                    return;
                }

                $txn->callback_payload = $payload;
                $txn->result_code = $resultCode !== null ? (string) $resultCode : null;
                $txn->result_description = is_string($resultDesc) ? $resultDesc : null;
                if ($merchantRequestId && ! $txn->merchant_request_id) {
                    $txn->merchant_request_id = $merchantRequestId;
                }

                $status = MpesaTransaction::statusFromResultCode($resultCode);

                if ($status !== MpesaTransaction::STATUS_SUCCESS) {
                    $txn->status = $status;
                    $txn->save();

                    Log::info('M-PESA payment failed', [
                        'checkout_request_id' => $checkoutRequestId,
                        'result_code' => $resultCode,
                        'status' => $status,
                    ]);

                    return;
                }

                $meta = $this->extractCallbackMetadata($callback);
                $txn->mpesa_receipt_number = $meta['mpesa_receipt_number'];
                $txn->transaction_date = $meta['transaction_date'];
                if (! empty($meta['phone_number'])) {
                    $txn->phone_number = $meta['phone_number'];
                }
                if ($meta['amount'] !== null) {
                    $txn->amount = $meta['amount'];
                }

                $payloadCtx = $txn->checkout_payload ?? [];
                if (empty($payloadCtx['cart'])) {
                    $txn->status = MpesaTransaction::STATUS_FAILED;
                    $txn->result_description = 'Missing checkout payload; cannot complete sale.';
                    $txn->save();
                    Log::error('M-PESA success callback missing cart payload', [
                        'checkout_request_id' => $checkoutRequestId,
                    ]);

                    return;
                }

                try {
                    $order = $this->saleCompleter->complete([
                        'cart' => $payloadCtx['cart'],
                        'totals' => $payloadCtx['totals'],
                        'payment_method' => 'mobile_money',
                        'customer_name' => $payloadCtx['customer_name'] ?? null,
                        'customer_phone' => $payloadCtx['customer_phone'] ?? $txn->phone_number,
                        'customer_email' => $payloadCtx['customer_email'] ?? null,
                        'notes' => $payloadCtx['notes'] ?? null,
                        'user_id' => (int) ($payloadCtx['user_id'] ?? $txn->initiated_by),
                        'shop_customer_id' => $payloadCtx['shop_customer_id'] ?? null,
                        'payment_reference' => $txn->mpesa_receipt_number,
                    ]);
                } catch (Throwable $e) {
                    // Customer was charged; do not lose receipt / leave as pending forever.
                    $txn->status = MpesaTransaction::STATUS_FAILED;
                    $txn->result_description = 'Payment received but sale could not be completed: '.$e->getMessage();
                    $txn->save();

                    Log::error('M-PESA payment received but sale completion failed', [
                        'checkout_request_id' => $checkoutRequestId,
                        'mpesa_receipt' => $txn->mpesa_receipt_number,
                        'message' => $e->getMessage(),
                    ]);

                    return;
                }

                $txn->sale_id = $order->id;
                $txn->status = MpesaTransaction::STATUS_SUCCESS;
                $txn->save();

                Audit::log(
                    'sale_created',
                    'Sale '.$order->order_number.' completed (mobile money / M-PESA) for KES '.number_format((float) $order->total_amount, 2),
                    $order,
                    [
                        'reference' => $order->order_number,
                        'payment_method' => 'mobile_money',
                        'mpesa_receipt' => $txn->mpesa_receipt_number,
                        'checkout_request_id' => $checkoutRequestId,
                        'total' => (float) $order->total_amount,
                    ],
                    'pos'
                );

                Log::info('M-PESA payment completed', [
                    'checkout_request_id' => $checkoutRequestId,
                    'sale_id' => $order->id,
                    'order_number' => $order->order_number,
                    'mpesa_receipt' => $txn->mpesa_receipt_number,
                ]);
            });
        } catch (Throwable $e) {
            Log::error('M-PESA callback processing error', [
                'checkout_request_id' => $checkoutRequestId,
                'message' => $e->getMessage(),
            ]);
        }

        // Always acknowledge to Safaricom
        return response()->json(['ResultCode' => 0, 'ResultDesc' => 'Accepted']);
    }

    /**
     * Poll transaction status from POS frontend.
     */
    public function status(string $checkoutRequestId): JsonResponse
    {
        $txn = MpesaTransaction::query()
            ->where('checkout_request_id', $checkoutRequestId)
            ->first();

        if (! $txn) {
            return response()->json([
                'success' => false,
                'message' => 'Transaction not found.',
                'status' => 'unknown',
            ], 404);
        }

        // Only allow the initiator (or full admin) to poll
        $user = auth()->user();
        if ($user && (int) $txn->initiated_by !== (int) $user->id && ! $user->isFullAdmin()) {
            return response()->json([
                'success' => false,
                'message' => 'Not authorized to view this transaction.',
            ], 403);
        }

        $payload = [
            'success' => true,
            'status' => $txn->status,
            'amount' => (float) $txn->amount,
            'phone' => $this->daraja->maskPhone($txn->phone_number),
            'result_description' => $txn->result_description,
            'mpesa_receipt_number' => $txn->mpesa_receipt_number,
            'checkout_request_id' => $txn->checkout_request_id,
            'sale_id' => $txn->sale_id,
            'is_final' => $txn->isFinal(),
        ];

        if ($txn->isSuccessful() && $txn->sale_id) {
            $payload['receipt_url'] = route('admin.pos.receipt', $txn->sale_id);
            $payload['order_number'] = $txn->sale?->order_number;

            // Clear POS cart once payment is confirmed (idempotent for polling).
            if (session()->has('pos_cart')) {
                session()->forget([
                    'pos_cart',
                    'pos_coupon',
                    'pos_shop_customer_id',
                    'pos_loyalty_redeem_points',
                    'pos_sale_discount',
                    'pos_price_mode',
                ]);
            }
        }

        return response()->json($payload);
    }

    /**
     * Sandbox-only OAuth smoke test (admin). Never fakes a successful payment.
     */
    public function testOAuth(): JsonResponse
    {
        if (config('mpesa.environment') === 'production') {
            return response()->json([
                'success' => false,
                'message' => 'OAuth test is disabled when MPESA_ENVIRONMENT=production.',
            ], 403);
        }

        try {
            $token = $this->daraja->getAccessToken(true);

            return response()->json([
                'success' => true,
                'message' => 'OAuth OK (token obtained; value not returned).',
                'expires_in' => $token['expires_in'] ?? null,
                'environment' => config('mpesa.environment'),
            ]);
        } catch (RuntimeException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    /**
     * @param  array<string, mixed>  $callback
     * @return array{mpesa_receipt_number: ?string, transaction_date: ?string, phone_number: ?string, amount: ?float}
     */
    private function extractCallbackMetadata(array $callback): array
    {
        $items = data_get($callback, 'CallbackMetadata.Item', []);
        $map = [];
        if (is_array($items)) {
            foreach ($items as $item) {
                if (! is_array($item) || empty($item['Name'])) {
                    continue;
                }
                $map[$item['Name']] = $item['Value'] ?? null;
            }
        }

        return [
            'mpesa_receipt_number' => isset($map['MpesaReceiptNumber']) ? (string) $map['MpesaReceiptNumber'] : null,
            'transaction_date' => isset($map['TransactionDate']) ? (string) $map['TransactionDate'] : null,
            'phone_number' => isset($map['PhoneNumber']) ? (string) $map['PhoneNumber'] : null,
            'amount' => isset($map['Amount']) ? (float) $map['Amount'] : null,
        ];
    }
}
