<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('ctv_ranks', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->integer('min_invites')->default(0);
            $table->decimal('min_price', 20, 2)->default(0)->comment('Billion VND');
            $table->decimal('max_price', 20, 2)->nullable()->comment('Billion VND');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ctv_ranks');
    }
};
