<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Hồ sơ eKYC (xác thực CCCD/CMND) — GIAI ĐOẠN NÀY duyệt THỦ CÔNG bởi admin,
 * chưa gắn OCR tự động (xem ghi chú ở VaultEkycController). Bảng lưu đường
 * dẫn ảnh (KHÔNG public — chỉ đọc qua route có kiểm tra quyền admin), không
 * lưu số CCCD/họ tên tách rời ở giai đoạn này vì chưa có OCR để tự điền —
 * admin đọc trực tiếp trên ảnh khi duyệt.
 *
 * 1 user có thể có NHIỀU submission theo thời gian (nộp lại khi bị từ chối);
 * chỉ submission mới nhất có ý nghĩa cho ekyc_level hiện tại của user.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('vault_ekyc_submissions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('vault_user_id')->constrained('vault_users')->cascadeOnDelete();
            $table->string('id_number_encrypted')->nullable(); // số CCCD/CMND, user tự nhập (không OCR ở giai đoạn này)
            $table->string('full_name_encrypted')->nullable(); // họ tên theo giấy tờ, user tự nhập
            $table->date('date_of_birth')->nullable();
            $table->string('front_image_path'); // đường dẫn S3, KHÔNG public — chỉ đọc qua route có check quyền
            $table->string('back_image_path');
            $table->string('status')->default('pending'); // pending|approved|rejected
            $table->string('rejection_reason')->nullable();
            $table->unsignedBigInteger('reviewed_by_user_id')->nullable(); // users.id (admin site chính, KHÔNG FK cứng sang bảng users vì Vault độc lập)
            $table->timestamp('reviewed_at')->nullable();
            $table->timestamps();

            $table->index(['vault_user_id', 'status']);
            $table->index(['status', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('vault_ekyc_submissions');
    }
};
