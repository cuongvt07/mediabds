<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('listing_embeddings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('listing_id')->constrained('real_estate_listings')->cascadeOnDelete();
            $table->string('content_hash', 64); // sha256 của text được embed — phát hiện thay đổi
            $table->string('model', 64);
            $table->mediumText('embedding'); // JSON-encoded float[] (1536 chiều ~12KB)
            $table->timestamps();

            $table->unique('listing_id');
            $table->index('content_hash');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('listing_embeddings');
    }
};
