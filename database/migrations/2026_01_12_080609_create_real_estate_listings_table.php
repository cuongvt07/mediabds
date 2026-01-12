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
        Schema::create('real_estate_listings', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('type')->default('Cần bán'); // Cần bán, Cho thuê, Cần mua
            $table->string('property_type')->default('Nhà phố'); // Nhà phố, Đất nền, v.v.
            
            // Location
            $table->string('province_id')->nullable();
            $table->string('district_id')->nullable();
            $table->string('ward_id')->nullable();
            $table->string('address')->nullable();
            
            // Details
            $table->decimal('area', 10, 2)->nullable();
            $table->decimal('price', 15, 2)->nullable();
            $table->string('price_unit')->default('Tỷ'); // Tỷ, Triệu, Thỏa thuận
            
            $table->integer('floors')->nullable();
            $table->integer('bedrooms')->nullable();
            $table->integer('toilets')->nullable();
            $table->string('direction')->nullable();
            $table->decimal('front_width', 8, 2)->nullable();
            $table->decimal('road_width', 8, 2)->nullable();
            
            $table->string('youtube_link')->nullable();
            $table->text('description')->nullable();
            
            // Images - Stored as JSON array of paths
            $table->json('images')->nullable();
            
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('real_estate_listings');
    }
};
