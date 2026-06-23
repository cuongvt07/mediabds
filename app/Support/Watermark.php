<?php

namespace App\Support;

use App\Models\SiteSetting;
use Illuminate\Support\Facades\Schema;

/**
 * Tiện ích chèn watermark ON-THE-FLY khi hiển thị.
 *
 * View gọi Watermark::url($storedImageUrl) để lấy URL đi qua route /wm/{version}/{path}.
 * Route đó sinh ảnh có watermark (cache theo version) — xem App\Http\Controllers\WatermarkController.
 * Version băm từ cấu hình watermark, nên đổi setting → đổi URL → mọi ảnh (cũ + mới) tự cập nhật.
 */
class Watermark
{
    /** Tăng số này mỗi khi đổi CÁCH VẼ watermark (cỡ chữ, vị trí…) để bust cache _wm cũ. */
    private const RENDER = 'v2';

    private static ?string $version = null;

    /** Chuyển URL/đường dẫn ảnh trong storage public thành URL có watermark. Ảnh ngoài giữ nguyên. */
    public static function url(?string $value): ?string
    {
        if (! $value) {
            return $value;
        }

        $path = self::storagePath($value);
        if ($path === null) {
            return $value; // URL ngoài (vd ảnh placeholder Unsplash) — không watermark được.
        }

        return url('/wm/' . self::version() . '/' . $path);
    }

    /** Version băm từ cấu hình watermark (mode + ảnh logo + tên site). Memoize trong 1 request. */
    public static function version(): string
    {
        if (self::$version !== null) {
            return self::$version;
        }

        $mode = '';
        $watermarkImage = '';
        $siteName = '';

        if (Schema::hasTable('site_settings')) {
            $mode = (string) (SiteSetting::query()->whereKey('watermark_mode')->value('value') ?: 'image');
            $watermarkImage = (string) (SiteSetting::query()->whereKey('watermark_image_url')->value('value') ?: '');
            $siteName = (string) (SiteSetting::query()->whereKey('site_name')->value('value') ?: '');
        }

        return self::$version = substr(md5(self::RENDER . '|' . $mode . '|' . $watermarkImage . '|' . $siteName), 0, 10);
    }

    /** Trích đường dẫn tương đối trong disk public; null nếu không thuộc storage của site. */
    public static function storagePath(string $value): ?string
    {
        if (str_starts_with($value, 'http://') || str_starts_with($value, 'https://')) {
            $path = parse_url($value, PHP_URL_PATH) ?: '';
            $marker = '/storage/';
            $position = strpos($path, $marker);
            if ($position === false) {
                return null; // URL ngoài
            }

            return ltrim(substr($path, $position + strlen($marker)), '/');
        }

        if (str_starts_with($value, '/storage/')) {
            return ltrim(substr($value, strlen('/storage/')), '/');
        }

        if (str_starts_with($value, 'storage/')) {
            return ltrim(substr($value, strlen('storage/')), '/');
        }

        return ltrim($value, '/'); // đường dẫn tương đối trong disk public
    }
}
