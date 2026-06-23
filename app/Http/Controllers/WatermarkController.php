<?php

namespace App\Http\Controllers;

use App\Services\ListingImageService;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class WatermarkController extends Controller
{
    /**
     * Phục vụ ảnh đã chèn watermark, cache theo {version} (băm từ cấu hình watermark).
     * Lần đầu: copy ảnh gốc → cache → chèn watermark. Lần sau: trả thẳng file cache.
     */
    public function show(string $version, string $path): BinaryFileResponse
    {
        $path = ltrim($path, '/');

        // Chặn path traversal và không xử lý lại chính thư mục cache.
        if (str_contains($path, '..') || str_starts_with($path, '_wm/')) {
            abort(404);
        }

        $disk = Storage::disk('public');
        if (! $disk->exists($path)) {
            abort(404);
        }

        $originalAbs = storage_path('app/public/' . $path);
        $cacheAbs = storage_path('app/public/_wm/' . $version . '/' . $path);

        if (! is_file($cacheAbs)) {
            $dir = dirname($cacheAbs);
            if (! is_dir($dir)) {
                @mkdir($dir, 0775, true);
            }

            if (! @copy($originalAbs, $cacheAbs)) {
                return response()->file($originalAbs, $this->cacheHeaders()); // fallback: ảnh gốc
            }

            app(ListingImageService::class)->applyWatermarkAuto($cacheAbs);
        }

        return response()->file($cacheAbs, $this->cacheHeaders());
    }

    private function cacheHeaders(): array
    {
        return [
            'Cache-Control' => 'public, max-age=31536000, immutable',
        ];
    }
}
