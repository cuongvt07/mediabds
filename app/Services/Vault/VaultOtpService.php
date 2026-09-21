<?php

namespace App\Services\Vault;

use App\Models\Vault\VaultOtpCode;
use App\Models\Vault\VaultUser;
use DomainException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

/**
 * Sinh/gửi/xác minh mã OTP dùng chung cho mọi luồng (verify_phone, set_pin,
 * withdrawal, change_phone). Đây là lớp bảo mật cốt lõi — mọi luồng OTP khác
 * trong module Vault PHẢI đi qua service này, không tự viết logic OTP riêng.
 *
 * BẢO MẬT:
 * - Mã OTP 6 số, chỉ lưu HASH (bcrypt) — không bao giờ lưu/log mã gốc.
 * - Hết hạn sau 5 phút.
 * - Chống brute-force: tối đa 5 lần nhập sai/mã, sai quá thì mã bị vô hiệu
 *   hoá ngay (không cần đợi hết hạn).
 * - Chống spam gửi OTP: tối thiểu 60 giây giữa 2 lần gửi cùng
 *   (user, purpose); tối đa 5 lần gửi/giờ cho cùng (user, purpose).
 * - verify() dùng row lock (lockForUpdate) trong transaction để 2 request
 *   xác minh đồng thời (double-submit OTP) không thể cùng lúc pass qua giới
 *   hạn attempts hay cùng "tiêu" 1 mã 2 lần.
 *
 * CỜ TẠM THỜI services.twilio.otp_enabled (env VAULT_OTP_ENABLED, default
 * false): khi tắt — issue() KHÔNG gửi SMS thật (chỉ ghi log, không throw dù
 * Twilio chưa cấu hình), verify() LUÔN coi là đúng mà không cần tra DB. Toàn
 * bộ logic OTP thật vẫn giữ nguyên phía dưới — chỉ cần đổi biến env để phục
 * hồi khi có Twilio thật, không cần sửa code nơi gọi (controller không biết
 * và không cần biết cờ này).
 */
class VaultOtpService
{
    private const CODE_LENGTH = 6;
    private const EXPIRES_MINUTES = 5;
    private const MAX_ATTEMPTS = 5;
    private const RESEND_COOLDOWN_SECONDS = 60;
    private const MAX_SENDS_PER_HOUR = 5;

    public function __construct(private VaultSmsService $sms)
    {
    }

    public function isEnabled(): bool
    {
        return (bool) config('services.twilio.otp_enabled');
    }

    /**
     * Sinh mã mới, gửi SMS, lưu hash vào DB. $phone là số NHẬN otp (khác
     * $user->phone khi đang đổi sang số mới — xem luồng change_phone).
     *
     * @throws DomainException nếu bị throttle (gửi quá nhanh/quá nhiều lần).
     */
    public function issue(VaultUser $user, string $purpose, string $phone, ?int $referenceId = null): void
    {
        if (! $this->isEnabled()) {
            Log::info('VaultOtpService: OTP đang tắt tạm thời (VAULT_OTP_ENABLED=false), bỏ qua gửi SMS', [
                'vault_user_id' => $user->id,
                'purpose' => $purpose,
            ]);

            return;
        }

        $this->assertNotThrottled($user, $purpose);

        $code = (string) random_int(100000, 999999);

        VaultOtpCode::create([
            'vault_user_id' => $user->id,
            'purpose' => $purpose,
            'phone' => $phone,
            'code_hash' => Hash::make($code),
            'reference_id' => $referenceId,
            'attempts' => 0,
            'expires_at' => now()->addMinutes(self::EXPIRES_MINUTES),
        ]);

        $this->sms->send($phone, "Ma xac thuc Vault cua ban la: {$code}. Ma co hieu luc trong 5 phut, khong chia se cho bat ky ai.");
    }

    /**
     * Xác minh mã OTP mới nhất (chưa dùng, chưa hết hạn) của
     * (user, purpose[, referenceId]). Trả về true nếu đúng — mã sẽ được đánh
     * dấu đã dùng (consumed_at), KHÔNG thể verify lại lần 2 dù đúng mã.
     *
     * @throws DomainException nếu không có mã hợp lệ, mã hết hạn, hoặc nhập sai.
     */
    public function verify(VaultUser $user, string $purpose, string $code, ?int $referenceId = null): bool
    {
        if (! $this->isEnabled()) {
            return true;
        }

        return DB::transaction(function () use ($user, $purpose, $code, $referenceId) {
            $query = VaultOtpCode::where('vault_user_id', $user->id)
                ->where('purpose', $purpose)
                ->whereNull('consumed_at')
                ->orderByDesc('created_at')
                ->lockForUpdate();

            if ($referenceId !== null) {
                $query->where('reference_id', $referenceId);
            }

            $otp = $query->first();

            if (! $otp) {
                throw new DomainException('Không tìm thấy mã OTP hợp lệ, vui lòng yêu cầu gửi lại.');
            }

            if ($otp->isExpired()) {
                throw new DomainException('Mã OTP đã hết hạn, vui lòng yêu cầu gửi lại.');
            }

            if ($otp->attempts >= self::MAX_ATTEMPTS) {
                // Vô hiệu hoá luôn — không cho thử tiếp dù chưa hết hạn.
                $otp->update(['consumed_at' => now()]);
                throw new DomainException('Bạn đã nhập sai quá số lần cho phép, vui lòng yêu cầu gửi lại mã mới.');
            }

            if (! Hash::check($code, $otp->code_hash)) {
                $otp->increment('attempts');
                $remaining = self::MAX_ATTEMPTS - $otp->attempts;
                throw new DomainException(
                    $remaining > 0
                        ? "Mã OTP không đúng, bạn còn {$remaining} lần thử."
                        : 'Mã OTP không đúng, bạn đã hết lượt thử.'
                );
            }

            $otp->update(['consumed_at' => now()]);

            return true;
        });
    }

    private function assertNotThrottled(VaultUser $user, string $purpose): void
    {
        $recent = VaultOtpCode::where('vault_user_id', $user->id)
            ->where('purpose', $purpose)
            ->orderByDesc('created_at')
            ->first();

        if ($recent && $recent->created_at->diffInSeconds(now()) < self::RESEND_COOLDOWN_SECONDS) {
            $wait = self::RESEND_COOLDOWN_SECONDS - $recent->created_at->diffInSeconds(now());
            throw new DomainException("Vui lòng đợi {$wait} giây trước khi yêu cầu gửi lại mã.");
        }

        $sentLastHour = VaultOtpCode::where('vault_user_id', $user->id)
            ->where('purpose', $purpose)
            ->where('created_at', '>=', now()->subHour())
            ->count();

        if ($sentLastHour >= self::MAX_SENDS_PER_HOUR) {
            throw new DomainException('Bạn đã yêu cầu gửi mã quá nhiều lần trong 1 giờ, vui lòng thử lại sau.');
        }
    }
}
