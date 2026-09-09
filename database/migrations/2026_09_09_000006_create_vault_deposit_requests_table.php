<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Nạp tiền (mô phỏng) — để có số dư test luồng rút tiền. Không gọi cổng
 * thanh toán thật; job giả lập tự chuyển pending -> success sau vài giây.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('vault_deposit_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('vault_user_id')->constrained('vault_users')->cascadeOnDelete();
            $table->foreignId('vault_id')->constrained('vault_vaults');
            $table->unsignedBigInteger('amount');
            $table->string('status')->default('pending'); // pending|success|failed
            $table->string('idempotency_key')->unique();
            $table->string('method')->default('bank_transfer'); // mô phỏng - chỉ 1 phương thức
            $table->string('note')->nullable(); // vd "Mã GD #89214"
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();

            $table->index(['vault_user_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('vault_deposit_requests');
    }
};
