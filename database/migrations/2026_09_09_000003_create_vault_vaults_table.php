<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * "Két" tích lũy — mỗi user có thể mở nhiều két (Linh Hoạt, Vàng 3 tháng...).
 * Đổi tên bảng thành `vault_vaults` để tránh trùng với danh từ chung "vault"
 * (tên module) — đây là danh sách các GÓI TÀI KHOẢN TÍCH LŨY cụ thể.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('vault_vaults', function (Blueprint $table) {
            $table->id();
            $table->foreignId('vault_user_id')->constrained('vault_users')->cascadeOnDelete();
            $table->string('type'); // flexible|fixed_term
            $table->string('name'); // vd "Két Linh Hoạt", "Két Vàng 3 Tháng"
            $table->decimal('interest_rate_yearly', 5, 2); // vd 6.80, 8.50 (%/năm)
            $table->unsignedBigInteger('principal_amount')->default(0); // số dư gốc đã cất giữ (đồng)
            $table->unsignedBigInteger('accrued_interest_amount')->default(0); // lãi lũy kế đã cộng vào gốc (đồng)
            $table->unsignedSmallInteger('term_days')->nullable(); // null nếu linh hoạt, vd 90 nếu kỳ hạn 3 tháng
            $table->timestamp('matures_at')->nullable(); // ngày đáo hạn (null nếu linh hoạt)
            $table->boolean('auto_compound')->default(true); // tự động cộng dồn lãi kép
            $table->string('status')->default('active'); // active|matured|closed
            $table->timestamps();

            $table->index(['vault_user_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('vault_vaults');
    }
};
