<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MpesaTransaction extends Model
{
    public const STATUS_PENDING = 'pending';

    public const STATUS_SUCCESS = 'success';

    public const STATUS_FAILED = 'failed';

    public const STATUS_CANCELLED = 'cancelled';

    public const STATUS_TIMEOUT = 'timeout';

    protected $fillable = [
        'sale_id',
        'phone_number',
        'amount',
        'merchant_request_id',
        'checkout_request_id',
        'mpesa_receipt_number',
        'transaction_date',
        'result_code',
        'result_description',
        'status',
        'account_reference',
        'transaction_description',
        'checkout_payload',
        'callback_payload',
        'initiated_by',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'checkout_payload' => 'array',
        'callback_payload' => 'array',
    ];

    public function sale(): BelongsTo
    {
        return $this->belongsTo(Order::class, 'sale_id');
    }

    public function initiator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'initiated_by');
    }

    public function isPending(): bool
    {
        return $this->status === self::STATUS_PENDING;
    }

    public function isFinal(): bool
    {
        return in_array($this->status, [
            self::STATUS_SUCCESS,
            self::STATUS_FAILED,
            self::STATUS_CANCELLED,
            self::STATUS_TIMEOUT,
        ], true);
    }

    public function isSuccessful(): bool
    {
        return $this->status === self::STATUS_SUCCESS;
    }

    /**
     * Map Daraja ResultCode to internal status.
     */
    public static function statusFromResultCode(int|string|null $resultCode): string
    {
        $code = (int) $resultCode;

        return match ($code) {
            0 => self::STATUS_SUCCESS,
            1032 => self::STATUS_CANCELLED,
            1037 => self::STATUS_TIMEOUT,
            default => self::STATUS_FAILED,
        };
    }
}
