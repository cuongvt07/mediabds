<?php

namespace App\Http\Controllers\Vault;

use App\Services\Vault\VaultInterestAccrualService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

/**
 * "Cron giả lập qua FE" — thay cho php artisan schedule:run trên hosting
 * không cấu hình được cron thật. FE gọi endpoint này mỗi khi user mở Dashboard
 * Vault; BE tự kiểm tra và bù MỌI ngày còn thiếu cho từng két (xem
 * VaultInterestAccrualService), không chỉ "hôm nay".
 *
 * An toàn khi nhiều user cùng gọi liên tục: idempotency key theo
 * "interest:{vault_id}:{date}" ở tầng ledger nên gọi trùng không cộng 2 lần;
 * thêm throttle 1 lần/phút TOÀN HỆ THỐNG (không phải theo user) để tránh hàng
 * nghìn request đồng thời cùng quét toàn bộ két mỗi lần ai đó mở app.
 */
class VaultCronController extends VaultBaseController
{
    private const THROTTLE_KEY = 'vault:cron:accrue-check:last-run';
    private const THROTTLE_SECONDS = 60;

    public function accrueCheck(Request $request, VaultInterestAccrualService $service)
    {
        // Throttle toàn cục qua cache (không phụ thuộc Redis — dùng cache
        // driver mặc định của app, an toàn trên hosting chỉ có 'file'/'database').
        if (Cache::has(self::THROTTLE_KEY)) {
            return $this->ok(['skipped' => true, 'reason' => 'throttled']);
        }
        Cache::put(self::THROTTLE_KEY, true, self::THROTTLE_SECONDS);

        $result = $service->accrueAllDue();

        return $this->ok([
            'skipped' => false,
            'vaultsProcessed' => $result['vaultsProcessed'],
            'daysAccrued' => $result['daysAccrued'],
        ]);
    }
}
