<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BlogPost extends Model
{
    protected $guarded = [];

    protected $casts = [
        'tags' => 'array',
        'published_at' => 'datetime',
    ];
}
