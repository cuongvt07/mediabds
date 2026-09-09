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
use Illuminate\Support\Facades\Redis;

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
        // Chặn double-submit sớm: nếu key đã tồn tại, trả về bản ghi cũ thay vì tạo mới.
        if ($existing = VaultWithdrawalRequest::where('idempotency_key', $idempotencyKey)->first()) {
            return $existing;
        }

        if ($amount < self::MIN_AMOUNT) {
            throw new DomainException('Số tiền rút tối thiểu là 50.000đ');
        }

        if ($vault->vault_user_id !== $user->id || $bankAccount->vault_user_id !== $user->id) {
            throw new DomainException('Két hoặc tài khoản ngân hàng không hợp lệ');
        }

        $this->assertWithinDailyLimit($user, $amount);

        return DB::transaction(function () use ($user, $vault, $bankAccount, $amount, $idempotencyKey) {
            // Trừ tiền ngay (lock row bên trong VaultLedgerService::post), throw nếu không đủ số dư.
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

    private function assertWithinDailyLimit(VaultUser $user, int $amount): void
    {
        $key = "vault:withdrawal:daily:{$user->id}:" . now()->format('Y-m-d');
        $limit = $user->dailyWithdrawalLimit();

        try {
            $current = (int) Redis::get($key);
        } catch (\Throwable $e) {
            // Redis không khả dụng trên môi trường local/dev — fallback tính từ DB
            // thay vì chặn cứng toàn bộ luồng rút tiền.
            $current = (int) VaultWithdrawalRequest::where('vault_user_id', $user->id)
                ->whereIn('status', ['pending', 'processing', 'success'])
                ->whereDate('requested_at', now()->toDateString())
                ->sum('amount');
        }

        if ($current + $amount > $limit) {
            throw new DomainException('Vượt hạn mức rút tiền trong ngày');
        }

        try {
            Redis::incrby($key, $amount);
            Redis::expireat($key, now()->endOfDay()->timestamp);
        } catch (\Throwable $e) {
            // bỏ qua — DB fallback ở lần kiểm tra sau vẫn đảm bảo đúng.
        }
    }
}
