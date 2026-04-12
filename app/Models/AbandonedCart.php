<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AbandonedCart extends Model
{
    protected $fillable = [
        'user_id',
        'email',
        'cart_payload',
        'total_amount',
        'last_activity_at',
        'reminder_sent_at',
    ];

    protected $casts = [
        'cart_payload' => 'array',
        'last_activity_at' => 'datetime',
        'reminder_sent_at' => 'datetime',
    ];
}
