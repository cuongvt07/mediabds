<?php

namespace App\Livewire;

use App\Models\Vault\VaultDepositRequest;
use App\Models\Vault\VaultLedgerEntry;
use App\Models\Vault\VaultUser;
use App\Models\Vault\VaultWithdrawalRequest;
use Livewire\Component;
use Livewire\WithPagination;

/**
 * Chi tiết 1 user Vault — két, số dư, lịch sử ledger đầy đủ (nạp/rút/lãi).
 * Chỉ đọc, không có hành động ghi (giống VaultDashboard). Nhận $userId từ
 * route param (int), tự findOrFail lại — không tin Model binding trực tiếp
 * từ URL nếu có thể bị thao túng (ở đây route model binding của Laravel đã
 * tự an toàn vì chỉ query theo primary key, nhưng vẫn giữ pattern nhất quán
 * với toàn bộ module: luôn tự truy vấn lại theo ID).
 */
class VaultUserDetail extends Component
{
    use WithPagination;

    public int $userId;

    public function mount(int $userId): void
    {
        $this->userId = $userId;
    }

    public function render()
    {
        $user = VaultUser::with(['vaults', 'bankAccounts'])->findOrFail($this->userId);

        $ledger = VaultLedgerEntry::where('vault_user_id', $user->id)
            ->orderByDesc('created_at')
            ->paginate(25);

        return view('livewire.vault-user-detail', [
            'vaultUser' => $user,
            'ledger' => $ledger,
            'depositsCount' => VaultDepositRequest::where('vault_user_id', $user->id)->count(),
            'withdrawalsCount' => VaultWithdrawalRequest::where('vault_user_id', $user->id)->count(),
            'totalPrincipal' => (int) $user->vaults->sum('principal_amount'),
            'totalInterest' => (int) $user->vaults->sum('accrued_interest_amount'),
        ])->layout('components.layouts.vault-cms', [
            'title' => 'Chi tiết: ' . $user->name,
        ]);
    }
}
