<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WebsiteHomeSection extends Model
{
    protected $fillable = [
        'key',
        'title',
        'description',
        'section_type',
        'enabled',
        'source_type',
        'transaction_type',
        'property_kind',
        'category_id',
        'province_name',
        'sort_by',
        'sort_order',
        'limit',
        'href',
        'manual_listing_ids',
        'config',
        'sort_order_index',
    ];

    protected $casts = [
        'enabled' => 'boolean',
        'limit' => 'integer',
        'manual_listing_ids' => 'array',
        'config' => 'array',
        'sort_order_index' => 'integer',
    ];
}
