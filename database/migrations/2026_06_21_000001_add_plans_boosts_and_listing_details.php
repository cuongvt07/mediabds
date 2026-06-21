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
            if (! Schema::hasColumn('real_estate_listings', 'deposit_months')) {
                $table->unsignedSmallInteger('deposit_months')->nullable()->after('price_unit');
            }
            if (! Schema::hasColumn('real_estate_listings', 'apartment_name')) {
                $table->string('apartment_name')->nullable()->after('property_type');
            }
            if (! Schema::hasColumn('real_estate_listings', 'apartment_block')) {
                $table->string('apartment_block')->nullable()->after('apartment_name');
            }
            if (! Schema::hasColumn('real_estate_listings', 'boost_tier')) {
                $table->string('boost_tier', 20)->default('normal')->after('vip_tier');
            }
            if (! Schema::hasColumn('real_estate_listings', 'boost_started_at')) {
                $table->timestamp('boost_started_at')->nullable()->after('boost_tier');
            }
            if (! Schema::hasColumn('real_estate_listings', 'boost_expires_at')) {
                $table->timestamp('boost_expires_at')->nullable()->after('boost_started_at');
            }
        });

        Schema::table('users', function (Blueprint $table) {
            if (! Schema::hasColumn('users', 'posting_plan')) {
                $table->string('posting_plan', 20)->default('free')->after('role');
            }
            if (! Schema::hasColumn('users', 'posting_plan_expires_at')) {
                $table->timestamp('posting_plan_expires_at')->nullable()->after('posting_plan');
            }
        });

        DB::table('users')->whereNull('posting_plan')->update(['posting_plan' => 'free']);
        DB::table('real_estate_listings')->whereNull('boost_tier')->update(['boost_tier' => 'normal']);
    }

    public function down(): void
    {
        Schema::table('real_estate_listings', function (Blueprint $table) {
            foreach (['deposit_months', 'apartment_name', 'apartment_block', 'boost_tier', 'boost_started_at', 'boost_expires_at'] as $column) {
                if (Schema::hasColumn('real_estate_listings', $column)) {
                    $table->dropColumn($column);
                }
            }
        });

        Schema::table('users', function (Blueprint $table) {
            foreach (['posting_plan', 'posting_plan_expires_at'] as $column) {
                if (Schema::hasColumn('users', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
