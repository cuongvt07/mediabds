<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RealEstateListingSale extends Model
{
    protected $fillable = [
        'listing_id',
        'sold_by_user_id',
        'project_name',
        'actual_price',
        'revenue_percent',
        'revenue_amount',
        'bonus_amount',
        'net_received_amount',
        'sold_at',
    ];

    protected function casts(): array
    {
        return [
            'actual_price' => 'float',
            'revenue_percent' => 'float',
            'revenue_amount' => 'float',
            'bonus_amount' => 'float',
            'net_received_amount' => 'float',
            'sold_at' => 'datetime',
        ];
    }

    public function listing(): BelongsTo
    {
        return $this->belongsTo(RealEstateListing::class, 'listing_id');
    }

    public function soldBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'sold_by_user_id');
    }
}
