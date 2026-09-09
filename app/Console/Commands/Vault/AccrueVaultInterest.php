<?php

namespace App\Console\Commands\Vault;

use App\Models\Vault\VaultAccount;
use App\Services\Vault\VaultLedgerService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

/**
 * Cộng lãi hằng ngày vào từng két đang active. Idempotent theo ngày: key
 * "interest:{vault_id}:{Y-m-d}" nên chạy lại nhiều lần trong 1 ngày (retry
 * cron, chạy tay để test) không cộng lãi 2 lần.
 *
 * Chạy: php artisan vault:accrue-interest
 * Lịch: đăng ký trong routes/console.php (Schedule::command(...)->dailyAt('00:00')).
 */
class AccrueVaultInterest extends Command
{
    protected $signature = 'vault:accrue-interest {--date= : Ngày tính lãi (Y-m-d), mặc định hôm nay}';

    protected $description = 'Cộng lãi hằng ngày cho các két Vault đang active';

    public function handle(VaultLedgerService $ledger): int
    {
        $date = $this->option('date') ?: now()->toDateString();

        $vaults = VaultAccount::where('status', 'active')->get();
        $this->info("Tính lãi ngày {$date} cho {$vaults->count()} két...");

        $accrued = 0;
        $skipped = 0;

        foreach ($vaults as $vault) {
            $interest = $vault->estimatedDailyInterest();

            if ($interest <= 0) {
                $skipped++;
                continue;
            }

            try {
                $ledger->post(
                    vaultAccountId: $vault->id,
                    type: 'interest',
                    amount: $interest,
                    idempotencyKey: "interest:{$vault->id}:{$date}",
                    referenceType: VaultAccount::class,
                    referenceId: $vault->id,
                    meta: ['date' => $date, 'rate_yearly' => (float) $vault->interest_rate_yearly],
                );
                $accrued++;
            } catch (\Throwable $e) {
                Log::error("AccrueVaultInterest lỗi ở vault #{$vault->id}: " . $e->getMessage());
                $this->error("Lỗi vault #{$vault->id}: " . $e->getMessage());
            }
        }

        $this->info("Xong: {$accrued} két được cộng lãi, {$skipped} két bỏ qua (lãi = 0).");

        return self::SUCCESS;
    }
}
