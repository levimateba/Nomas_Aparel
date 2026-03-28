<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class GalleryItem extends Model
{
    protected $fillable = [
        'title',
        'image',
    ];

    public function getImageAttribute($value)
    {
        if (! $value) {
            return null;
        }

        if (str_starts_with($value, 'http://') || str_starts_with($value, 'https://') || str_starts_with($value, '/storage/')) {
            return $value;
        }

        if (str_starts_with($value, 'public/')) {
            return Storage::url(substr($value, 7));
        }

        return Storage::url($value);
    }
}
