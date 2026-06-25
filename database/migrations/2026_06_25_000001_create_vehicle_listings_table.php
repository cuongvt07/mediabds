<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Bảng tin đăng XE CỘ (ô tô, xe máy) — tách riêng khỏi real_estate_listings.
 *
 * Cấu trúc phần chung (title/slug/code/giá/vị trí/liên hệ/ảnh/trạng thái) bám theo
 * real_estate_listings để tái dùng hạ tầng FE; phần riêng là các thông số xe.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('vehicle_listings', function (Blueprint $table) {
            $table->id();

            // ----- Chung -----
            $table->string('title');
            $table->string('slug')->nullable()->unique();
            $table->string('code')->nullable()->index();
            $table->string('type')->default('Cần bán');      // Cần bán, Cho thuê, Cần mua
            $table->string('vehicle_type')->default('car');   // car (ô tô) | motorbike (xe máy)

            // ----- Thông số xe -----
            $table->string('brand')->nullable();              // Hãng: Toyota, Honda...
            $table->string('model_name')->nullable();         // Dòng xe: Vios, SH...
            $table->unsignedSmallInteger('year')->nullable(); // Năm sản xuất / đời xe
            $table->unsignedInteger('mileage')->nullable();   // Số km đã đi (odo)
            $table->string('transmission')->nullable();       // Số sàn / Số tự động
            $table->string('fuel_type')->nullable();          // Xăng / Dầu / Điện / Hybrid
            $table->string('engine_capacity')->nullable();    // Dung tích / phân khối (vd 1.5L, 150cc)
            $table->string('color')->nullable();              // Màu sắc
            $table->unsignedTinyInteger('seats')->nullable(); // Số chỗ (ô tô)
            $table->string('condition')->nullable();          // Mới / Đã sử dụng
            $table->string('origin')->nullable();             // Nhập khẩu / Lắp ráp trong nước

            // ----- Giá -----
            $table->decimal('price', 15, 2)->nullable();
            $table->string('price_unit')->default('Triệu');   // Triệu, Tỷ, Thỏa thuận

            // ----- Vị trí -----
            $table->string('province_id')->nullable();
            $table->string('district_id')->nullable();
            $table->string('ward_id')->nullable();
            $table->string('province_name')->nullable();
            $table->string('district_name')->nullable();
            $table->string('ward_name')->nullable();
            $table->string('address')->nullable();
            $table->decimal('lat', 10, 7)->nullable();
            $table->decimal('lng', 10, 7)->nullable();

            // ----- Liên hệ -----
            $table->string('contact_name')->nullable();
            $table->string('contact_phone')->nullable();
            $table->string('contact_zalo')->nullable();
            $table->string('contact_type')->nullable();

            // ----- Nội dung & media -----
            $table->text('description')->nullable();
            $table->string('avatar')->nullable();
            $table->json('images')->nullable();
            $table->json('tags')->nullable();
            $table->string('youtube_link')->nullable();

            // ----- Trạng thái -----
            $table->string('status')->default('active');      // active, pending, expired, sold
            $table->string('vip_tier')->default('normal');    // normal, vip1, vip2, vip3
            $table->boolean('is_sold')->default(false);
            $table->unsignedInteger('view_count')->default(0);
            $table->timestamp('published_at')->nullable();
            $table->timestamp('expires_at')->nullable();

            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->unsignedBigInteger('reporter_id')->nullable();

            $table->timestamps();
            $table->softDeletes();

            $table->index(['vehicle_type', 'status']);
            $table->index('brand');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('vehicle_listings');
    }
};
