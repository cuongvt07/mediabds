<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('chat_feedback', function (Blueprint $table) {
            $table->id();
            $table->foreignId('chat_message_id')->constrained('chat_messages')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->tinyInteger('rating'); // 1 = 👍 đúng, -1 = 👎 sai
            $table->string('error_category', 32)->nullable(); // wrong_tool|wrong_filter|wrong_format|missed_data|fabricated|other
            $table->text('note')->nullable();
            $table->string('intent_at_time')->nullable();   // intent classifier output
            $table->float('confidence_at_time')->nullable();
            $table->json('tool_calls_meta')->nullable();    // các tool đã được gọi cho message này
            $table->timestamps();

            $table->unique(['chat_message_id', 'user_id']); // mỗi user vote 1 lần / message
            $table->index('rating');
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('chat_feedback');
    }
};
