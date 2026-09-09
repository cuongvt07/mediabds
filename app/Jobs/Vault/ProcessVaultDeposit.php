<?php

namespace App\Jobs\Vault;

use App\Models\Vault\VaultDepositRequest;
use App\Services\Vault\VaultLedgerService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

/**
 * MÔ PHỎNG nạp tiền — không gọi cổng thanh toán thật. Tiền chỉ được cộng vào
 * két SAU KHI job này chạy (khác với rút tiền: rút thì trừ ngay lúc tạo yêu
 * cầu). Việc này mô phỏng đúng thực tế "nạp tiền cần ngân hàng xác nhận trước".
 */
class ProcessVaultDeposit implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    public function __construct(public VaultDepositRequest $deposit)
    {
        $this->delay(now()->addSeconds(random_int(2, 5)));
    }

    public function handle(VaultLedgerService $ledger): void
    {
        $this->deposit->refresh();

        if ($this->deposit->status !== 'pending') {
            return;
        }

        $ledger->post(
            vaultAccountId: $this->deposit->vault_id,
            type: 'deposit',
            amount: $this->deposit->amount,
            idempotencyKey: $this->deposit->idempotency_key,
            referenceType: VaultDepositRequest::class,
            referenceId: $this->deposit->id,
        );

        $this->deposit->update([
            'status' => 'success',
            'completed_at' => now(),
        ]);
    }

    public function failed(\Throwable $e): void
    {
        $this->deposit->refresh();
        if ($this->deposit->status === 'success') {
            return;
        }

        $this->deposit->update(['status' => 'failed']);
    }
}
