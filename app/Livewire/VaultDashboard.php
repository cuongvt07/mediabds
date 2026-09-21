<?php

namespace App\Livewire;

use App\Models\SepaySetting;
use App\Models\Vault\VaultDepositRequest;
use App\Models\Vault\VaultUser;
use App\Models\Vault\VaultWithdrawalRequest;
use App\Services\Vault\VaultOtpService;
use Livewire\Component;
use Livewire\WithPagination;

/**
 * Dashboard tổng quan module Vault cho admin — HOÀN TOÀN TÁCH BIỆT guard
 * 'vault' (đây là trang admin, guard 'web' + middleware ['auth','admin'] ở
 * routes/web.php). Chỉ đọc dữ liệu để đối soát/theo dõi + bật/tắt/xoay
 * secret key SePay — KHÔNG có hành động nào chỉnh sửa số dư/giao dịch trực
 * tiếp từ đây (mọi thay đổi số dư PHẢI đi qua VaultLedgerService, không có
 * lối tắt nào ở CMS để tránh lệch sổ sách).
 *
 * BẢO MẬT: giống VaultEkycReview — mọi hành động ghi (rotateSepaySecret,
 * toggleSepayEnabled) không nhận Model trực tiếp từ Livewire property, tự
 * đọc lại SepaySetting::current() tại thời điểm xử lý.
 */
class VaultDashboard extends Component
{
    use WithPagination;

    public string $activeSubTab = 'overview'; // overview|users|deposits|withdrawals|sepay
    public ?string $revealedSecret = null;

    public function switchSubTab(string $tab): void
    {
        $this->activeSubTab = $tab;
        $this->revealedSecret = null;
        $this->resetPage();
    }

    public function toggleSepayEnabled(): void
    {
        $settings = SepaySetting::current();
        $settings->update(['enabled' => ! $settings->enabled]);
    }

    /** Hiện lại secret key hiện tại (đã giải mã) để admin copy dán lại lên SePay Console nếu cần. */
    public function revealSecret(): void
    {
        $settings = SepaySetting::current();
        $this->revealedSecret = $settings->webhook_secret_encrypted;
    }

    public function hideSecret(): void
    {
        $this->revealedSecret = null;
    }

    /** Xoay secret key mới — PHẢI cập nhật lại trên SePay Console sau khi bấm. */
    public function rotateSepaySecret(): void
    {
        $settings = SepaySetting::current();
        $settings->update(['webhook_secret_encrypted' => bin2hex(random_bytes(32))]);
        $this->revealedSecret = $settings->fresh()->webhook_secret_encrypted;
    }

    public function render()
    {
        $data = match ($this->activeSubTab) {
            'users' => ['users' => VaultUser::withCount(['vaults', 'bankAccounts'])
                ->orderByDesc('created_at')
                ->paginate(15)],
            'deposits' => ['deposits' => VaultDepositRequest::with('vaultUser:id,name,phone,vault_code')
                ->orderByDesc('created_at')
                ->paginate(15)],
            'withdrawals' => ['withdrawals' => VaultWithdrawalRequest::with('vaultUser:id,name,phone,vault_code')
                ->orderByDesc('created_at')
                ->paginate(15)],
            default => [],
        };

        return view('livewire.vault-dashboard', [
            ...$data,
            'stats' => [
                'usersCount' => VaultUser::count(),
                'totalPrincipal' => (int) \App\Models\Vault\VaultAccount::sum('principal_amount'),
                'totalInterest' => (int) \App\Models\Vault\VaultAccount::sum('accrued_interest_amount'),
                'pendingWithdrawals' => VaultWithdrawalRequest::whereIn('status', ['pending_otp', 'pending', 'processing'])->count(),
                'pendingDeposits' => VaultDepositRequest::where('status', 'pending_payment')->count(),
            ],
            'sepaySettings' => SepaySetting::current(),
            'otpEnabled' => app(VaultOtpService::class)->isEnabled(),
        ])->layout('components.layouts.vault-cms', [
            'title' => 'Tổng quan',
        ]);
    }
}
