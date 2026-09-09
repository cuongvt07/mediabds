<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Module Vault (tích lũy sinh lời + rút tiền) — HOÀN TOÀN ĐỘC LẬP với hệ thống
 * user/auth hiện tại. Không có khóa ngoại nào trỏ tới bảng `users`.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('vault_users', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('phone')->unique();
            $table->string('email')->nullable()->unique();
            $table->string('password');
            $table->string('avatar_url')->nullable();
            $table->string('vip_tier')->default('standard'); // standard|vip_silver|vip_gold
            $table->string('vault_code')->unique(); // mã định danh hiển thị, vd VAULT-889201
            $table->string('referral_code')->unique(); // mã mời bạn của chính user này
            $table->unsignedBigInteger('referred_by_id')->nullable(); // vault_users.id giới thiệu (tự tham chiếu, không FK cứng để tránh ràng buộc xoá)
            $table->string('ekyc_level')->default('0'); // 0|1|2|3
            $table->boolean('face_id_enabled')->default(false);
            $table->string('pin_code_hash')->nullable(); // PIN giao dịch 6 số, hash riêng (không phải password)
            $table->timestamp('pin_set_at')->nullable();
            $table->timestamp('phone_verified_at')->nullable();
            $table->timestamp('last_login_at')->nullable();
            $table->rememberToken();
            $table->timestamps();

            $table->index('referred_by_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('vault_users');
    }
};
