<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CustomerWork extends Model
{
    protected $fillable = [
        'customer_id',
        'user_id',
        'work_date',
        'content',
        'progress',
    ];

    protected $casts = [
        'work_date' => 'date',
    ];

    /**
     * Get the customer this work belongs to
     */
    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    /**
     * Get the user who recorded this work
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get formatted date
     */
    public function getFormattedDateAttribute(): string
    {
        return \Carbon\Carbon::parse($this->work_date)->format('d/m/Y');
    }
}
