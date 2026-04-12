<?php

namespace App\Models;

use App\Support\PublicStorageUrl;
use Illuminate\Database\Eloquent\Model;

class Video extends Model
{
    protected $fillable = [
        'title',
        'description',
        'video_path',
        'video_url',
    ];

    public function getVideoPathAttribute($value)
    {
        return PublicStorageUrl::fromPath($value);
    }

    public function getVideoUrlAttribute($value)
    {
        return $value ?: null;
    }
}
