<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Danh mục HÃNG XE — thay cho hằng số PHP, quản lý được từ admin.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('vehicle_brands', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('vehicle_type')->default('both'); // car | motorbike | both
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['vehicle_type', 'is_active']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('vehicle_brands');
    }
};
