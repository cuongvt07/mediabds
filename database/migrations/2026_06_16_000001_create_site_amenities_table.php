<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('site_amenities', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();          // slug bền, lưu trong listings.amenities
            $table->string('name');
            $table->string('icon', 2048)->nullable();  // URL ảnh icon upload
            $table->boolean('is_furniture')->default(false); // cũng hiển thị ở bộ lọc "Nội thất"
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // Seed lại đúng danh mục đang dùng để không vỡ amenities đã lưu trên tin cũ.
        $now = now();
        $seed = [
            ['bed', 'Giường', true],
            ['mattress', 'Nệm', true],
            ['wardrobe', 'Tủ quần áo', true],
            ['wifi', 'Wifi', true],
            ['air_conditioner', 'Máy lạnh', true],
            ['kitchen', 'Kệ bếp', true],
            ['water_heater', 'Nước nóng', true],
            ['fridge', 'Tủ lạnh', true],
            ['elevator', 'Thang máy', false],
            ['loft', 'Gác', false],
        ];

        $rows = [];
        foreach ($seed as $i => [$key, $name, $isFurniture]) {
            $rows[] = [
                'key' => $key,
                'name' => $name,
                'icon' => null,
                'is_furniture' => $isFurniture,
                'sort_order' => $i + 1,
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }

        DB::table('site_amenities')->insert($rows);
    }

    public function down(): void
    {
        Schema::dropIfExists('site_amenities');
    }
};
