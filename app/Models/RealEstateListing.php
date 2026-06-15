<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\User;

class RealEstateListing extends Model
{
    use SoftDeletes;
    protected $guarded = [];

    protected $casts = [
        'images' => 'array',
        'amenities' => 'array',
        'tags' => 'array',
        'is_sold' => 'boolean',
        'lat' => 'float',
        'lng' => 'float',
        'view_count' => 'integer',
        'published_at' => 'datetime',
        'expires_at' => 'datetime',
    ];

    public function sale(): HasOne
    {
        return $this->hasOne(RealEstateListingSale::class, 'listing_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function reporter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reporter_id');
    }

    public function embedding(): HasOne
    {
        return $this->hasOne(ListingEmbedding::class, 'listing_id');
    }

    public function favorites(): HasMany
    {
        return $this->hasMany(ListingFavorite::class, 'listing_id');
    }

    public function contactRequests(): HasMany
    {
        return $this->hasMany(ListingContactRequest::class, 'listing_id');
    }

    public function viewEvents(): HasMany
    {
        return $this->hasMany(ListingViewEvent::class, 'listing_id');
    }

    /**
     * Tách trường contact_phone (có thể chứa nhiều số) thành mảng số đã chuẩn hoá.
     * Hỗ trợ phân tách bằng dấu phẩy / xuyệt / khoảng trắng / gạch ngang.
     * VD: "0901234567 / 0987654321"  → ["0901234567", "0987654321"]
     *     "+84901234567, 0987654321" → ["0901234567", "0987654321"]
     *
     * Trả tối đa 3 số (đề phòng dữ liệu rác).
     */
    public static function parseContactPhones(?string $raw): array
    {
        if (!$raw) return [];
        // Tách theo bất kỳ ký tự nào không phải số/+
        $parts = preg_split('/[^\d+]+/', $raw, -1, PREG_SPLIT_NO_EMPTY);
        $clean = [];
        foreach ($parts as $p) {
            // Bỏ +84 prefix và thay bằng 0
            if (str_starts_with($p, '+84')) $p = '0' . substr($p, 3);
            elseif (str_starts_with($p, '84') && strlen($p) >= 11) $p = '0' . substr($p, 2);
            // Lọc các chuỗi quá ngắn/dài (số VN: 9-11 chữ số)
            $len = strlen($p);
            if ($len < 9 || $len > 12) continue;
            // Loại trùng
            if (!in_array($p, $clean)) $clean[] = $p;
            if (count($clean) >= 3) break;
        }
        return $clean;
    }

    /**
     * [PHASE 4] Khi tin được tạo/sửa các trường ngữ nghĩa → đánh dấu cần re-embed.
     * Không gọi API ở đây để không block save flow — chỉ xoá embedding cũ.
     * Embedding sẽ được sinh lại khi cron chạy hoặc khi user query lần sau.
     */
    protected static function booted(): void
    {
        // [FIX] try/catch để không crash khi bảng listing_embeddings chưa migrate
        static::updated(function (self $l) {
            $semanticFields = ['title', 'description', 'address', 'price', 'price_unit', 'area', 'bedrooms', 'toilets', 'floors', 'direction', 'property_type', 'room_type', 'furnish', 'amenities', 'type'];
            if (collect($semanticFields)->some(fn($f) => $l->wasChanged($f))) {
                try {
                    ListingEmbedding::where('listing_id', $l->id)->delete();
                } catch (\Throwable $e) {
                    // Bảng chưa tồn tại — bỏ qua
                }
            }
        });
        static::deleted(function (self $l) {
            try {
                ListingEmbedding::where('listing_id', $l->id)->delete();
            } catch (\Throwable $e) {
                // Bảng chưa tồn tại — bỏ qua
            }
        });
    }
}
