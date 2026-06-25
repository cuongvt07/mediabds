<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('real_estate_listings', function (Blueprint $table) {
            // Hỗ trợ lọc/gợi ý theo tiêu đề (LIKE prefix + ORDER BY). code đã có unique index sẵn.
            $table->index('title', 'rel_title_index');
        });
    }

    public function down(): void
    {
        Schema::table('real_estate_listings', function (Blueprint $table) {
            $table->dropIndex('rel_title_index');
        });
    }
};
