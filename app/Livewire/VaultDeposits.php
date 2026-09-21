<?php

namespace App\Livewire;

use App\Models\SepayWebhookLog;
use App\Models\Vault\VaultDepositRequest;
use Livewire\Component;
use Livewire\WithPagination;

/**
 * Lịch sử nạp tiền module Vault — chỉ đọc, không có hành động ghi. Có thể
 * xem lại log webhook SePay (mọi lần gửi, kể cả bị từ chối) gắn với từng
 * lệnh nạp qua viewLogs()/closeLogs() — xem SePayWebhookController::logAttempt().
 */
class VaultDeposits extends Component
{
    use WithPagination;

    public ?int $viewingLogsForDepositId = null;

    public function viewLogs(int $depositId): void
    {
        // Tự truy vấn lại theo ID (không tin property khác từ client) —
        // cùng nguyên tắc bảo mật với VaultEkycReview::approve().
        $this->viewingLogsForDepositId = VaultDepositRequest::findOrFail($depositId)->id;
    }

    public function closeLogs(): void
    {
        $this->viewingLogsForDepositId = null;
    }

    public function render()
    {
        $logs = $this->viewingLogsForDepositId
            ? SepayWebhookLog::where('vault_deposit_request_id', $this->viewingLogsForDepositId)
                ->orderBy('created_at')
                ->get()
            : null;

        return view('livewire.vault-deposits', [
            'deposits' => VaultDepositRequest::with('vaultUser:id,name,phone,vault_code')
                ->withCount('webhookLogs')
                ->orderByDesc('created_at')
                ->paginate(20),
            'logs' => $logs,
        ])->layout('components.layouts.vault-cms', [
            'title' => 'Lịch sử nạp tiền',
        ]);
    }
}
