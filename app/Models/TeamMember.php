<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class TeamMember extends Model
{
    protected $fillable = [
        'name',
        'position',
        'description',
        'image',
        'facebook_url',
        'twitter_url',
        'linkedin_url',
        'instagram_url',
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
