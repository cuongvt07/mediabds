<?php

namespace App\Http\Controllers\Vault;

use App\Models\Vault\VaultUser;
use App\Models\Vault\VaultWithdrawalRequest;
use App\Services\Vault\VaultOtpService;
use DomainException;
use Illuminate\Http\Request;

/**
 * Endpoint OTP dùng cho verify_phone (xác thực SĐT khi đăng ký/gửi lại),
 * set_pin (đặt/đổi PIN), và withdrawal (gửi lại OTP rút tiền nếu lần đầu
 * không nhận được SMS) — xem VaultOtpService cho logic lõi.
 *
 * Luồng đổi số điện thoại (change_phone) có controller RIÊNG
 * (VaultChangePhoneController) vì cần 2 bước tuần tự (OTP số cũ rồi số mới),
 * khác cấu trúc request/verify đơn giản ở đây.
 *
 * BẢO MẬT: mỗi `purpose` có ràng buộc RIÊNG về việc dùng SĐT nào + được phép
 * yêu cầu khi nào — KHÔNG cho client tự do chọn phone/reference_id tuỳ ý,
 * tránh trường hợp gửi OTP tới số điện thoại bất kỳ nhân danh tài khoản này
 * (spam SMS bomb) hoặc verify OTP cho giao dịch không phải của chính mình.
 */
class VaultOtpController extends VaultBaseController
{
    private const PURPOSES = ['verify_phone', 'set_pin', 'withdrawal'];

    public function request(Request $request)
    {
        $data = $request->validate([
            'purpose' => ['required', 'string', 'in:' . implode(',', self::PURPOSES)],
            // Chỉ dùng cho purpose=withdrawal — id lệnh rút đang chờ xác nhận OTP.
            'reference_id' => 'nullable|integer',
        ]);

        $user = $request->user('vault');
        $purpose = $data['purpose'];

        [$phone, $referenceId, $error] = $this->resolvePhoneAndReference($user, $purpose, $data);
        if ($error) {
            return $this->fail($error, 422);
        }

        try {
            app(VaultOtpService::class)->issue($user, $purpose, $phone, $referenceId);
        } catch (DomainException $e) {
            return $this->fail($e->getMessage(), 429);
        } catch (\RuntimeException $e) {
            // VaultSmsService throw khi Twilio chưa cấu hình / gửi lỗi.
            return $this->fail($e->getMessage(), 503);
        }

        // Không trả lại số điện thoại đầy đủ trong response — chỉ trả phần
        // che để FE hiển thị "Mã đã gửi tới 09xx***123" mà không lộ full số
        // (quan trọng nhất với change_phone, nơi $phone có thể là số MỚI).
        return $this->ok(['maskedPhone' => $this->maskPhone($phone)], 'Đã gửi mã OTP');
    }

    public function verify(Request $request)
    {
        $data = $request->validate([
            'purpose' => ['required', 'string', 'in:' . implode(',', self::PURPOSES)],
            'code' => ['required', 'string', 'regex:/^\d{6}$/'],
            'reference_id' => 'nullable|integer',
        ]);

        $user = $request->user('vault');

        try {
            app(VaultOtpService::class)->verify($user, $data['purpose'], $data['code'], $data['reference_id'] ?? null);
        } catch (DomainException $e) {
            return $this->fail($e->getMessage(), 422);
        }

        return $this->ok(['verified' => true]);
    }

    /**
     * Xác định SĐT thực sự sẽ nhận OTP + reference_id hợp lệ cho mỗi purpose.
     * Đây là nơi CHẶN việc client tự ý chỉ định phone/reference tuỳ ý.
     *
     * @return array{0: ?string, 1: ?int, 2: ?string} [phone, referenceId, lỗi]
     */
    private function resolvePhoneAndReference(VaultUser $user, string $purpose, array $data): array
    {
        return match ($purpose) {
            'verify_phone', 'set_pin' => [$user->phone, null, null],

            'withdrawal' => $this->resolveWithdrawalReference($user, $data),

            default => [null, null, 'Mục đích không hợp lệ'],
        };
    }

    private function resolveWithdrawalReference(VaultUser $user, array $data): array
    {
        if (empty($data['reference_id'])) {
            return [null, null, 'Thiếu mã lệnh rút tiền cần xác nhận'];
        }

        // Kiểm tra lệnh rút này THẬT SỰ thuộc về user đang đăng nhập — chặn
        // việc lấy reference_id của người khác để "xin" OTP gửi cho chính
        // mình rồi verify hộ (không hợp lệ vì OTP gửi tới SĐT của USER ĐANG
        // ĐĂNG NHẬP, nhưng vẫn chặn sớm để tránh lộ có/không tồn tại lệnh đó).
        $withdrawal = VaultWithdrawalRequest::where('id', $data['reference_id'])
            ->where('vault_user_id', $user->id)
            ->first();

        if (! $withdrawal) {
            return [null, null, 'Không tìm thấy lệnh rút tiền hợp lệ'];
        }

        if ($withdrawal->status !== 'pending_otp') {
            return [null, null, 'Lệnh rút tiền này không ở trạng thái chờ xác nhận OTP'];
        }

        return [$user->phone, $withdrawal->id, null];
    }

    private function maskPhone(string $phone): string
    {
        return strlen($phone) > 6
            ? substr($phone, 0, 4) . str_repeat('*', strlen($phone) - 7) . substr($phone, -3)
            : $phone;
    }
}
