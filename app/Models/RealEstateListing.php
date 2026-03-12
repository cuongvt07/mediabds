<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Model;
use App\Models\User;

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

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
