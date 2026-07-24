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

        $raw = @file_get_contents($sourcePath);
        if ($raw === false) {
            return null;
        }

        return $this->stampBinary($raw, $mime, false);
    }

    /**
     * Watermark raw image bytes. Used by the upload flow (via apply) and by the
     * backfill command, which passes $respectToggle = false so existing images
     * get stamped even when the live toggle is momentarily off. Returns null when
     * the image cannot / should not be changed.
     */
    public function stampBinary(string $raw, ?string $mime, bool $respectToggle = true): ?string
    {
        if ($respectToggle && ! $this->isEnabled()) {
            return null;
        }
        if (! $this->supports($mime) || ! function_exists('imagecreatefromstring')) {
            return null;
        }

        try {
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
        $style = (string) ($cfg['style'] ?? 'single');

        if ($style === 'tiled') {
            $this->drawTiled($image, $text, $fontSize, $font, $fill, $shadow, $width, $height, $cfg);

            return;
        }

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

    /**
     * Repeat the watermark diagonally across the whole image so it cannot be
     * cropped out. Spacing is deliberately airy (density: sparse|normal|dense)
     * to keep the picture readable.
     */
    private function drawTiled($image, string $text, int $fontSize, ?string $font, int $fill, int $shadow, int $width, int $height, array $cfg): void
    {
        $angle = (int) ($cfg['angle'] ?? 30);
        $density = (string) ($cfg['density'] ?? 'sparse');

        // Horizontal / vertical gap as a multiple of the text box size.
        [$gapX, $gapY] = match ($density) {
            'dense' => [1.3, 1.8],
            'normal' => [1.9, 2.4],
            default => [2.6, 3.2], // sparse
        };

        if ($font !== null) {
            $box = imagettfbbox($fontSize, 0, $font, $text);
            $textW = max(1, abs($box[2] - $box[0]));
            $textH = max(1, abs($box[7] - $box[1]));
            $stepX = max(1, (int) round($textW * $gapX));
            $stepY = max(1, (int) round($textH * $gapY));

            $row = 0;
            for ($y = $stepY; $y < $height + $textH; $y += $stepY) {
                $offset = ($row % 2) * (int) ($stepX / 2); // brick-lay alternate rows
                for ($x = -$stepX; $x < $width + $stepX; $x += $stepX) {
                    $px = (int) ($x + $offset);
                    imagettftext($image, $fontSize, $angle, $px + 1, $y + 1, $shadow, $font, $text);
                    imagettftext($image, $fontSize, $angle, $px, $y, $fill, $font, $text);
                }
                $row++;
            }

            return;
        }

        // Fallback: bitmap font cannot rotate, so tile it straight instead.
        $glyph = 5;
        $textW = max(1, imagefontwidth($glyph) * strlen($text));
        $textH = max(1, imagefontheight($glyph));
        $stepX = max(1, (int) round($textW * $gapX));
        $stepY = max(1, (int) round($textH * $gapY * 2));

        $row = 0;
        for ($y = 0; $y < $height; $y += $stepY) {
            $offset = ($row % 2) * (int) ($stepX / 2);
            for ($x = -$stepX; $x < $width; $x += $stepX) {
                $px = (int) ($x + $offset);
                imagestring($image, $glyph, $px + 1, $y + 1, $text, $shadow);
                imagestring($image, $glyph, $px, $y, $text, $fill);
            }
            $row++;
        }
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
