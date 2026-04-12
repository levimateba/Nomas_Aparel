<?php

namespace App\Models;

use App\Support\PublicStorageUrl;
use Illuminate\Database\Eloquent\Model;

class NewsEvent extends Model
{
    protected $fillable = [
        'title',
        'excerpt',
        'content',
        'image',
        'event_date',
        'published',
    ];

    protected $casts = [
        'event_date' => 'datetime',
        'published' => 'boolean',
    ];

    public function getImageAttribute($value)
    {
        return PublicStorageUrl::fromPath($value);
    }
}
