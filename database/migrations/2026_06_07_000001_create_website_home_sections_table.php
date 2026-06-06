<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('website_home_sections')) {
            Schema::create('website_home_sections', function (Blueprint $table) {
                $table->id();
                $table->string('key')->unique();
                $table->string('title');
                $table->text('description')->nullable();
                $table->string('section_type')->default('listings');
                $table->boolean('enabled')->default(true);
                $table->string('source_type')->default('latest');
                $table->string('transaction_type')->nullable();
                $table->string('property_kind')->nullable();
                $table->string('category_id')->nullable();
                $table->string('province_name')->nullable();
                $table->string('sort_by')->default('created_at');
                $table->string('sort_order', 4)->default('desc');
                $table->unsignedSmallInteger('limit')->default(8);
                $table->string('href')->nullable();
                $table->json('manual_listing_ids')->nullable();
                $table->json('config')->nullable();
                $table->unsignedSmallInteger('sort_order_index')->default(0);
                $table->timestamps();
            });
        }

        $now = now();
        $rows = [
            [
                'key' => 'featured_latest',
                'title' => 'Tin dang noi bat',
                'description' => 'Tin dang dang hien thi, cap nhat moi nhat tu API',
                'section_type' => 'listings',
                'source_type' => 'latest',
                'limit' => 8,
                'href' => '/tin-dang',
                'sort_order_index' => 10,
            ],
            [
                'key' => 'regions',
                'title' => 'Khu vuc noi bat',
                'description' => 'Thong ke tin dang theo tinh thanh',
                'section_type' => 'regions',
                'source_type' => 'regions',
                'limit' => 5,
                'sort_order_index' => 20,
            ],
            [
                'key' => 'newest',
                'title' => 'Tin dang moi nhat',
                'description' => 'Cap nhat lien tuc theo thoi gian thuc',
                'section_type' => 'listings',
                'source_type' => 'latest',
                'limit' => 8,
                'href' => '/tin-dang',
                'sort_order_index' => 40,
            ],
            [
                'key' => 'land_hot',
                'title' => 'Ban dat nen hot',
                'description' => 'Tin dat nen dang rao ban',
                'section_type' => 'listings',
                'source_type' => 'property',
                'transaction_type' => 'sale',
                'property_kind' => 'land',
                'limit' => 8,
                'href' => '/tin-dang?propertyType=land',
                'sort_order_index' => 50,
            ],
            [
                'key' => 'tools',
                'title' => 'Tien ich',
                'section_type' => 'tools',
                'source_type' => 'static',
                'limit' => 0,
                'sort_order_index' => 60,
            ],
            [
                'key' => 'recently_viewed',
                'title' => 'Da xem gan day',
                'section_type' => 'recently_viewed',
                'source_type' => 'client',
                'limit' => 0,
                'sort_order_index' => 70,
            ],
            [
                'key' => 'blogs',
                'title' => 'Blog',
                'section_type' => 'blogs',
                'source_type' => 'latest',
                'limit' => 10,
                'sort_order_index' => 80,
            ],
            [
                'key' => 'feature_descriptions',
                'title' => 'Mo ta dich vu',
                'section_type' => 'feature_descriptions',
                'source_type' => 'static',
                'limit' => 0,
                'sort_order_index' => 90,
            ],
        ];

        foreach ($rows as $row) {
            DB::table('website_home_sections')->updateOrInsert(
                ['key' => $row['key']],
                array_merge([
                    'enabled' => true,
                    'sort_by' => 'created_at',
                    'sort_order' => 'desc',
                    'created_at' => $now,
                    'updated_at' => $now,
                ], $row)
            );
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('website_home_sections');
    }
};
