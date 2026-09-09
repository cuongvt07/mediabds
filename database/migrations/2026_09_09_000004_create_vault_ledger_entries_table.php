<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Sổ cái bút toán — nguồn sự thật (source of truth) cho mọi biến động số dư két.
 * CHỈ INSERT, không bao giờ UPDATE/DELETE một bản ghi đã tạo. Muốn đảo ngược
 * (hoàn tiền) thì ghi thêm 1 bút toán mới ngược dấu, có `reversal_of_id` trỏ
 * về bút toán gốc.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('vault_ledger_entries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('vault_user_id')->constrained('vault_users')->cascadeOnDelete();
            $table->foreignId('vault_id')->nullable()->constrained('vault_vaults')->nullOnDelete();
            $table->string('type'); // deposit|withdrawal|interest|referral_bonus|withdrawal_refund
            $table->bigInteger('amount'); // dương = cộng vào két, âm = trừ khỏi két (đồng)
            $table->unsignedBigInteger('balance_after'); // số dư két NGAY SAU bút toán này (audit trail)
            $table->string('idempotency_key')->unique();
            $table->unsignedBigInteger('reversal_of_id')->nullable(); // trỏ tới bút toán bị đảo ngược (tự tham chiếu)
            $table->string('reference_type')->nullable(); // vd VaultWithdrawalRequest, VaultDepositRequest
            $table->unsignedBigInteger('reference_id')->nullable();
            $table->json('meta')->nullable();
            $table->timestamps();

            $table->index(['vault_user_id', 'created_at']);
            $table->index(['reference_type', 'reference_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('vault_ledger_entries');
    }
};
