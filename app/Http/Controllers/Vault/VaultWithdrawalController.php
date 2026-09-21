<?php

namespace App\Http\Controllers\Vault;

use App\Actions\Vault\CreateVaultWithdrawal;
use App\Models\Vault\VaultAccount;
use App\Models\Vault\VaultBankAccount;
use App\Models\Vault\VaultWithdrawalRequest;
use App\Services\Vault\VaultOtpService;
use DomainException;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

/**
 * Rút tiền GỒM 2 BƯỚC (mọi lần rút đều bắt buộc PIN + OTP — xem
 * CreateVaultWithdrawal cho chi tiết luồng):
 *   1. POST /withdrawals            -> initiate() -> status=pending_otp
 *   2. POST /otp/request  {purpose: withdrawal, reference_id}  -> gửi OTP
 *   3. POST /withdrawals/{id}/confirm {code}                   -> trừ tiền thật
 */
class VaultWithdrawalController extends VaultBaseController
{
    public function store(Request $request, CreateVaultWithdrawal $action)
    {
        $data = $request->validate([
            'vault_id' => 'required|integer',
            'bank_account_id' => 'required|integer',
            'amount' => 'required|integer|min:50000',
            'pin' => ['required', 'string', 'regex:/^\d{6}$/'],
            'idempotency_key' => 'nullable|string|max:64',
        ]);

        $user = $request->user('vault');
        $vault = VaultAccount::where('id', $data['vault_id'])->where('vault_user_id', $user->id)->first();
        $bankAccount = VaultBankAccount::where('id', $data['bank_account_id'])->where('vault_user_id', $user->id)->first();

        if (! $vault) {
            return $this->fail('Không tìm thấy két', 404);
        }
        if (! $bankAccount) {
            return $this->fail('Không tìm thấy tài khoản ngân hàng', 404);
        }

        $otp = app(VaultOtpService::class);

        try {
            $withdrawal = $action->initiate(
                user: $user,
                vault: $vault,
                bankAccount: $bankAccount,
                amount: (int) $data['amount'],
                pin: $data['pin'],
                idempotencyKey: $data['idempotency_key'] ?? (string) Str::uuid(),
            );
        } catch (DomainException $e) {
            return $this->fail($e->getMessage(), 422);
        }

        if (! $otp->isEnabled()) {
            // OTP đang TẠM TẮT — bỏ qua bước chờ confirm() hoàn toàn, PIN đã
            // xác nhận ở initiate() là lớp bảo vệ duy nhất lúc này. Khôi phục
            // lại luồng 2 bước khi Twilio sẵn sàng (VAULT_OTP_ENABLED=true).
            try {
                $withdrawal = $action->confirm($withdrawal);
            } catch (DomainException $e) {
                return $this->fail($e->getMessage(), 422);
            }

            return $this->ok($this->transform($withdrawal), 'Yêu cầu rút tiền đã được tạo', 201);
        }

        // Tự động gửi OTP luôn — FE không cần gọi /otp/request riêng cho
        // purpose=withdrawal (giảm 1 round-trip, và tránh client "quên" gọi
        // rồi đứng chờ vô thời hạn ở bước confirm).
        try {
            $otp->issue($user, 'withdrawal', $user->phone, $withdrawal->id);
        } catch (DomainException $e) {
            return $this->fail($e->getMessage(), 429);
        } catch (\RuntimeException $e) {
            return $this->fail($e->getMessage(), 503);
        }

        return $this->ok($this->transform($withdrawal), 'Đã gửi mã OTP xác nhận rút tiền', 201);
    }

    public function confirm(Request $request, VaultWithdrawalRequest $withdrawalRequest, CreateVaultWithdrawal $action)
    {
        abort_if($withdrawalRequest->vault_user_id !== $request->user('vault')->id, 403);

        $data = $request->validate([
            'code' => ['required', 'string', 'regex:/^\d{6}$/'],
        ]);

        $user = $request->user('vault');

        try {
            // Verify OTP TRƯỚC — chỉ khi đúng mã mới cho phép confirm() trừ tiền.
            app(VaultOtpService::class)->verify($user, 'withdrawal', $data['code'], $withdrawalRequest->id);
            $withdrawal = $action->confirm($withdrawalRequest);
        } catch (DomainException $e) {
            return $this->fail($e->getMessage(), 422);
        }

        return $this->ok($this->transform($withdrawal), 'Xác nhận thành công, đang xử lý rút tiền');
    }

    public function show(Request $request, VaultWithdrawalRequest $withdrawalRequest)
    {
        abort_if($withdrawalRequest->vault_user_id !== $request->user('vault')->id, 403);

        return $this->ok($this->transform($withdrawalRequest));
    }

    public function index(Request $request)
    {
        $items = VaultWithdrawalRequest::where('vault_user_id', $request->user('vault')->id)
            ->orderByDesc('created_at')
            ->limit(30)
            ->get();

        return $this->ok($items->map(fn ($w) => $this->transform($w))->values());
    }

    private function transform(VaultWithdrawalRequest $w): array
    {
        return [
            'id' => $w->id,
            'vaultId' => $w->vault_id,
            'bankAccountId' => $w->vault_bank_account_id,
            'amount' => $w->amount,
            'status' => $w->status,
            'gatewayRef' => $w->gateway_ref,
            'failureReason' => $w->failure_reason,
            'requestedAt' => $w->requested_at?->toIso8601String(),
            'completedAt' => $w->completed_at?->toIso8601String(),
        ];
    }
}
