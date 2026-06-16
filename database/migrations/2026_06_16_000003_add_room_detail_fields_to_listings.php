<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('real_estate_listings', function (Blueprint $table) {
            // Chi phí (nhập tự do)
            if (! Schema::hasColumn('real_estate_listings', 'electricity_price')) {
                $table->string('electricity_price')->nullable();
            }
            if (! Schema::hasColumn('real_estate_listings', 'water_price')) {
                $table->string('water_price')->nullable();
            }
            if (! Schema::hasColumn('real_estate_listings', 'parking_fee')) {
                $table->string('parking_fee')->nullable();
            }
            // Điều kiện
            if (! Schema::hasColumn('real_estate_listings', 'access_hours')) {
                $table->string('access_hours')->nullable();
            }
            if (! Schema::hasColumn('real_estate_listings', 'has_window')) {
                $table->string('has_window')->nullable();
            }
            if (! Schema::hasColumn('real_estate_listings', 'pets_allowed')) {
                $table->string('pets_allowed')->nullable();
            }
            if (! Schema::hasColumn('real_estate_listings', 'parking_available')) {
                $table->string('parking_available')->nullable();
            }
        });
    }

    public function down(): void
    {
        Schema::table('real_estate_listings', function (Blueprint $table) {
            foreach (['electricity_price', 'water_price', 'parking_fee', 'access_hours', 'has_window', 'pets_allowed', 'parking_available'] as $col) {
                if (Schema::hasColumn('real_estate_listings', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
    }
};
