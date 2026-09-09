<?php

namespace App\Jobs\Vault;

use App\Models\Vault\VaultWithdrawalRequest;
use App\Services\Vault\VaultLedgerService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Str;

/**
 * MÔ PHỎNG cổng ngân hàng — KHÔNG gọi gateway thật, KHÔNG chuyển tiền thật.
 * Tiền đã bị trừ khỏi két ngay khi tạo yêu cầu (CreateVaultWithdrawal); job này
 * chỉ giả lập độ trễ xử lý rồi tự động đánh dấu thành công (delay() để không
 * chặn request, xử lý thật diễn ra trong hàng đợi `vault`).
 *
 * Khi có cổng thanh toán thật, thay nội dung handle() bằng lệnh gọi gateway
 * thật + verify chữ ký webhook, giữ nguyên toàn bộ phần còn lại (ledger/status).
 */
class ProcessVaultWithdrawal implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    public function __construct(public VaultWithdrawalRequest $withdrawal)
    {
        // Giả lập độ trễ xử lý ngân hàng (3-8 giây) trước khi job thật sự chạy.
        $this->delay(now()->addSeconds(random_int(3, 8)));
    }

    public function handle(VaultLedgerService $ledger): void
    {
        $this->withdrawal->refresh();

        if ($this->withdrawal->status !== 'pending') {
            return; // đã xử lý (tránh chạy trùng khi job retry).
        }

        $this->withdrawal->update([
            'status' => 'processing',
            'gateway_ref' => 'SIM-' . Str::upper(Str::random(10)),
        ]);

        // Mô phỏng tỷ lệ thành công cao (~97%) để có thể test cả luồng lỗi.
        $succeeded = random_int(1, 100) <= 97;

        if ($succeeded) {
            $this->withdrawal->update([
                'status' => 'success',
                'completed_at' => now(),
            ]);

            return;
        }

        $this->withdrawal->update([
            'status' => 'failed',
            'failure_reason' => 'Ngân hàng từ chối giao dịch (mô phỏng)',
        ]);

        // Hoàn tiền lại két, dùng idempotency key riêng nên chạy lại job không hoàn 2 lần.
        $ledger->post(
            vaultAccountId: $this->withdrawal->vault_id,
            type: 'withdrawal_refund',
            amount: $this->withdrawal->amount,
            idempotencyKey: $this->withdrawal->idempotency_key . ':refund',
            referenceType: VaultWithdrawalRequest::class,
            referenceId: $this->withdrawal->id,
        );
    }

    public function failed(\Throwable $e): void
    {
        $this->withdrawal->refresh();
        if ($this->withdrawal->status === 'success') {
            return;
        }

        $this->withdrawal->update([
            'status' => 'failed',
            'failure_reason' => $e->getMessage(),
        ]);

        app(VaultLedgerService::class)->post(
            vaultAccountId: $this->withdrawal->vault_id,
            type: 'withdrawal_refund',
            amount: $this->withdrawal->amount,
            idempotencyKey: $this->withdrawal->idempotency_key . ':refund',
            referenceType: VaultWithdrawalRequest::class,
            referenceId: $this->withdrawal->id,
        );
    }
}
