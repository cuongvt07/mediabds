<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('blog_posts', function (Blueprint $table) {
            // 'bds' = Bất động sản, 'xe' = Xe, 'general' = Chung
            $table->string('type', 20)->default('bds')->after('category_tag');
        });

        // Cập nhật bài viết có category_tag liên quan đến xe
        DB::table('blog_posts')
            ->where(function ($q) {
                $q->where('category_tag', 'like', '%xe%')
                  ->orWhere('category_tag', 'like', '%Xe%')
                  ->orWhere('category_tag', 'like', '%ô tô%')
                  ->orWhere('category_tag', 'like', '%Ô tô%');
            })
            ->update(['type' => 'xe']);
    }

    public function down(): void
    {
        Schema::table('blog_posts', function (Blueprint $table) {
            $table->dropColumn('type');
        });
    }
};
