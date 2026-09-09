<?php

namespace App\Livewire;

use App\Models\Vault\VaultEkycSubmission;
use App\Models\Vault\VaultUser;
use Illuminate\Support\Facades\DB;
use Livewire\Component;
use Livewire\WithPagination;

/**
 * Duyệt hồ sơ eKYC (CCCD) module Vault — HOÀN TOÀN TÁCH BIỆT guard 'vault'.
 * Đây là trang admin (guard 'web', middleware ['auth','admin'] ở routes/web.php).
 *
 * BẢO MẬT:
 * - approve()/reject() nhận $submissionId (int) rồi TỰ TRUY VẤN LẠI qua
 *   Eloquent (VaultEkycSubmission::findOrFail) — KHÔNG bind trực tiếp Model
 *   từ Livewire property vì Livewire property có thể bị thao túng qua
 *   wire:model từ phía client (đây chính là điểm bạn lo ngại: "query thay đổi
 *   id"). Sau khi tìm bản ghi, kiểm tra status === 'pending' trước khi ghi —
 *   không tin trạng thái hiển thị trên UI, luôn đọc lại từ DB tại thời điểm
 *   xử lý.
 * - Toàn bộ query dùng Eloquent (tham số hoá tự động) — KHÔNG có bất kỳ nơi
 *   nào nối chuỗi SQL thô từ input người dùng (chống SQL injection).
 * - Nâng ekyc_level dùng where(...)->update() (không load-modify-save) để
 *   tránh ghi đè các field khác của VaultUser nếu có request đồng thời khác.
 */
class VaultEkycReview extends Component
{
    use WithPagination;

    public string $statusFilter = 'pending';
    public ?int $rejectingId = null;
    public string $rejectionReason = '';
    public ?string $flashMessage = null;

    public function updatingStatusFilter(): void
    {
        $this->resetPage();
    }

    public function approve(int $submissionId): void
    {
        // Tự truy vấn lại từ DB bằng ID — KHÔNG tin bất kỳ dữ liệu nào khác
        // ngoài chính ID này để xác định bản ghi (chống thao túng qua Livewire
        // property/wire:model).
        $submission = VaultEkycSubmission::findOrFail($submissionId);

        if ($submission->status !== 'pending') {
            $this->flashMessage = 'Hồ sơ này đã được xử lý trước đó.';
            return;
        }

        DB::transaction(function () use ($submission) {
            $submission->update([
                'status' => 'approved',
                'rejection_reason' => null,
                'reviewed_by_user_id' => auth()->id(),
                'reviewed_at' => now(),
            ]);

            VaultUser::where('id', $submission->vault_user_id)->update(['ekyc_level' => '2']);
        });

        $this->flashMessage = 'Đã duyệt hồ sơ #' . $submission->id . '.';
    }

    public function openReject(int $submissionId): void
    {
        $this->rejectingId = $submissionId;
        $this->rejectionReason = '';
    }

    public function closeReject(): void
    {
        $this->rejectingId = null;
        $this->rejectionReason = '';
    }

    public function confirmReject(): void
    {
        $this->validate([
            'rejectionReason' => 'required|string|max:255',
        ], [], ['rejectionReason' => 'lý do từ chối']);

        $submission = VaultEkycSubmission::findOrFail($this->rejectingId);

        if ($submission->status !== 'pending') {
            $this->flashMessage = 'Hồ sơ này đã được xử lý trước đó.';
            $this->closeReject();
            return;
        }

        $submission->update([
            'status' => 'rejected',
            'rejection_reason' => $this->rejectionReason,
            'reviewed_by_user_id' => auth()->id(),
            'reviewed_at' => now(),
        ]);

        $this->flashMessage = 'Đã từ chối hồ sơ #' . $submission->id . '.';
        $this->closeReject();
    }

    public function render()
    {
        $submissions = VaultEkycSubmission::with('vaultUser:id,name,phone,vault_code,ekyc_level')
            ->where('status', $this->statusFilter)
            ->orderBy('created_at')
            ->paginate(15);

        return view('livewire.vault-ekyc-review', [
            'submissions' => $submissions,
            'pendingCount' => VaultEkycSubmission::where('status', 'pending')->count(),
        ]);
    }
}
