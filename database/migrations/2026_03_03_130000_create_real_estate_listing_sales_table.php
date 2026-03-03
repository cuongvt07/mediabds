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
        Schema::create('real_estate_listing_sales', function (Blueprint $table) {
            $table->id();
            $table->foreignId('listing_id')->constrained('real_estate_listings')->cascadeOnDelete();
            $table->foreignId('sold_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('project_name');
            $table->decimal('actual_price', 18, 2);
            $table->decimal('revenue_percent', 6, 2);
            $table->decimal('revenue_amount', 18, 2);
            $table->decimal('bonus_amount', 18, 2)->default(0);
            $table->decimal('net_received_amount', 18, 2);
            $table->timestamp('sold_at')->nullable();
            $table->timestamps();

            $table->unique('listing_id');
            $table->index('sold_by_user_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('real_estate_listing_sales');
    }
};
