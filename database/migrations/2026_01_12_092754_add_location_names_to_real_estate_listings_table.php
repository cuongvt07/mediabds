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
        Schema::table('real_estate_listings', function (Blueprint $table) {
            $table->string('province_name')->nullable()->after('province_id');
            $table->string('district_name')->nullable()->after('district_id');
            $table->string('ward_name')->nullable()->after('ward_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('real_estate_listings', function (Blueprint $table) {
            $table->dropColumn(['province_name', 'district_name', 'ward_name']);
        });
    }
};
