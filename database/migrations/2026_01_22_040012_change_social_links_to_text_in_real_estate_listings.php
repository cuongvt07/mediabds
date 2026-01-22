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
            $table->text('facebook_link')->nullable()->change();
            $table->text('google_map_link')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('real_estate_listings', function (Blueprint $table) {
            $table->string('facebook_link')->nullable()->change();
            $table->string('google_map_link')->nullable()->change();
        });
    }
};
