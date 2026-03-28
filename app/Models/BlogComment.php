<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BlogComment extends Model
{
    protected $fillable = [
        'blog_post_id',
        'name',
        'email',
        'comment',
        'approved',
    ];

    protected $casts = [
        'approved' => 'boolean',
    ];

    public function post()
    {
        return $this->belongsTo(BlogPost::class, 'blog_post_id');
    }
}
