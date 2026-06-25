<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Tin đăng XE CỘ (ô tô / xe máy). Tách riêng khỏi RealEstateListing.
 *
 * Các hằng số phân loại đặt ngay trong model để admin (Livewire), API controller
 * và Resource dùng chung một nguồn — tránh lặp như bên BĐS.
 */
class VehicleListing extends Model
{
    use SoftDeletes;

    protected $guarded = [];

    protected $casts = [
        'images' => 'array',
        'tags' => 'array',
        'is_sold' => 'boolean',
        'lat' => 'float',
        'lng' => 'float',
        'year' => 'integer',
        'mileage' => 'integer',
        'seats' => 'integer',
        'view_count' => 'integer',
        'published_at' => 'datetime',
        'expires_at' => 'datetime',
    ];

    /** Loại phương tiện (trục phân loại gốc). */
    public const VEHICLE_TYPES = [
        'car' => 'Ô tô',
        'motorbike' => 'Xe máy',
    ];

    public const TRANSMISSIONS = [
        'manual' => 'Số sàn',
        'automatic' => 'Số tự động',
        'cvt' => 'Số vô cấp (CVT)',
        'semi_automatic' => 'Số bán tự động',
    ];

    public const FUEL_TYPES = [
        'petrol' => 'Xăng',
        'diesel' => 'Dầu (Diesel)',
        'electric' => 'Điện',
        'hybrid' => 'Hybrid',
    ];

    public const CONDITIONS = [
        'new' => 'Mới',
        'used' => 'Đã sử dụng',
    ];

    public const ORIGINS = [
        'imported' => 'Nhập khẩu',
        'domestic' => 'Lắp ráp trong nước',
    ];

    /** Hãng phổ biến theo loại xe — gợi ý cho dropdown, không ràng buộc cứng. */
    public const CAR_BRANDS = [
        'Toyota', 'Honda', 'Hyundai', 'Kia', 'Mazda', 'Ford', 'Mitsubishi',
        'Mercedes-Benz', 'BMW', 'Audi', 'VinFast', 'Suzuki', 'Nissan',
        'Chevrolet', 'Lexus', 'Peugeot', 'MG', 'Isuzu', 'Khác',
    ];

    public const MOTORBIKE_BRANDS = [
        'Honda', 'Yamaha', 'Suzuki', 'SYM', 'Piaggio', 'Vespa', 'VinFast',
        'Kawasaki', 'Ducati', 'Harley-Davidson', 'Khác',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function reporter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reporter_id');
    }

    /** Nhãn hiển thị cho loại xe. */
    public function getVehicleTypeLabelAttribute(): string
    {
        return self::VEHICLE_TYPES[$this->vehicle_type] ?? (string) $this->vehicle_type;
    }
}
