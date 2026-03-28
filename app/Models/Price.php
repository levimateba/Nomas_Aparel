<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Price extends Model
{
    protected $fillable = ['title', 'description', 'amount', 'billing_period', 'featured', 'features'];
    
    protected $casts = [
        'features' => 'json',
    ];
}
