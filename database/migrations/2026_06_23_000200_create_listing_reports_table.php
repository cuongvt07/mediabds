<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('listing_reports', function (Blueprint $table) {
            $table->id();

            // What is being reported.
            $table->string('target_type', 20)->default('listing'); // listing | user
            $table->foreignId('listing_id')->nullable()->constrained('real_estate_listings')->nullOnDelete();
            $table->foreignId('reported_user_id')->nullable()->constrained('users')->nullOnDelete();

            // Who reported (logged-in user and/or guest contact).
            $table->foreignId('reporter_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('reporter_name')->nullable();
            $table->string('reporter_phone', 40)->nullable();

            // tin_ao | gia_ao | ngon_tu | anh_vi_pham | sai_thong_tin | khac
            $table->string('reason', 40);
            $table->text('detail')->nullable();

            // pending | resolved_removed (gỡ) | resolved_kept (giữ)
            $table->string('status', 20)->default('pending');
            $table->text('admin_reason')->nullable();
            $table->foreignId('handled_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('handled_at')->nullable();

            $table->timestamps();

            $table->index(['status', 'target_type']);
            $table->index('listing_id');
        });

        if (! Schema::hasColumn('real_estate_listings', 'rejection_reason')) {
            Schema::table('real_estate_listings', function (Blueprint $table) {
                $table->text('rejection_reason')->nullable()->after('status');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('listing_reports');

        if (Schema::hasColumn('real_estate_listings', 'rejection_reason')) {
            Schema::table('real_estate_listings', function (Blueprint $table) {
                $table->dropColumn('rejection_reason');
            });
        }
    }
};
