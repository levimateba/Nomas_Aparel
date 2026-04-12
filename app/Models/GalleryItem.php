<?php

namespace App\Models;

use App\Support\PublicStorageUrl;
use Illuminate\Database\Eloquent\Model;

class GalleryItem extends Model
{
    protected $fillable = [
        'title',
        'category',
        'short_description',
        'description',
        'image',
    ];

    public function getImageAttribute($value)
    {
        return PublicStorageUrl::fromPath($value);
    }
}
