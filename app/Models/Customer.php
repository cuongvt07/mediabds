<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Customer extends Model
{
    protected $fillable = [
        'code',
        'name',
        'phone',
        'phone2',
        'status',
        'assigned_user_id',
        'budget_from',
        'budget_to',
        'description',
    ];

    protected $casts = [
        'budget_from' => 'decimal:0',
        'budget_to' => 'decimal:0',
    ];

    /**
     * Ensure phone2 always returns string (never null)
     */
    public function getPhone2Attribute($value): string
    {
        return $value ?? '';
    }

    /**
     * Status labels in Vietnamese
     */
    public const STATUS_LABELS = [
        'khach_mua_o' => 'Khách mua ở',
        'dau_tu' => 'Đầu tư',
        'mua' => 'Mua',
        'ban' => 'Bán',
        'dich_vu' => 'Dịch vụ',
    ];

    /**
     * Status colors for UI
     */
    public const STATUS_COLORS = [
        'khach_mua_o' => 'bg-blue-100 text-blue-700',
        'dau_tu' => 'bg-purple-100 text-purple-700',
        'mua' => 'bg-green-100 text-green-700',
        'ban' => 'bg-orange-100 text-orange-700',
        'dich_vu' => 'bg-pink-100 text-pink-700',
    ];

    /**
     * Get assigned employee
     */
    public function assignedUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_user_id');
    }

    /**
     * Get work timeline
     */
    public function works(): HasMany
    {
        return $this->hasMany(CustomerWork::class)->orderByDesc('work_date');
    }

    /**
     * Generate unique customer code
     */
    public static function generateCode(): string
    {
        do {
            $code = 'KH' . str_pad(random_int(0, 9999), 4, '0', STR_PAD_LEFT);
        } while (self::where('code', $code)->exists());

        return $code;
    }

    /**
     * Get status label
     */
    public function getStatusLabelAttribute(): string
    {
        return self::STATUS_LABELS[$this->status] ?? $this->status;
    }

    /**
     * Get status color class
     */
    public function getStatusColorAttribute(): string
    {
        return self::STATUS_COLORS[$this->status] ?? 'bg-gray-100 text-gray-700';
    }

    /**
     * Format budget range
     */
    public function getFormattedBudgetAttribute(): string
    {
        if (!$this->budget_from && !$this->budget_to) {
            return 'Chưa xác định';
        }

        $from = $this->budget_from ? number_format((float) $this->budget_from, 0, ',', '.') . ' VNĐ' : '0';
        $to = $this->budget_to ? number_format((float) $this->budget_to, 0, ',', '.') . ' VNĐ' : '∞';

        return "{$from} - {$to}";
    }
}
