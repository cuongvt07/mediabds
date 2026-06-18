<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('real_estate_listings', function (Blueprint $table) {
            if (! Schema::hasColumn('real_estate_listings', 'moderation_status')) {
                $table->string('moderation_status', 20)->default('approved'); // pending | approved | rejected
            }
            if (! Schema::hasColumn('real_estate_listings', 'rejection_reason')) {
                $table->string('rejection_reason', 500)->nullable();
            }
        });

        // Tin cũ coi như đã duyệt để không bị ẩn khỏi public.
        DB::table('real_estate_listings')->whereNull('moderation_status')->update(['moderation_status' => 'approved']);
    }

    public function down(): void
    {
        Schema::table('real_estate_listings', function (Blueprint $table) {
            foreach (['moderation_status', 'rejection_reason'] as $col) {
                if (Schema::hasColumn('real_estate_listings', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
    }
};
