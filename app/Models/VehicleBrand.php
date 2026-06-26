<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class VehicleBrand extends Model
{
    protected $guarded = [];

    protected $casts = [
        'sort_order' => 'integer',
        'is_active' => 'boolean',
    ];

    /**
     * Danh sách tên hãng (đang bật) cho một loại xe — gồm cả hãng dùng chung ('both').
     * Trả về mảng tên; fallback hằng số trong VehicleListing nếu bảng trống.
     */
    public static function namesForKind(string $kind): array
    {
        try {
            $names = static::query()
                ->where('is_active', true)
                ->whereIn('vehicle_type', [$kind, 'both'])
                ->orderBy('sort_order')
                ->orderBy('name')
                ->pluck('name')
                ->all();

            if (! empty($names)) {
                return $names;
            }
        } catch (\Throwable $e) {
            // bảng chưa migrate -> dùng fallback
        }

        return $kind === 'motorbike'
            ? VehicleListing::MOTORBIKE_BRANDS
            : VehicleListing::CAR_BRANDS;
    }
}
