<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('real_estate_listings', 'room_type')) {
            Schema::table('real_estate_listings', function (Blueprint $table) {
                $table->string('room_type')->nullable()->after('property_type');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('real_estate_listings', 'room_type')) {
            Schema::table('real_estate_listings', function (Blueprint $table) {
                $table->dropColumn('room_type');
            });
        }
    }
};
