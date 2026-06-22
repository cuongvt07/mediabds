<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ListingReport extends Model
{
    protected $guarded = [];

    protected $casts = [
        'handled_at' => 'datetime',
    ];

    public const REASONS = [
        'tin_ao' => 'Tin ảo',
        'gia_ao' => 'Giá ảo',
        'ngon_tu' => 'Ngôn từ vi phạm',
        'anh_vi_pham' => 'Ảnh vi phạm',
        'sai_thong_tin' => 'Sai thông tin',
        'khac' => 'Khác',
    ];

    public const STATUSES = [
        'pending' => 'Chờ xử lý',
        'resolved_removed' => 'Đã gỡ bài',
        'resolved_kept' => 'Giữ bài',
    ];

    public function listing(): BelongsTo
    {
        return $this->belongsTo(RealEstateListing::class, 'listing_id');
    }

    public function reportedUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reported_user_id');
    }

    public function reporter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reporter_user_id');
    }

    public function handler(): BelongsTo
    {
        return $this->belongsTo(User::class, 'handled_by');
    }
}
