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
            $table->string('contact_type')->nullable()->after('type'); // Chủ or Môi giới
            $table->string('house_password')->nullable()->after('contact_type'); // Alphanumeric password
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('real_estate_listings', function (Blueprint $table) {
            $table->dropColumn(['contact_type', 'house_password']);
        });
    }
};
