<?php

namespace App\Http\Controllers\Vault;

use App\Services\Vault\VaultOtpService;
use DomainException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

/**
 * Đổi số điện thoại — thao tác NHẠY CẢM NHẤT (đổi SĐT gần như đổi luôn quyền
 * đăng nhập), nên bắt buộc OTP CẢ SỐ CŨ LẪN SỐ MỚI, theo đúng thứ tự:
 *
 * 1. POST /change-phone/start        -> gửi OTP về SĐT CŨ (purpose=change_phone_old)
 * 2. POST /change-phone/verify-old   -> verify OTP số cũ, cấp 1 "phiên đổi SĐT"
 *                                        (lưu trong cache, TTL ngắn) + gửi OTP
 *                                        về SĐT MỚI (purpose=change_phone_new)
 * 3. POST /change-phone/verify-new   -> verify OTP số mới + phiên hợp lệ ->
 *                                        THỰC SỰ đổi phone trong DB.
 *
 * "Phiên đổi SĐT" (đã verify số cũ) lưu qua Cache (key theo user, TTL 10
 * phút) — KHÔNG lưu vào bảng DB riêng vì đây là trạng thái tạm thời, ngắn hạn,
 * không cần audit trail lâu dài như OTP.
 */
class VaultChangePhoneController extends VaultBaseController
{
    private const SESSION_TTL_MINUTES = 10;

    public function start(Request $request)
    {
        $user = $request->user('vault');

        try {
            app(VaultOtpService::class)->issue($user, 'change_phone_old', $user->phone);
        } catch (DomainException $e) {
            return $this->fail($e->getMessage(), 429);
        } catch (\RuntimeException $e) {
            return $this->fail($e->getMessage(), 503);
        }

        return $this->ok(null, 'Đã gửi mã OTP về số điện thoại hiện tại');
    }

    public function verifyOld(Request $request)
    {
        $data = $request->validate([
            'otp_code' => ['required', 'string', 'regex:/^\d{6}$/'],
            'new_phone' => ['required', 'string', 'regex:/^0\d{9,10}$/'],
        ]);

        $user = $request->user('vault');

        if (\App\Models\Vault\VaultUser::where('phone', $data['new_phone'])->exists()) {
            return $this->fail('Số điện thoại này đã được sử dụng bởi tài khoản khác', 422);
        }

        try {
            app(VaultOtpService::class)->verify($user, 'change_phone_old', $data['otp_code']);
        } catch (DomainException $e) {
            return $this->fail($e->getMessage(), 422);
        }

        // Cấp phiên đổi SĐT — token ngẫu nhiên riêng (KHÔNG phải Bearer token
        // đăng nhập), chỉ dùng nội bộ giữa verify-old và verify-new, tự hết
        // hạn sau 10 phút, gắn chặt với đúng (user, new_phone) này.
        $sessionToken = (string) Str::uuid();
        Cache::put(
            $this->sessionCacheKey($user->id, $sessionToken),
            ['new_phone' => $data['new_phone']],
            now()->addMinutes(self::SESSION_TTL_MINUTES),
        );

        try {
            app(VaultOtpService::class)->issue($user, 'change_phone_new', $data['new_phone']);
        } catch (DomainException $e) {
            return $this->fail($e->getMessage(), 429);
        } catch (\RuntimeException $e) {
            return $this->fail($e->getMessage(), 503);
        }

        return $this->ok(['sessionToken' => $sessionToken], 'Đã gửi mã OTP về số điện thoại mới');
    }

    public function verifyNew(Request $request)
    {
        $data = $request->validate([
            'session_token' => 'required|string',
            'otp_code' => ['required', 'string', 'regex:/^\d{6}$/'],
        ]);

        $user = $request->user('vault');
        $cacheKey = $this->sessionCacheKey($user->id, $data['session_token']);
        $session = Cache::get($cacheKey);

        if (! $session) {
            return $this->fail('Phiên đổi số điện thoại đã hết hạn, vui lòng thực hiện lại từ đầu', 422);
        }

        // Re-check trùng SĐT tại thời điểm confirm — phòng trường hợp người
        // khác đăng ký đúng số này trong lúc phiên đang chờ (dù hiếm).
        if (\App\Models\Vault\VaultUser::where('phone', $session['new_phone'])->exists()) {
            Cache::forget($cacheKey);
            return $this->fail('Số điện thoại này đã được sử dụng bởi tài khoản khác', 422);
        }

        try {
            app(VaultOtpService::class)->verify($user, 'change_phone_new', $data['otp_code']);
        } catch (DomainException $e) {
            return $this->fail($e->getMessage(), 422);
        }

        $user->update(['phone' => $session['new_phone']]);
        Cache::forget($cacheKey);

        return $this->ok($this->transformUser($user->fresh()), 'Đã đổi số điện thoại thành công');
    }

    private function sessionCacheKey(int $userId, string $sessionToken): string
    {
        return "vault:change-phone-session:{$userId}:{$sessionToken}";
    }

    private function transformUser(\App\Models\Vault\VaultUser $user): array
    {
        return [
            'id' => $user->id,
            'phone' => $user->phone,
        ];
    }
}
