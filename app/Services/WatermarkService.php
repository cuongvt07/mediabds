<?php

namespace App\Services;

use App\Models\SiteSetting;
use Throwable;

/**
 * Applies a text watermark to uploaded listing images using the GD extension.
 *
 * Driven entirely by site settings (CMS → Cài đặt → Watermark). Any failure is
 * swallowed and the original image is kept — watermarking must never break an
 * upload.
 */
class WatermarkService
{
    /** MIME types GD can reliably decode + re-encode here. */
    private const SUPPORTED = ['image/jpeg', 'image/png', 'image/webp'];

    public function isEnabled(): bool
    {
        return (bool) SiteSetting::get('watermark.enabled', false);
    }

    public function supports(?string $mime): bool
    {
        return $mime !== null && in_array($mime, self::SUPPORTED, true);
    }

    /**
     * Return watermarked image binary for the given file, or null when the image
     * should be stored unchanged (disabled, unsupported format, or any error).
     */
    public function apply(string $sourcePath, ?string $mime): ?string
    {
        if (! $this->isEnabled() || ! $this->supports($mime) || ! function_exists('imagecreatefromstring')) {
            return null;
        }

        try {
            $raw = @file_get_contents($sourcePath);
            if ($raw === false) {
                return null;
            }

            $image = @imagecreatefromstring($raw);
            if ($image === false) {
                return null;
            }

            imagealphablending($image, true);
            imagesavealpha($image, true);

            $this->drawWatermark($image);

            return $this->encode($image, $mime);
        } catch (Throwable $e) {
            report($e);

            return null;
        }
    }

    private function drawWatermark($image): void
    {
        $cfg = SiteSetting::get('watermark', []);
        $text = trim((string) ($cfg['text'] ?? ''));
        if ($text === '') {
            return;
        }

        $width = imagesx($image);
        $height = imagesy($image);

        // Scale font with image width but respect the configured size as a floor.
        $configured = (int) ($cfg['font_size'] ?? 22);
        $fontSize = (int) max($configured, round($width * 0.025));
        $margin = (int) ($cfg['margin'] ?? 16);
        $opacity = (int) ($cfg['opacity'] ?? 55);
        $alpha = (int) round((100 - max(0, min(100, $opacity))) / 100 * 127);

        [$r, $g, $b] = $this->hexToRgb((string) ($cfg['color'] ?? '#FFFFFF'));
        $fill = imagecolorallocatealpha($image, $r, $g, $b, $alpha);
        $shadow = imagecolorallocatealpha($image, 0, 0, 0, min(127, $alpha + 25));

        $font = $this->fontPath();
        $position = (string) ($cfg['position'] ?? 'bottom-right');

        if ($font !== null) {
            $box = imagettfbbox($fontSize, 0, $font, $text);
            $textW = abs($box[2] - $box[0]);
            $textH = abs($box[7] - $box[1]);
            [$x, $y] = $this->ttfOrigin($position, $width, $height, $textW, $textH, $margin);
            imagettftext($image, $fontSize, 0, $x + 1, $y + 1, $shadow, $font, $text);
            imagettftext($image, $fontSize, 0, $x, $y, $fill, $font, $text);

            return;
        }

        // Fallback: GD built-in bitmap font (no TTF available in the runtime).
        $glyph = 5;
        $textW = imagefontwidth($glyph) * strlen($text);
        $textH = imagefontheight($glyph);
        [$x, $y] = $this->bitmapOrigin($position, $width, $height, $textW, $textH, $margin);
        imagestring($image, $glyph, $x + 1, $y + 1, $text, $shadow);
        imagestring($image, $glyph, $x, $y, $text, $fill);
    }

    /** TTF baseline origin (imagettftext y is the text baseline). */
    private function ttfOrigin(string $pos, int $w, int $h, int $tw, int $th, int $m): array
    {
        $left = $m;
        $right = $w - $tw - $m;
        $centerX = (int) (($w - $tw) / 2);
        $top = $m + $th;
        $bottom = $h - $m;
        $centerY = (int) (($h + $th) / 2);

        return match ($pos) {
            'top-left' => [$left, $top],
            'top-right' => [$right, $top],
            'bottom-left' => [$left, $bottom],
            'center' => [$centerX, $centerY],
            default => [$right, $bottom], // bottom-right
        };
    }

    /** Bitmap-font top-left origin (imagestring y is the top). */
    private function bitmapOrigin(string $pos, int $w, int $h, int $tw, int $th, int $m): array
    {
        $left = $m;
        $right = $w - $tw - $m;
        $centerX = (int) (($w - $tw) / 2);
        $top = $m;
        $bottom = $h - $th - $m;
        $centerY = (int) (($h - $th) / 2);

        return match ($pos) {
            'top-left' => [$left, $top],
            'top-right' => [$right, $top],
            'bottom-left' => [$left, $bottom],
            'center' => [$centerX, $centerY],
            default => [$right, $bottom], // bottom-right
        };
    }

    private function encode($image, string $mime): ?string
    {
        ob_start();
        $ok = match ($mime) {
            'image/png' => imagepng($image),
            'image/webp' => function_exists('imagewebp') ? imagewebp($image, null, 85) : false,
            default => imagejpeg($image, null, 88),
        };
        $binary = ob_get_clean();

        return $ok && $binary !== false && $binary !== '' ? $binary : null;
    }

    private function hexToRgb(string $hex): array
    {
        $hex = ltrim($hex, '#');
        if (strlen($hex) === 3) {
            $hex = $hex[0] . $hex[0] . $hex[1] . $hex[1] . $hex[2] . $hex[2];
        }
        if (strlen($hex) !== 6 || ! ctype_xdigit($hex)) {
            return [255, 255, 255];
        }

        return [hexdec(substr($hex, 0, 2)), hexdec(substr($hex, 2, 2)), hexdec(substr($hex, 4, 2))];
    }

    private function fontPath(): ?string
    {
        $candidates = array_filter([
            config('site.defaults.watermark.font_path'),
            storage_path('fonts/watermark.ttf'),
            resource_path('fonts/watermark.ttf'),
            '/usr/share/fonts/truetype/dejavu/DejaVuSans-Bold.ttf',
            '/usr/share/fonts/truetype/dejavu/DejaVuSans.ttf',
            '/usr/share/fonts/truetype/liberation/LiberationSans-Bold.ttf',
        ]);

        foreach ($candidates as $path) {
            if ($path && is_string($path) && is_file($path) && function_exists('imagettftext')) {
                return $path;
            }
        }

        return null;
    }
}
