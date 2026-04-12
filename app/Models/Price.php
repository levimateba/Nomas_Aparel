<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Price extends Model
{
    protected $fillable = ['title', 'subtitle', 'description', 'amount', 'display_price', 'billing_period', 'featured', 'features'];
    
    protected $casts = [
        'features' => 'json',
    ];
}
