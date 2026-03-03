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
        Schema::table('users', function (Blueprint $table) {
            $table->string('invite_code')->nullable()->unique()->after('phone');
            $table->foreignId('invited_by_user_id')
                ->nullable()
                ->after('invite_code')
                ->constrained('users')
                ->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['invited_by_user_id']);
            $table->dropColumn(['invite_code', 'invited_by_user_id']);
        });
    }
};
