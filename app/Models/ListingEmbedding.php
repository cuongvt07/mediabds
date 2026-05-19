<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ListingEmbedding extends Model
{
    protected $fillable = ['listing_id', 'content_hash', 'model', 'embedding'];

    protected $casts = [
        'embedding' => 'array',
    ];

    public function listing(): BelongsTo
    {
        return $this->belongsTo(RealEstateListing::class, 'listing_id');
    }
}
