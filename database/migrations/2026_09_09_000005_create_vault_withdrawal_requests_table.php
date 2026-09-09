<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('vault_withdrawal_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('vault_user_id')->constrained('vault_users')->cascadeOnDelete();
            $table->foreignId('vault_id')->constrained('vault_vaults');
            $table->foreignId('vault_bank_account_id')->constrained('vault_bank_accounts');
            $table->unsignedBigInteger('amount'); // đồng, luôn số nguyên dương
            $table->string('status')->default('pending'); // pending|processing|success|failed|reversed
            $table->string('idempotency_key')->unique();
            $table->string('gateway_ref')->nullable(); // mã tham chiếu mô phỏng gateway
            $table->string('failure_reason')->nullable();
            $table->timestamp('requested_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();

            $table->index(['vault_user_id', 'status']);
            $table->index('gateway_ref');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('vault_withdrawal_requests');
    }
};
