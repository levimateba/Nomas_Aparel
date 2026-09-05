<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PurchaseOrder extends Model
{
    public const STATUS_DRAFT = 'draft';
    public const STATUS_SUBMITTED = 'submitted';
    public const STATUS_APPROVED = 'approved';
    public const STATUS_PARTIALLY_RECEIVED = 'partially_received';
    public const STATUS_RECEIVED = 'received';
    public const STATUS_CANCELLED = 'cancelled';
    public const STATUS_CLOSED = 'closed';

    protected $fillable = [
        'po_number',
        'supplier_id',
        'user_id',
        'approved_by',
        'order_date',
        'expected_date',
        'subtotal',
        'tax',
        'discount',
        'total',
        'status',
        'notes',
        'submitted_at',
        'approved_at',
        'cancelled_at',
    ];

    protected $casts = [
        'order_date' => 'date',
        'expected_date' => 'date',
        'subtotal' => 'decimal:2',
        'tax' => 'decimal:2',
        'discount' => 'decimal:2',
        'total' => 'decimal:2',
        'submitted_at' => 'datetime',
        'approved_at' => 'datetime',
        'cancelled_at' => 'datetime',
    ];

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function items(): HasMany
    {
        return $this->hasMany(PurchaseOrderItem::class);
    }

    public function receipts(): HasMany
    {
        return $this->hasMany(Purchase::class);
    }

    public function refreshTotalsAndStatus(): void
    {
        $subtotal = (float) $this->items()->sum('total');
        $total = max($subtotal - (float) $this->discount, 0) + (float) $this->tax;
        $ordered = (int) $this->items()->sum('ordered_qty');
        $received = (int) $this->items()->sum('received_qty');

        $status = $this->status;
        if (! in_array($status, [self::STATUS_CANCELLED, self::STATUS_CLOSED, self::STATUS_DRAFT, self::STATUS_SUBMITTED], true)) {
            if ($received <= 0) {
                $status = self::STATUS_APPROVED;
            } elseif ($received < $ordered) {
                $status = self::STATUS_PARTIALLY_RECEIVED;
            } else {
                $status = self::STATUS_RECEIVED;
            }
        }

        $this->forceFill([
            'subtotal' => round($subtotal, 2),
            'total' => round($total, 2),
            'status' => $status,
        ])->save();
    }
}
