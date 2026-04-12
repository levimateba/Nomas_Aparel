<?php

namespace App\Models;

use App\Support\PublicStorageUrl;
use Illuminate\Database\Eloquent\Model;

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
        return PublicStorageUrl::fromPath($value);
    }
}
