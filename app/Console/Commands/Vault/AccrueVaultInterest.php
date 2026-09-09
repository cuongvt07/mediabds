<?php

namespace App\Console\Commands\Vault;

use App\Services\Vault\VaultInterestAccrualService;
use Illuminate\Console\Command;

/**
 * Cộng lãi hằng ngày vào từng két đang active — TỰ BÙ mọi ngày còn thiếu cho
 * từng két (xem VaultInterestAccrualService). Idempotent, chạy lại bao nhiêu
 * lần cũng không cộng trùng.
 *
 * Chạy tay: php artisan vault:accrue-interest
 * Lịch (chỉ có tác dụng nếu server chạy `schedule:run` qua cron thật):
 *   routes/console.php -> Schedule::command(...)->dailyAt('00:00')
 *
 * LƯU Ý: trên hosting KHÔNG cấu hình được cron thật, có thể bỏ qua command
 * này — cơ chế chính là VaultCronController::accrueCheck(), được FE tự gọi
 * mỗi khi user mở Dashboard Vault (xem routes/vault.php).
 */
class AccrueVaultInterest extends Command
{
    protected $signature = 'vault:accrue-interest';

    protected $description = 'Cộng lãi cho các két Vault đang active (tự bù mọi ngày còn thiếu)';

    public function handle(VaultInterestAccrualService $service): int
    {
        $result = $service->accrueAllDue();

        $this->info("Xong: {$result['vaultsProcessed']} két được xử lý, tổng {$result['daysAccrued']} lượt ngày được cộng lãi.");

        return self::SUCCESS;
    }
}
