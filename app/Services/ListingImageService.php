<?php

namespace App\Services;

use App\Models\SiteSetting;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;

class ListingImageService
{
    /**
     * Lưu ảnh GỐC SẠCH (không bake watermark).
     *
     * Watermark được chèn ON-THE-FLY khi hiển thị (xem App\Support\Watermark + route /wm),
     * nên ảnh cũ lẫn ảnh mới đều luôn có dấu, đổi setting là tự cập nhật, và không làm hỏng
     * ảnh gốc (không re-encode chồng nhiều lần).
     */
    public function storeWithWatermark(UploadedFile $file, string $directory = 'site/listings'): string
    {
        $path = $file->store($directory, 'public');

        return Storage::disk('public')->url($path);
    }

    /**
     * Chèn watermark theo chế độ cấu hình trong admin: 'image' (ảnh logo) hoặc 'text' (tên site).
     * 'image' nhưng chưa upload logo → tự rơi về chữ tên site để ảnh không bao giờ thiếu dấu.
     */
    public function applyWatermarkAuto(string $imagePath): void
    {
        if ($this->watermarkMode() === 'text') {
            $this->applyTextWatermark($imagePath);
        } elseif ($this->watermarkPath()) {
            $this->applyWatermark($imagePath);
        } else {
            $this->applyTextWatermark($imagePath);
        }
    }

    private function watermarkMode(): string
    {
        if (! Schema::hasTable('site_settings')) {
            return 'image';
        }

        return SiteSetting::query()->whereKey('watermark_mode')->value('value') === 'text'
            ? 'text'
            : 'image';
    }

    /** Watermark bằng ảnh logo — đặt GIỮA ảnh, kích thước ~40% chiều rộng. */
    public function applyWatermark(string $imagePath): void
    {
        if (! extension_loaded('gd') || ! is_file($imagePath)) {
            return;
        }

        $watermarkPath = $this->watermarkPath();
        if (! $watermarkPath || ! is_file($watermarkPath)) {
            return;
        }

        [$image, $imageType] = $this->createImage($imagePath);
        [$watermark] = $this->createImage($watermarkPath);

        if (! $image || ! $watermark) {
            if ($image) {
                imagedestroy($image);
            }
            if ($watermark) {
                imagedestroy($watermark);
            }
            return;
        }

        $imageWidth = imagesx($image);
        $imageHeight = imagesy($image);
        $watermarkWidth = imagesx($watermark);
        $watermarkHeight = imagesy($watermark);

        if ($imageWidth <= 0 || $imageHeight <= 0 || $watermarkWidth <= 0 || $watermarkHeight <= 0) {
            imagedestroy($image);
            imagedestroy($watermark);
            return;
        }

        // Kích thước mục tiêu ~40% chiều rộng ảnh, giới hạn không quá 40% chiều cao.
        $targetWidth = max(80, (int) round($imageWidth * 0.40));
        $targetHeight = (int) round($watermarkHeight * ($targetWidth / $watermarkWidth));
        if ($targetHeight > $imageHeight * 0.40) {
            $targetHeight = (int) round($imageHeight * 0.40);
            $targetWidth = (int) round($watermarkWidth * ($targetHeight / $watermarkHeight));
        }

        $resized = imagecreatetruecolor($targetWidth, $targetHeight);
        imagealphablending($resized, false);
        imagesavealpha($resized, true);
        imagefill($resized, 0, 0, imagecolorallocatealpha($resized, 0, 0, 0, 127));
        imagecopyresampled($resized, $watermark, 0, 0, 0, 0, $targetWidth, $targetHeight, $watermarkWidth, $watermarkHeight);

        imagealphablending($image, true);
        $x = (int) round(($imageWidth - $targetWidth) / 2);
        $y = (int) round(($imageHeight - $targetHeight) / 2);
        imagecopy($image, $resized, $x, $y, 0, 0, $targetWidth, $targetHeight);

        $this->saveImage($image, $imagePath, $imageType);

        imagedestroy($image);
        imagedestroy($watermark);
        imagedestroy($resized);
    }

    /** Watermark bằng tên site — chữ TO, MỜ, nổi (emboss), đặt GIỮA ảnh. */
    public function applyTextWatermark(string $imagePath): void
    {
        if (! extension_loaded('gd') || ! is_file($imagePath)) {
            return;
        }

        $text = $this->siteName();
        if ($text === '') {
            return;
        }

        [$image, $imageType] = $this->createImage($imagePath);
        if (! $image) {
            return;
        }

        $imageWidth = imagesx($image);
        $imageHeight = imagesy($image);
        if ($imageWidth <= 0 || $imageHeight <= 0) {
            imagedestroy($image);
            return;
        }

        imagealphablending($image, true);
        imagesavealpha($image, true);

        $font = $this->fontPath();

        // Chữ trắng MỜ + bóng tối nhẹ lệch 2px → cảm giác "nổi mờ" đè giữa ảnh.
        $white = imagecolorallocatealpha($image, 255, 255, 255, 88);  // alpha cao = mờ
        $shadow = imagecolorallocatealpha($image, 0, 0, 0, 96);

        if ($font && function_exists('imagettftext')) {
            // Tính cỡ chữ để bề ngang chữ ~72% chiều rộng ảnh.
            $base = 100;
            $baseBox = imagettfbbox($base, 0, $font, $text);
            $baseWidth = max(1, abs($baseBox[2] - $baseBox[0]));
            $fontSize = max(12, (int) round($base * ($imageWidth * 0.72) / $baseWidth));

            $box = imagettfbbox($fontSize, 0, $font, $text);
            $textWidth = abs($box[2] - $box[0]);
            $textHeight = abs($box[1] - $box[7]);
            $x = (int) round(($imageWidth - $textWidth) / 2 - $box[0]);
            $y = (int) round(($imageHeight - $textHeight) / 2 - $box[7]);

            imagettftext($image, $fontSize, 0, $x + 2, $y + 2, $shadow, $font, $text);
            imagettftext($image, $fontSize, 0, $x, $y, $white, $font, $text);
        } else {
            // Fallback không cần TTF (LƯU Ý: font built-in không hiện được dấu tiếng Việt).
            $gdFont = 5;
            $textWidth = imagefontwidth($gdFont) * strlen($text);
            $textHeight = imagefontheight($gdFont);
            $x = (int) round(($imageWidth - $textWidth) / 2);
            $y = (int) round(($imageHeight - $textHeight) / 2);
            imagestring($image, $gdFont, $x + 1, $y + 1, $text, $shadow);
            imagestring($image, $gdFont, $x, $y, $text, $white);
        }

        $this->saveImage($image, $imagePath, $imageType);
        imagedestroy($image);
    }

    private function siteName(): string
    {
        if (! Schema::hasTable('site_settings')) {
            return (string) config('app.name', '');
        }

        return (string) (SiteSetting::query()->whereKey('site_name')->value('value')
            ?: config('app.name', ''));
    }

    private function fontPath(): ?string
    {
        // Ưu tiên font đặt trong repo (nên dùng font hỗ trợ tiếng Việt, vd Be Vietnam Pro / Roboto).
        $candidates = glob(resource_path('fonts') . '/*.{ttf,otf}', GLOB_BRACE) ?: [];

        // Fallback một vài font hệ thống phổ biến trên Linux server.
        $candidates = array_merge($candidates, [
            '/usr/share/fonts/truetype/dejavu/DejaVuSans-Bold.ttf',
            '/usr/share/fonts/truetype/dejavu/DejaVuSans.ttf',
        ]);

        foreach ($candidates as $candidate) {
            if ($candidate && is_file($candidate)) {
                return $candidate;
            }
        }

        return null;
    }

    private function watermarkPath(): ?string
    {
        if (! Schema::hasTable('site_settings')) {
            return null;
        }

        $url = SiteSetting::query()->whereKey('watermark_image_url')->value('value');
        if (! $url) {
            return null;
        }

        $path = parse_url($url, PHP_URL_PATH) ?: $url;
        $marker = '/storage/';
        $position = strpos($path, $marker);
        if ($position !== false) {
            return storage_path('app/public/' . ltrim(substr($path, $position + strlen($marker)), '/'));
        }

        return null;
    }

    private function createImage(string $path): array
    {
        $type = @exif_imagetype($path);
        $image = match ($type) {
            IMAGETYPE_JPEG => @imagecreatefromjpeg($path),
            IMAGETYPE_PNG => @imagecreatefrompng($path),
            IMAGETYPE_WEBP => function_exists('imagecreatefromwebp') ? @imagecreatefromwebp($path) : false,
            default => false,
        };

        return [$image ?: null, $type];
    }

    private function saveImage($image, string $path, ?int $type): void
    {
        match ($type) {
            IMAGETYPE_JPEG => imagejpeg($image, $path, 88),
            IMAGETYPE_PNG => imagepng($image, $path, 6),
            IMAGETYPE_WEBP => function_exists('imagewebp') ? imagewebp($image, $path, 88) : imagejpeg($image, $path, 88),
            default => imagejpeg($image, $path, 88),
        };
    }
}
