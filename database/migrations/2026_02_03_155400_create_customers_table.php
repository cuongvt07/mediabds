<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('customers', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique(); // KH + 4 random digits
            $table->string('name');
            $table->string('phone');
            $table->enum('status', ['khach_mua_o', 'dau_tu', 'mua', 'ban', 'dich_vu'])->default('khach_mua_o');
            $table->foreignId('assigned_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->decimal('budget_from', 15, 0)->nullable(); // VND
            $table->decimal('budget_to', 15, 0)->nullable(); // VND
            $table->text('description')->nullable();
            $table->timestamps();

            $table->index('phone');
            $table->index('status');
            $table->index('assigned_user_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('customers');
    }
};
