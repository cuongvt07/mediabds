<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Cấu hình webhook SePay — bảng SINGLETON (luôn chỉ 1 dòng id=1), quản lý qua
 * CMS admin (WebsiteAdmin), KHÔNG dùng chung JSON blob của SiteSetting vì
 * cần mã hoá riêng cột secret bằng Eloquent `encrypted` cast (khoá APP_KEY),
 * SiteSetting::value là JSON thô không mã hoá được từng field.
 *
 * webhook_secret_encrypted: secret TỰ SINH ở server (không phải SePay cấp),
 * dùng để verify chữ ký HMAC-SHA256 SePay gửi trong header X-SePay-Signature
 * — xem SePayWebhookController. Admin copy giá trị này dán vào SePay Console.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sepay_settings', function (Blueprint $table) {
            $table->id();
            $table->text('webhook_secret_encrypted')->nullable();
            $table->string('bank_account_number')->nullable();
            $table->string('bank_name')->nullable();
            $table->string('bank_account_name')->nullable();
            $table->boolean('enabled')->default(false);
            $table->timestamp('last_webhook_at')->nullable();
            $table->foreignId('updated_by_user_id')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sepay_settings');
    }
};
