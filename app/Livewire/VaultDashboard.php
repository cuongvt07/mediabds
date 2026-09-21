<?php

namespace App\Livewire;

use App\Models\Vault\VaultAccount;
use App\Models\Vault\VaultDepositRequest;
use App\Models\Vault\VaultUser;
use App\Models\Vault\VaultWithdrawalRequest;
use App\Services\Vault\VaultOtpService;
use Livewire\Component;
use Livewire\WithPagination;

/**
 * Tổng quan module Vault cho admin — HOÀN TOÀN TÁCH BIỆT guard 'vault' (đây
 * là trang admin, guard 'web' + middleware ['auth','admin'] ở routes/web.php).
 * Chỉ đọc dữ liệu (KPI + danh sách user) để theo dõi — KHÔNG có hành động
 * ghi nào ở đây. Lịch sử nạp/rút tiền và cấu hình SePay đã tách ra route
 * riêng (VaultDeposits/VaultWithdrawals/VaultSepaySettings) — mỗi mục 1
 * trang độc lập trong sidebar, không lồng sub-tab như trước.
 */
class VaultDashboard extends Component
{
    use WithPagination;

    public function render()
    {
        return view('livewire.vault-dashboard', [
            'users' => VaultUser::withCount(['vaults', 'bankAccounts'])
                ->orderByDesc('created_at')
                ->paginate(15),
            'stats' => [
                'usersCount' => VaultUser::count(),
                'totalPrincipal' => (int) VaultAccount::sum('principal_amount'),
                'totalInterest' => (int) VaultAccount::sum('accrued_interest_amount'),
                'pendingWithdrawals' => VaultWithdrawalRequest::whereIn('status', ['pending_otp', 'pending', 'processing'])->count(),
                'pendingDeposits' => VaultDepositRequest::where('status', 'pending_payment')->count(),
            ],
            'otpEnabled' => app(VaultOtpService::class)->isEnabled(),
        ])->layout('components.layouts.vault-cms', [
            'title' => 'Tổng quan',
        ]);
    }
}
