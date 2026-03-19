<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RealEstateListingSaleMember extends Model
{
    protected $fillable = [
        'sale_id',
        'user_id',
        'received_amount',
    ];

    protected $casts = [
        'received_amount' => 'float',
    ];

    public function sale(): BelongsTo
    {
        return $this->belongsTo(RealEstateListingSale::class, 'sale_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
