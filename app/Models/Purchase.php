<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Purchase extends Model
{
    protected $fillable = [
        'purchase_number',
        'supplier_id',
        'stock_location_id',
        'user_id',
        'purchase_order_id',
        'purchase_date',
        'due_date',
        'invoice_reference',
        'subtotal',
        'tax',
        'discount',
        'total',
        'amount_paid',
        'balance_due',
        'payment_status',
        'notes',
    ];

    protected $casts = [
        'purchase_date' => 'date',
        'due_date' => 'date',
        'subtotal' => 'decimal:2',
        'tax' => 'decimal:2',
        'discount' => 'decimal:2',
        'total' => 'decimal:2',
        'amount_paid' => 'decimal:2',
        'balance_due' => 'decimal:2',
    ];

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function purchaseOrder(): BelongsTo
    {
        return $this->belongsTo(PurchaseOrder::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(PurchaseItem::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(PurchasePayment::class);
    }

    public function refreshPaymentState(): void
    {
        $paid = (float) $this->payments()->sum('amount');
        $total = (float) $this->total;
        $balance = max(round($total - $paid, 2), 0);
        $status = match (true) {
            $paid <= 0 => 'unpaid',
            $balance <= 0 => 'paid',
            default => 'partial',
        };

        $this->forceFill([
            'amount_paid' => round($paid, 2),
            'balance_due' => $balance,
            'payment_status' => $status,
        ])->save();
    }
}
