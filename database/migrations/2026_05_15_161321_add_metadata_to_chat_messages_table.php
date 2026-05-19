<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('chat_messages', function (Blueprint $table) {
            $table->json('metadata')->nullable()->after('content');
            // metadata schema:
            // {
            //   "intent": "search_listings",
            //   "confidence": 0.92,
            //   "mode": "FAST"|"SMART",
            //   "model": "gpt-4o-mini",
            //   "tool_calls": [{"name":"search_listings","args":{...}}, ...]
            // }
        });
    }

    public function down(): void
    {
        Schema::table('chat_messages', function (Blueprint $table) {
            $table->dropColumn('metadata');
        });
    }
};
