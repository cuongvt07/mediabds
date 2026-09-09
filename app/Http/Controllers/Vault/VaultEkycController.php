<?php

namespace App\Http\Controllers\Vault;

use App\Models\Vault\VaultEkycSubmission;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * Nộp hồ sơ eKYC (CCCD/CMND) — GIAI ĐOẠN DUYỆT THỦ CÔNG, chưa có OCR tự động.
 * Admin site chính xem ảnh + duyệt/từ chối qua VaultEkycReviewController (CMS).
 *
 * BẢO MẬT (theo yêu cầu — chống sửa hồ sơ người khác qua thao tác ID):
 * - vault_user_id KHÔNG BAO GIỜ nhận từ request — luôn lấy từ
 *   $request->user('vault')->id (đã xác thực qua Bearer token).
 * - Ảnh lưu visibility PRIVATE trên S3 — không có URL public nào lộ ra; chỉ
 *   đọc được qua route có kiểm tra quyền (xem VaultEkycReviewController).
 * - Toàn bộ query lọc theo vault_user_id của chính request đang đăng nhập,
 *   không tin bất kỳ ID nào truyền từ client (route model binding chỉ dùng ở
 *   nơi có check ownership tường minh).
 */
class VaultEkycController extends VaultBaseController
{
    private const MAX_SIZE_KB = 5120; // 5MB/ảnh

    public function show(Request $request)
    {
        $user = $request->user('vault');

        $latest = VaultEkycSubmission::where('vault_user_id', $user->id)
            ->orderByDesc('created_at')
            ->first();

        if (! $latest) {
            return $this->ok(['status' => 'none', 'ekycLevel' => $user->ekyc_level]);
        }

        return $this->ok([
            'status' => $latest->status,
            'rejectionReason' => $latest->rejection_reason,
            'submittedAt' => $latest->created_at->toIso8601String(),
            'reviewedAt' => $latest->reviewed_at?->toIso8601String(),
            'ekycLevel' => $user->ekyc_level,
        ]);
    }

    public function store(Request $request)
    {
        $user = $request->user('vault');

        // Đã ở cấp 2 rồi thì không cho nộp lại (tránh spam ảnh không cần thiết).
        if ((int) $user->ekyc_level >= 2) {
            return $this->fail('Tài khoản đã xác thực cấp 2, không cần nộp lại', 422);
        }

        // Đang có hồ sơ chờ duyệt thì không cho nộp thêm — tránh spam nhiều
        // bản ghi cùng lúc gây khó xử lý cho admin.
        $hasPending = VaultEkycSubmission::where('vault_user_id', $user->id)
            ->where('status', 'pending')
            ->exists();
        if ($hasPending) {
            return $this->fail('Bạn đã có hồ sơ đang chờ duyệt, vui lòng đợi kết quả', 422);
        }

        $data = $request->validate([
            'id_number' => 'required|string|regex:/^\d{9,12}$/',
            'full_name' => 'required|string|max:150',
            'date_of_birth' => 'required|date|before:today',
            // KHÔNG chấp nhận svg — SVG có thể chứa script, không phải rủi ro
            // chấp nhận được cho upload ảnh giấy tờ tuỳ thân.
            'front_image' => "required|file|image|mimes:jpg,jpeg,png|max:" . self::MAX_SIZE_KB,
            'back_image' => "required|file|image|mimes:jpg,jpeg,png|max:" . self::MAX_SIZE_KB,
        ]);

        $disk = config('filesystems.disks.s3_uploads.bucket')
            ? 's3_uploads'
            : (config('filesystems.disks.s3.bucket') ? 's3' : 'public');

        // Prefix riêng, KHÔNG chung với listing-uploads (ảnh BĐS công khai) —
        // tách bạch rõ ràng dữ liệu định danh nhạy cảm khỏi ảnh thường.
        $prefix = 'vault-ekyc/' . $user->id;

        $frontPath = $this->storePrivate($data['front_image'], $disk, $prefix, 'front');
        $backPath = $this->storePrivate($data['back_image'], $disk, $prefix, 'back');

        $submission = VaultEkycSubmission::create([
            'vault_user_id' => $user->id,
            'id_number_encrypted' => $data['id_number'],
            'full_name_encrypted' => $data['full_name'],
            'date_of_birth' => $data['date_of_birth'],
            'front_image_path' => $disk . '::' . $frontPath, // lưu kèm disk để đọc đúng chỗ khi duyệt
            'back_image_path' => $disk . '::' . $backPath,
            'status' => 'pending',
        ]);

        return $this->ok(['id' => $submission->id, 'status' => 'pending'], 'Đã nộp hồ sơ, chờ admin duyệt', 201);
    }

    private function storePrivate($file, string $disk, string $prefix, string $side): string
    {
        $filename = $side . '-' . Str::lower(Str::random(16)) . '.' . strtolower($file->extension() ?: 'jpg');
        $path = $prefix . '/' . $filename;

        // visibility PRIVATE — quan trọng nhất: đây là ảnh giấy tờ tuỳ thân,
        // tuyệt đối không được có URL public như ảnh listing.
        Storage::disk($disk)->put($path, file_get_contents($file->getRealPath()), ['visibility' => 'private']);

        return $path;
    }
}
