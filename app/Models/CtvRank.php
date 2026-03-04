<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CtvRank extends Model
{
    protected $fillable = [
        'name',
        'min_invites',
        'min_price',
        'max_price',
    ];

    protected function casts(): array
    {
        return [
            'min_invites' => 'integer',
            'min_price' => 'float',
            'max_price' => 'float',
        ];
    }
}
