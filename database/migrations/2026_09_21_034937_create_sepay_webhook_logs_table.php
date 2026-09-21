<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Log MỌI request webhook SePay gửi tới — kể cả bị từ chối (sai chữ ký,
 * không tìm thấy mã, amount lệch...) — để admin đối soát/debug chi tiết
 * qua CMS (VaultDeposits hiển thị log gắn với từng lệnh nạp). Đây là dữ
 * liệu audit thuần, KHÔNG ảnh hưởng logic xử lý webhook (ghi log không
 * bao giờ được throw để làm fail cả request webhook).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sepay_webhook_logs', function (Blueprint $table) {
            $table->id();
            // nullable — nhiều request bị từ chối trước khi xác định được deposit nào.
            $table->foreignId('vault_deposit_request_id')->nullable()
                ->constrained('vault_deposit_requests')->nullOnDelete();
            $table->string('payment_code')->nullable();
            $table->string('outcome'); // accepted|rejected_signature|rejected_no_code|rejected_no_deposit|rejected_status|rejected_amount_mismatch
            $table->string('reason')->nullable(); // mô tả ngắn lý do (khớp $outcome)
            $table->json('payload')->nullable(); // raw body đã decode JSON
            $table->string('signature_header')->nullable();
            $table->string('sepay_transaction_id')->nullable();
            $table->ipAddress('ip_address')->nullable();
            $table->timestamps();

            $table->index(['vault_deposit_request_id', 'created_at']);
            $table->index(['outcome', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sepay_webhook_logs');
    }
};
