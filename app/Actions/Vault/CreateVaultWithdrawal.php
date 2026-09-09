<?php

namespace App\Actions\Vault;

use App\Jobs\Vault\ProcessVaultWithdrawal;
use App\Models\Vault\VaultAccount;
use App\Models\Vault\VaultBankAccount;
use App\Models\Vault\VaultUser;
use App\Models\Vault\VaultWithdrawalRequest;
use App\Services\Vault\VaultLedgerService;
use DomainException;
use Illuminate\Support\Facades\DB;

class CreateVaultWithdrawal
{
    private const MIN_AMOUNT = 50_000;

    public function __construct(
        private VaultLedgerService $ledger,
    ) {
    }

    public function run(
        VaultUser $user,
        VaultAccount $vault,
        VaultBankAccount $bankAccount,
        int $amount,
        string $idempotencyKey,
    ): VaultWithdrawalRequest {
        if ($vault->vault_user_id !== $user->id || $bankAccount->vault_user_id !== $user->id) {
            throw new DomainException('Két hoặc tài khoản ngân hàng không hợp lệ');
        }

        // Chặn double-submit sớm: nếu key đã tồn tại CỦA CHÍNH USER NÀY, trả về
        // bản ghi cũ thay vì tạo mới. Lọc thêm vault_user_id để tránh lộ dữ
        // liệu của user khác nếu key (dù khó đoán) bị trùng/rò rỉ.
        if ($existing = VaultWithdrawalRequest::where('idempotency_key', $idempotencyKey)
            ->where('vault_user_id', $user->id)
            ->first()) {
            return $existing;
        }

        if ($amount < self::MIN_AMOUNT) {
            throw new DomainException('Số tiền rút tối thiểu là 50.000đ');
        }

        if ($vault->status !== 'active') {
            throw new DomainException('Két này hiện không thể rút tiền (đã đáo hạn hoặc đã đóng)');
        }

        // Chỉ két linh hoạt (flexible) được rút tự do; két kỳ hạn phải đáo hạn.
        // Đây là điều kiện BẮT BUỘC ở BE — FE chỉ hiển thị cảnh báo, không đủ
        // để chặn client tự ý gọi thẳng API.
        if ($vault->type !== 'flexible' && (! $vault->matures_at || $vault->matures_at->isFuture())) {
            throw new DomainException('Két có kỳ hạn chưa đến ngày đáo hạn, không thể rút tại đây');
        }

        return DB::transaction(function () use ($user, $vault, $bankAccount, $amount, $idempotencyKey) {
            // Lock row user trong SUỐT transaction này để serialize toàn bộ yêu
            // cầu rút tiền của CÙNG 1 user — đây là cách chặn triệt để race
            // condition ở hạn mức/ngày (2 request đồng thời không thể cùng đọc
            // "current" cũ rồi cùng pass check nữa, vì request thứ 2 phải đợi
            // request thứ 1 commit/rollback xong mới lock được row).
            VaultUser::where('id', $user->id)->lockForUpdate()->firstOrFail();

            $this->assertWithinDailyLimit($user, $amount);

            // Trừ tiền ngay (lock row két bên trong VaultLedgerService::post), throw nếu không đủ số dư.
            $this->ledger->post(
                vaultAccountId: $vault->id,
                type: 'withdrawal',
                amount: -$amount,
                idempotencyKey: $idempotencyKey,
            );

            $withdrawal = VaultWithdrawalRequest::create([
                'vault_user_id' => $user->id,
                'vault_id' => $vault->id,
                'vault_bank_account_id' => $bankAccount->id,
                'amount' => $amount,
                'status' => 'pending',
                'idempotency_key' => $idempotencyKey,
                'requested_at' => now(),
            ]);

            ProcessVaultWithdrawal::dispatch($withdrawal)->onQueue('vault');

            return $withdrawal;
        });
    }

    /**
     * Đọc tổng đã rút trong ngày TRỰC TIẾP TỪ DB trong transaction đang lock
     * row user (xem run()) — đây là nguồn sự thật duy nhất, không dùng Redis
     * làm bộ đếm chính vì Redis INCR không nằm trong cùng transaction nên
     * không thể rollback nếu bước sau throw. Redis ở đây CHỈ dùng làm cache
     * đọc nhanh cho mục đích hiển thị khác (không có trong action này).
     */
    private function assertWithinDailyLimit(VaultUser $user, int $amount): void
    {
        $limit = $user->dailyWithdrawalLimit();

        $current = (int) VaultWithdrawalRequest::where('vault_user_id', $user->id)
            ->whereIn('status', ['pending', 'processing', 'success'])
            ->whereDate('requested_at', now()->toDateString())
            ->lockForUpdate()
            ->sum('amount');

        if ($current + $amount > $limit) {
            throw new DomainException('Vượt hạn mức rút tiền trong ngày');
        }
    }
}
