<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('vault_bank_accounts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('vault_user_id')->constrained('vault_users')->cascadeOnDelete();
            $table->string('bank_code'); // vd VCB, TCB
            $table->string('bank_name');
            $table->text('account_number_encrypted');
            $table->string('masked_number'); // vd *8899
            $table->string('account_name');
            $table->boolean('is_default')->default(false);
            $table->boolean('is_verified')->default(false);
            $table->timestamps();

            $table->index(['vault_user_id', 'is_default']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('vault_bank_accounts');
    }
};
