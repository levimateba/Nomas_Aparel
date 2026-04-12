<?php

namespace App\Models;

use App\Support\PublicStorageUrl;
use Illuminate\Database\Eloquent\Model;

class AboutSection extends Model
{
    protected $fillable = ['title', 'content', 'image_url'];

    public function getImageUrlAttribute($value)
    {
        return PublicStorageUrl::fromPath($value);
    }
}
