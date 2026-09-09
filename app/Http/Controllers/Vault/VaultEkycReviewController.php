<?php

namespace App\Http\Controllers\Vault;

use App\Http\Controllers\Controller;
use App\Models\Vault\VaultEkycSubmission;
use Illuminate\Support\Facades\Storage;

/**
 * Route THUẦN cho ảnh CCCD — dành cho ADMIN SITE CHÍNH (guard 'web' mặc định,
 * middleware ['auth','admin'] đăng ký ở routes/web.php). Logic duyệt/từ chối
 * nằm ở Livewire component App\Livewire\VaultEkycReview (đúng pattern CMS
 * hiện có, xem WebsiteAdmin.php) — controller này CHỈ phục vụ ảnh.
 *
 * BẢO MẬT:
 * - Middleware ['auth','admin'] chặn truy cập nếu không phải admin đã đăng
 *   nhập — áp ở route, không tự kiểm tra lại ở đây (tránh trùng lặp logic dễ
 *   quên 1 chỗ).
 * - image(): trả ảnh qua STREAM RESPONSE, không bao giờ redirect/trả URL S3
 *   trực tiếp — ảnh CCCD không có cách nào bị lộ ra ngoài dù link admin bị lộ,
 *   vì response luôn đi qua kiểm tra quyền của route này.
 */
class VaultEkycReviewController extends Controller
{
    /** Trả 1 mặt ảnh CCCD qua stream — KHÔNG bao giờ trả URL public. */
    public function image(VaultEkycSubmission $submission, string $side)
    {
        abort_unless(in_array($side, ['front', 'back'], true), 404);

        $rawPath = $side === 'front' ? $submission->front_image_path : $submission->back_image_path;
        [$disk, $path] = $this->splitDiskPath($rawPath);

        abort_unless(Storage::disk($disk)->exists($path), 404);

        $contents = Storage::disk($disk)->get($path);
        $mime = Storage::disk($disk)->mimeType($path) ?: 'image/jpeg';

        return response($contents, 200, [
            'Content-Type' => $mime,
            // no-store: trình duyệt/proxy trung gian không được cache ảnh này.
            'Cache-Control' => 'no-store, no-cache, must-revalidate',
        ]);
    }

    /** front_image_path lưu dạng "disk::path" (xem VaultEkycController::store). */
    private function splitDiskPath(string $raw): array
    {
        if (str_contains($raw, '::')) {
            return explode('::', $raw, 2);
        }

        // Dữ liệu cũ (nếu có) không có tiền tố disk — fallback disk mặc định.
        $fallbackDisk = config('filesystems.disks.s3_uploads.bucket') ? 's3_uploads' : 's3';

        return [$fallbackDisk, $raw];
    }
}
