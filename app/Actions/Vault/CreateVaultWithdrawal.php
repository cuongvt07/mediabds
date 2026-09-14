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
use Illuminate\Support\Facades\Hash;

/**
 * Luồng rút tiền GỒM 2 BƯỚC — MỌI lần rút đều bắt buộc PIN + OTP SMS (theo
 * yêu cầu bảo mật, không có ngoại lệ theo số tiền):
 *
 * 1. initiate(): validate số dư/hạn mức/PIN, CHƯA trừ tiền — chỉ tạo bản ghi
 *    status='pending_otp' rồi tự gửi OTP về SĐT của user (xem
 *    VaultOtpController::request với purpose=withdrawal, reference_id = id
 *    bản ghi này).
 * 2. confirm(): verify mã OTP đúng — CHỈ KHI ĐÓ mới thực sự trừ tiền (post
 *    vào ledger) rồi chuyển status='pending' + dispatch job xử lý (giống
 *    luồng cũ). Nếu OTP sai/hết hạn, bản ghi vẫn ở pending_otp, KHÔNG mất
 *    tiền — tiền chỉ rời két ở đúng thời điểm confirm() thành công.
 */
class CreateVaultWithdrawal
{
    private const MIN_AMOUNT = 50_000;
    private const OTP_EXPIRES_MINUTES = 10; // bản ghi pending_otp tự "nguội" nếu không confirm kịp

    public function __construct(
        private VaultLedgerService $ledger,
    ) {
    }

    /**
     * Bước 1 — validate + tạo bản ghi chờ OTP. KHÔNG trừ tiền ở đây.
     *
     * @throws DomainException nếu PIN sai, vượt hạn mức, két không hợp lệ...
     */
    public function initiate(
        VaultUser $user,
        VaultAccount $vault,
        VaultBankAccount $bankAccount,
        int $amount,
        string $pin,
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

        if (! $user->pin_code_hash || ! Hash::check($pin, $user->pin_code_hash)) {
            throw new DomainException('Mã PIN không đúng');
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
            // cầu rút tiền của CÙNG 1 user — chặn race condition ở hạn mức/ngày
            // VÀ ở kiểm tra số dư khả dụng (xem assertSufficientBalance).
            VaultUser::where('id', $user->id)->lockForUpdate()->firstOrFail();

            $this->assertWithinDailyLimit($user, $amount);
            $this->assertSufficientBalance($vault, $amount);

            return VaultWithdrawalRequest::create([
                'vault_user_id' => $user->id,
                'vault_id' => $vault->id,
                'vault_bank_account_id' => $bankAccount->id,
                'amount' => $amount,
                'status' => 'pending_otp',
                'idempotency_key' => $idempotencyKey,
                'requested_at' => now(),
            ]);
        });
    }

    /**
     * Bước 2 — gọi SAU KHI VaultOtpService::verify() đã xác nhận đúng mã.
     * Controller PHẢI verify OTP trước khi gọi hàm này — hàm này không tự
     * verify lại (tách trách nhiệm: OTP là việc của VaultOtpService).
     *
     * @throws DomainException nếu bản ghi không ở đúng trạng thái chờ, hoặc
     *         không đủ số dư TẠI THỜI ĐIỂM XÁC NHẬN (số dư có thể đã thay đổi
     *         từ lúc initiate() tới lúc confirm() do giao dịch khác).
     */
    public function confirm(VaultWithdrawalRequest $withdrawal): VaultWithdrawalRequest
    {
        if ($withdrawal->status !== 'pending_otp') {
            throw new DomainException('Lệnh rút tiền này không ở trạng thái chờ xác nhận OTP');
        }

        if ($withdrawal->requested_at->diffInMinutes(now()) > self::OTP_EXPIRES_MINUTES) {
            $withdrawal->update(['status' => 'failed', 'failure_reason' => 'Hết thời gian xác nhận OTP']);
            throw new DomainException('Đã quá thời gian xác nhận, vui lòng tạo lại lệnh rút tiền');
        }

        return DB::transaction(function () use ($withdrawal) {
            // Trừ tiền NGAY BÂY GIỜ (lock row két bên trong VaultLedgerService::post).
            // idempotency_key của bản ghi withdrawal vẫn dùng lại — confirm() có
            // gọi lại nhiều lần (double-submit OTP) cũng không trừ 2 lần.
            $this->ledger->post(
                vaultAccountId: $withdrawal->vault_id,
                type: 'withdrawal',
                amount: -$withdrawal->amount,
                idempotencyKey: $withdrawal->idempotency_key,
            );

            $withdrawal->update(['status' => 'pending']);

            ProcessVaultWithdrawal::dispatch($withdrawal)->onQueue('vault');

            return $withdrawal;
        });
    }

    /**
     * Đọc tổng đã rút trong ngày TRỰC TIẾP TỪ DB trong transaction đang lock
     * row user (xem initiate()) — nguồn sự thật duy nhất, tính cả bản ghi
     * đang pending_otp (đã "giữ chỗ" hạn mức dù chưa trừ tiền thật, tránh user
     * mở nhiều tab tạo hàng loạt lệnh pending_otp để lách hạn mức).
     */
    private function assertWithinDailyLimit(VaultUser $user, int $amount): void
    {
        $limit = $user->dailyWithdrawalLimit();

        $current = (int) VaultWithdrawalRequest::where('vault_user_id', $user->id)
            ->whereIn('status', ['pending_otp', 'pending', 'processing', 'success'])
            ->whereDate('requested_at', now()->toDateString())
            ->lockForUpdate()
            ->sum('amount');

        if ($current + $amount > $limit) {
            throw new DomainException('Vượt hạn mức rút tiền trong ngày');
        }
    }

    /**
     * Vì initiate() không trừ tiền ngay, phải tự kiểm tra số dư khả dụng ở
     * đây (VaultLedgerService::post() vốn tự chặn số dư âm, nhưng đó là lúc
     * confirm() — cần báo lỗi SỚM ngay từ initiate() để UX tốt hơn, không đợi
     * user nhập xong OTP mới biết không đủ tiền).
     */
    private function assertSufficientBalance(VaultAccount $vault, int $amount): void
    {
        $vault->refresh();
        if ($vault->totalBalance() < $amount) {
            throw new DomainException('Số dư trong két không đủ để thực hiện giao dịch này');
        }
    }
}
