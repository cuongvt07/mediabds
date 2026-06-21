<?php

namespace App\Services;

use App\Models\SiteSetting;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;

class ListingImageService
{
    public function storeWithWatermark(UploadedFile $file, string $directory = 'site/listings'): string
    {
        $path = $file->store($directory, 'public');
        $this->applyWatermark(storage_path('app/public/' . $path));

        return Storage::disk('public')->url($path);
    }

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

        $targetWidth = max(80, (int) round($imageWidth * 0.22));
        $targetHeight = (int) round($watermarkHeight * ($targetWidth / $watermarkWidth));
        $targetWidth = min($targetWidth, (int) round($imageWidth * 0.45));
        $targetHeight = min($targetHeight, (int) round($imageHeight * 0.25));

        $resized = imagecreatetruecolor($targetWidth, $targetHeight);
        imagealphablending($resized, false);
        imagesavealpha($resized, true);
        imagefill($resized, 0, 0, imagecolorallocatealpha($resized, 0, 0, 0, 127));
        imagecopyresampled($resized, $watermark, 0, 0, 0, 0, $targetWidth, $targetHeight, $watermarkWidth, $watermarkHeight);

        imagealphablending($image, true);
        $padding = max(12, (int) round($imageWidth * 0.025));
        $x = max(0, $imageWidth - $targetWidth - $padding);
        $y = max(0, $imageHeight - $targetHeight - $padding);
        imagecopy($image, $resized, $x, $y, 0, 0, $targetWidth, $targetHeight);

        $this->saveImage($image, $imagePath, $imageType);

        imagedestroy($image);
        imagedestroy($watermark);
        imagedestroy($resized);
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
