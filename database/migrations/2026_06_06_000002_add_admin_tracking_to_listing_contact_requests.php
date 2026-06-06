<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('listing_contact_requests')) {
            return;
        }

        Schema::table('listing_contact_requests', function (Blueprint $table) {
            if (! Schema::hasColumn('listing_contact_requests', 'status')) {
                $table->string('status')->default('new')->after('source');
            }
            if (! Schema::hasColumn('listing_contact_requests', 'admin_note')) {
                $table->text('admin_note')->nullable()->after('status');
            }
            if (! Schema::hasColumn('listing_contact_requests', 'handled_by')) {
                $table->foreignId('handled_by')->nullable()->after('admin_note')->constrained('users')->nullOnDelete();
            }
            if (! Schema::hasColumn('listing_contact_requests', 'handled_at')) {
                $table->timestamp('handled_at')->nullable()->after('handled_by');
            }
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('listing_contact_requests')) {
            return;
        }

        Schema::table('listing_contact_requests', function (Blueprint $table) {
            if (Schema::hasColumn('listing_contact_requests', 'handled_by')) {
                $table->dropConstrainedForeignId('handled_by');
            }
            foreach (['status', 'admin_note', 'handled_at'] as $column) {
                if (Schema::hasColumn('listing_contact_requests', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
