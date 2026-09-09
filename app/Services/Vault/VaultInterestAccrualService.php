<?php

namespace App\Services\Vault;

use App\Models\Vault\VaultAccount;
use App\Models\Vault\VaultLedgerEntry;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;

/**
 * Cộng lãi hằng ngày cho két Vault — BÙ MỌI NGÀY CÒN THIẾU cho từng két
 * riêng biệt (không phải chỉ cộng cho "hôm nay"). Idempotent theo
 * "interest:{vault_id}:{Y-m-d}" nên gọi lại bao nhiêu lần cũng không cộng
 * trùng.
 *
 * Được gọi từ 2 nơi:
 * 1. Command `vault:accrue-interest` (nếu server có cron thật).
 * 2. VaultCronController::accrueCheck() — FE gọi mỗi khi user mở Dashboard,
 *    dùng cho hosting KHÔNG chạy được `schedule:run` (xem ghi chú ở đó).
 */
class VaultInterestAccrualService
{
    private const MAX_BACKFILL_DAYS = 60; // trần an toàn — két "chết" lâu ngày không bị vòng lặp quá lớn khi có người mở lại

    public function __construct(private VaultLedgerService $ledger)
    {
    }

    /**
     * Bù lãi cho TẤT CẢ két active, mỗi két tự tính từ ngày cộng gần nhất của
     * chính nó tới hôm nay (không vượt hôm nay — không cộng lãi "tương lai").
     *
     * @return array{vaultsProcessed:int, daysAccrued:int}
     */
    public function accrueAllDue(): array
    {
        $vaults = VaultAccount::where('status', 'active')->get();
        $totalDays = 0;

        foreach ($vaults as $vault) {
            $totalDays += $this->accrueForVault($vault);
        }

        return ['vaultsProcessed' => $vaults->count(), 'daysAccrued' => $totalDays];
    }

    /** Bù lãi cho 1 két, trả về số ngày đã cộng (0 nếu đã cập nhật đủ tới hôm nay). */
    public function accrueForVault(VaultAccount $vault): int
    {
        $today = now()->toDateString();
        $lastDate = $this->lastAccruedDate($vault->id);

        // Chưa từng cộng lãi lần nào -> bắt đầu từ hôm sau ngày tạo két (ngày
        // tạo không được tính lãi vì chưa đủ 1 ngày nắm giữ).
        $startDate = $lastDate
            ? Carbon::parse($lastDate)->addDay()
            : $vault->created_at->copy()->addDay()->startOfDay();

        $cursor = $startDate->copy();
        $endDate = Carbon::parse($today);

        // Trần an toàn: nếu két bị "bỏ quên" quá lâu, chỉ bù tối đa N ngày gần
        // nhất — tránh vòng lặp hàng trăm/nghìn lần nếu dữ liệu bất thường.
        if ($cursor->lt($endDate->copy()->subDays(self::MAX_BACKFILL_DAYS))) {
            $maxDays = self::MAX_BACKFILL_DAYS;
            Log::warning("VaultInterestAccrualService: vault #{$vault->id} thiếu quá {$maxDays} ngày, chỉ bù {$maxDays} ngày gần nhất.");
            $cursor = $endDate->copy()->subDays(self::MAX_BACKFILL_DAYS);
        }

        $daysAccrued = 0;

        while ($cursor->lte($endDate)) {
            $date = $cursor->toDateString();
            $interest = $vault->estimatedDailyInterest();

            if ($interest > 0) {
                $this->ledger->post(
                    vaultAccountId: $vault->id,
                    type: 'interest',
                    amount: $interest,
                    idempotencyKey: "interest:{$vault->id}:{$date}",
                    referenceType: VaultAccount::class,
                    referenceId: $vault->id,
                    meta: ['date' => $date, 'rate_yearly' => (float) $vault->interest_rate_yearly],
                );
                // Nạp lại số dư mới nhất — lãi ngày sau tính trên gốc đã gồm
                // lãi ngày trước (đúng bản chất "lãi kép" mà UI quảng cáo).
                $vault->refresh();
            }

            $daysAccrued++;
            $cursor->addDay();
        }

        return $daysAccrued;
    }

    private function lastAccruedDate(int $vaultId): ?string
    {
        $last = VaultLedgerEntry::where('vault_id', $vaultId)
            ->where('type', 'interest')
            ->orderByDesc('created_at')
            ->first();

        return $last?->meta['date'] ?? null;
    }
}
