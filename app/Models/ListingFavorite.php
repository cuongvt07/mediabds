<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ListingFavorite extends Model
{
    protected $guarded = [];

    public function listing(): BelongsTo
    {
        return $this->belongsTo(RealEstateListing::class, 'listing_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
