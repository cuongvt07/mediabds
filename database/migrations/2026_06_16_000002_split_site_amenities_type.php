<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('site_amenities', 'type')) {
            Schema::table('site_amenities', function (Blueprint $table) {
                $table->string('type', 20)->default('amenity')->after('name'); // 'amenity' | 'furniture'
            });
        }

        // Backfill từ cờ cũ nếu còn.
        if (Schema::hasColumn('site_amenities', 'is_furniture')) {
            DB::table('site_amenities')->where('is_furniture', true)->update(['type' => 'furniture']);
            DB::table('site_amenities')->where('is_furniture', false)->update(['type' => 'amenity']);
        }

        // Phân loại lại danh mục seed cho hợp lý (đồ đạc = nội thất, còn lại = tiện ích).
        DB::table('site_amenities')->whereIn('key', ['bed', 'mattress', 'wardrobe', 'kitchen', 'fridge'])->update(['type' => 'furniture']);
        DB::table('site_amenities')->whereIn('key', ['wifi', 'air_conditioner', 'water_heater', 'elevator', 'loft'])->update(['type' => 'amenity']);

        if (Schema::hasColumn('site_amenities', 'is_furniture')) {
            Schema::table('site_amenities', function (Blueprint $table) {
                $table->dropColumn('is_furniture');
            });
        }
    }

    public function down(): void
    {
        if (! Schema::hasColumn('site_amenities', 'is_furniture')) {
            Schema::table('site_amenities', function (Blueprint $table) {
                $table->boolean('is_furniture')->default(false)->after('name');
            });
        }

        DB::table('site_amenities')->where('type', 'furniture')->update(['is_furniture' => true]);

        if (Schema::hasColumn('site_amenities', 'type')) {
            Schema::table('site_amenities', function (Blueprint $table) {
                $table->dropColumn('type');
            });
        }
    }
};
