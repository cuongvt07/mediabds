<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Nạp tiền qua SePay (VietQR): thêm `payment_code` — mã tham chiếu NGẮN, duy
 * nhất, nhúng vào nội dung chuyển khoản QR để SePay đối soát đúng giao dịch
 * khi bắn webhook về (khác `idempotency_key` — UUID quá dài để nhúng vào nội
 * dung CK, dễ bị ngân hàng/app cắt hoặc user gõ tay sai).
 *
 * `sepay_transaction_id` lưu id giao dịch phía SePay (nếu có) — dùng để log
 * đối soát, KHÔNG dùng để khớp giao dịch (khớp bằng payment_code).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('vault_deposit_requests', function (Blueprint $table) {
            $table->string('payment_code')->nullable()->unique()->after('idempotency_key');
            $table->string('sepay_transaction_id')->nullable()->after('payment_code');
        });
    }

    public function down(): void
    {
        Schema::table('vault_deposit_requests', function (Blueprint $table) {
            $table->dropColumn(['payment_code', 'sepay_transaction_id']);
        });
    }
};
