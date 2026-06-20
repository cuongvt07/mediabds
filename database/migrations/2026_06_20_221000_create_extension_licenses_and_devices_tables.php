<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('extension_licenses', function (Blueprint $table) {
            $table->id();
            $table->string('label');
            $table->string('key_hash', 64)->unique();
            $table->string('key_hint', 24);
            $table->boolean('active')->default(true);
            $table->unsignedInteger('max_devices')->default(1);
            $table->timestamp('expires_at')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('extension_devices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('extension_license_id')->constrained()->cascadeOnDelete();
            $table->string('device_hash', 64);
            $table->string('device_name')->nullable();
            $table->string('token_hash', 64)->nullable()->unique();
            $table->timestamp('last_seen_at')->nullable();
            $table->timestamp('revoked_at')->nullable();
            $table->timestamps();
            $table->unique(['extension_license_id', 'device_hash']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('extension_devices');
        Schema::dropIfExists('extension_licenses');
    }
};
