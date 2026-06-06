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
            if (! Schema::hasColumn('real_estate_listings', 'slug')) {
                $table->string('slug')->nullable()->unique()->after('code');
            }
            if (! Schema::hasColumn('real_estate_listings', 'category_id')) {
                $table->string('category_id')->nullable()->after('property_type');
            }
            if (! Schema::hasColumn('real_estate_listings', 'status')) {
                $table->string('status')->default('active')->after('is_sold');
            }
            if (! Schema::hasColumn('real_estate_listings', 'vip_tier')) {
                $table->string('vip_tier')->default('normal')->after('status');
            }
            if (! Schema::hasColumn('real_estate_listings', 'furnish')) {
                $table->string('furnish')->nullable()->after('direction');
            }
            if (! Schema::hasColumn('real_estate_listings', 'lat')) {
                $table->decimal('lat', 10, 7)->nullable()->after('address');
            }
            if (! Schema::hasColumn('real_estate_listings', 'lng')) {
                $table->decimal('lng', 10, 7)->nullable()->after('lat');
            }
            if (! Schema::hasColumn('real_estate_listings', 'amenities')) {
                $table->json('amenities')->nullable()->after('images');
            }
            if (! Schema::hasColumn('real_estate_listings', 'tags')) {
                $table->json('tags')->nullable()->after('amenities');
            }
            if (! Schema::hasColumn('real_estate_listings', 'view_count')) {
                $table->unsignedBigInteger('view_count')->default(0)->after('tags');
            }
            if (! Schema::hasColumn('real_estate_listings', 'published_at')) {
                $table->timestamp('published_at')->nullable()->after('view_count');
            }
            if (! Schema::hasColumn('real_estate_listings', 'expires_at')) {
                $table->timestamp('expires_at')->nullable()->after('published_at');
            }
            if (! Schema::hasColumn('real_estate_listings', 'contact_name')) {
                $table->string('contact_name')->nullable()->after('contact_phone');
            }
            if (! Schema::hasColumn('real_estate_listings', 'contact_zalo')) {
                $table->string('contact_zalo')->nullable()->after('contact_name');
            }
        });

        Schema::create('listing_categories', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('transaction_type')->default('both');
            $table->string('property_type')->nullable();
            $table->string('icon')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });

        Schema::create('blog_posts', function (Blueprint $table) {
            $table->id();
            $table->string('slug')->unique();
            $table->string('title');
            $table->text('excerpt')->nullable();
            $table->longText('content');
            $table->string('cover_image')->nullable();
            $table->string('author_name')->nullable();
            $table->string('category_tag')->nullable();
            $table->json('tags')->nullable();
            $table->unsignedSmallInteger('reading_minutes')->default(1);
            $table->string('status')->default('published');
            $table->timestamp('published_at')->nullable();
            $table->timestamps();
        });

        Schema::create('listing_favorites', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('listing_id')->constrained('real_estate_listings')->cascadeOnDelete();
            $table->timestamps();
            $table->unique(['user_id', 'listing_id']);
        });

        Schema::create('saved_searches', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('label');
            $table->json('params');
            $table->timestamps();
        });

        Schema::create('listing_contact_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('listing_id')->nullable()->constrained('real_estate_listings')->nullOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('name');
            $table->string('phone');
            $table->text('message')->nullable();
            $table->string('source')->default('website');
            $table->timestamps();
        });

        Schema::create('listing_view_events', function (Blueprint $table) {
            $table->id();
            $table->foreignId('listing_id')->constrained('real_estate_listings')->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('ip_hash')->nullable();
            $table->string('user_agent')->nullable();
            $table->timestamps();
        });

        $now = now();
        DB::table('listing_categories')->upsert([
            ['id' => 'c-apt-rent', 'name' => 'Cho thuê căn hộ', 'slug' => 'cho-thue-can-ho', 'transaction_type' => 'rent', 'property_type' => 'apartment', 'sort_order' => 10, 'created_at' => $now, 'updated_at' => $now],
            ['id' => 'c-room-rent', 'name' => 'Cho thuê phòng trọ', 'slug' => 'cho-thue-phong-tro', 'transaction_type' => 'rent', 'property_type' => 'room', 'sort_order' => 20, 'created_at' => $now, 'updated_at' => $now],
            ['id' => 'c-house-rent', 'name' => 'Cho thuê nhà nguyên căn', 'slug' => 'cho-thue-nha-nguyen-can', 'transaction_type' => 'rent', 'property_type' => 'house', 'sort_order' => 30, 'created_at' => $now, 'updated_at' => $now],
            ['id' => 'c-office-rent', 'name' => 'Cho thuê văn phòng', 'slug' => 'cho-thue-van-phong', 'transaction_type' => 'rent', 'property_type' => 'office', 'sort_order' => 40, 'created_at' => $now, 'updated_at' => $now],
            ['id' => 'c-shared-rent', 'name' => 'Ở ghép', 'slug' => 'o-ghep', 'transaction_type' => 'rent', 'property_type' => 'shared', 'sort_order' => 50, 'created_at' => $now, 'updated_at' => $now],
            ['id' => 'c-apt-sale', 'name' => 'Bán căn hộ chung cư', 'slug' => 'ban-can-ho', 'transaction_type' => 'sale', 'property_type' => 'apartment', 'sort_order' => 60, 'created_at' => $now, 'updated_at' => $now],
            ['id' => 'c-house-sale', 'name' => 'Bán nhà riêng', 'slug' => 'ban-nha-rieng', 'transaction_type' => 'sale', 'property_type' => 'house', 'sort_order' => 70, 'created_at' => $now, 'updated_at' => $now],
            ['id' => 'c-land-sale', 'name' => 'Bán đất', 'slug' => 'ban-dat', 'transaction_type' => 'sale', 'property_type' => 'land', 'sort_order' => 80, 'created_at' => $now, 'updated_at' => $now],
        ], ['id'], ['name', 'slug', 'transaction_type', 'property_type', 'sort_order', 'updated_at']);
    }

    public function down(): void
    {
        Schema::dropIfExists('listing_view_events');
        Schema::dropIfExists('listing_contact_requests');
        Schema::dropIfExists('saved_searches');
        Schema::dropIfExists('listing_favorites');
        Schema::dropIfExists('blog_posts');
        Schema::dropIfExists('listing_categories');

        Schema::table('real_estate_listings', function (Blueprint $table) {
            foreach ([
                'slug', 'category_id', 'status', 'vip_tier', 'furnish', 'lat', 'lng',
                'amenities', 'tags', 'view_count', 'published_at', 'expires_at',
                'contact_name', 'contact_zalo',
            ] as $column) {
                if (Schema::hasColumn('real_estate_listings', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
