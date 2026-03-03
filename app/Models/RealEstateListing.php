<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Model;

class RealEstateListing extends Model
{
    protected $guarded = [];

    protected $casts = [
        'images' => 'array',
        'is_sold' => 'boolean',
    ];

    public function sale(): HasOne
    {
        return $this->hasOne(RealEstateListingSale::class, 'listing_id');
    }
}
