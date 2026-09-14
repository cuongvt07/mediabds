<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Mã OTP dùng chung cho MỌI luồng cần xác thực SMS trong module Vault:
 * đăng ký (verify_phone), đặt/đổi PIN (set_pin), rút tiền (withdrawal),
 * đổi số điện thoại — 2 bước (change_phone_old, change_phone_new). Phân biệt
 * bằng cột `purpose`.
 *
 * BẢO MẬT:
 * - Chỉ lưu HASH của mã OTP (bcrypt), không bao giờ lưu mã gốc — giống mật
 *   khẩu, để dù DB bị lộ cũng không đọc được mã OTP thật.
 * - `reference_id` (nullable) trỏ tới bản ghi liên quan (vd withdrawal_request
 *   id) để OTP chỉ có hiệu lực cho ĐÚNG giao dịch đó, không dùng chéo được
 *   cho giao dịch khác dù cùng purpose.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('vault_otp_codes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('vault_user_id')->constrained('vault_users')->cascadeOnDelete();
            $table->string('purpose'); // verify_phone|set_pin|withdrawal|change_phone_old|change_phone_new
            $table->string('phone'); // số điện thoại NHẬN otp (đổi SĐT thì đây là số MỚI cần xác nhận)
            $table->string('code_hash');
            $table->unsignedBigInteger('reference_id')->nullable(); // vd vault_withdrawal_requests.id
            $table->unsignedTinyInteger('attempts')->default(0); // số lần nhập sai
            $table->timestamp('expires_at');
            $table->timestamp('consumed_at')->nullable();
            $table->timestamps();

            $table->index(['vault_user_id', 'purpose', 'consumed_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('vault_otp_codes');
    }
};
