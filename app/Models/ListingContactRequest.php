<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ListingContactRequest extends Model
{
    protected $guarded = [];

    protected $casts = [
        'handled_at' => 'datetime',
    ];
}
