<?php

namespace App\Http\Controllers\Vault;

use App\Models\Vault\VaultAccount;
use App\Models\Vault\VaultUser;
use App\Services\Vault\VaultOtpService;
use DomainException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

/**
 * Auth HOÀN TOÀN riêng cho module Vault — Bearer token thuần qua Sanctum,
 * guard 'vault' (config/auth.php), KHÔNG dùng cookie/session của site chính.
 */
class VaultAuthController extends VaultBaseController
{
    public function register(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:100',
            'phone' => 'required|string|max:20|unique:vault_users,phone',
            'email' => 'nullable|email|max:150|unique:vault_users,email',
            'password' => 'required|string|min:6|max:72',
            'referral_code' => 'nullable|string|max:20|exists:vault_users,referral_code',
        ]);

        $referrer = null;
        if (! empty($data['referral_code'])) {
            $referrer = VaultUser::where('referral_code', $data['referral_code'])->first();
        }

        $user = VaultUser::create([
            'name' => $data['name'],
            'phone' => $data['phone'],
            'email' => $data['email'] ?? null,
            'password' => Hash::make($data['password']),
            'vault_code' => 'VAULT-' . random_int(100000, 999999),
            'referral_code' => Str::upper(Str::random(8)),
            'referred_by_id' => $referrer?->id,
        ]);

        // Tự động mở 1 két Linh Hoạt mặc định, số dư 0đ.
        VaultAccount::create([
            'vault_user_id' => $user->id,
            'type' => 'flexible',
            'name' => 'Két Linh Hoạt',
            'interest_rate_yearly' => 6.8,
            'principal_amount' => 0,
            'accrued_interest_amount' => 0,
            'auto_compound' => true,
            'status' => 'active',
        ]);

        $token = $user->createToken('vault-app')->plainTextToken;

        // Tự động gửi OTP xác thực SĐT ngay sau khi đăng ký. Lỗi gửi (vd
        // Twilio chưa cấu hình) KHÔNG chặn đăng ký thành công — user vẫn có
        // tài khoản, chỉ chưa xác thực được SĐT (ekyc cấp 0), có thể yêu cầu
        // gửi lại sau qua POST /otp/request purpose=verify_phone.
        try {
            app(VaultOtpService::class)->issue($user, 'verify_phone', $user->phone);
        } catch (\Throwable $e) {
            report($e);
        }

        return $this->ok([
            'user' => $this->transformUser($user),
            'token' => $token,
        ], 'Đăng ký thành công', 201);
    }

    /**
     * Xác thực SĐT bằng OTP — nâng eKYC lên cấp 1 (trước đây field
     * phone_verified_at tồn tại sẵn nhưng KHÔNG có luồng nào thực sự set nó,
     * ekyc cấp 1 chỉ là mặc định chưa qua xác minh thật).
     */
    public function verifyPhone(Request $request)
    {
        $data = $request->validate([
            'otp_code' => ['required', 'string', 'regex:/^\d{6}$/'],
        ]);

        $user = $request->user('vault');

        try {
            app(VaultOtpService::class)->verify($user, 'verify_phone', $data['otp_code']);
        } catch (DomainException $e) {
            return $this->fail($e->getMessage(), 422);
        }

        $user->update([
            'phone_verified_at' => now(),
            'ekyc_level' => (int) $user->ekyc_level < 1 ? '1' : $user->ekyc_level,
        ]);

        return $this->ok($this->transformUser($user->fresh()), 'Đã xác thực số điện thoại');
    }

    public function login(Request $request)
    {
        $data = $request->validate([
            'phone' => 'required|string',
            'password' => 'required|string',
        ]);

        $user = VaultUser::where('phone', $data['phone'])->first();

        if (! $user || ! Hash::check($data['password'], $user->password)) {
            return $this->fail('Số điện thoại hoặc mật khẩu không đúng', 401);
        }

        $user->update(['last_login_at' => now()]);
        $token = $user->createToken('vault-app')->plainTextToken;

        return $this->ok([
            'user' => $this->transformUser($user),
            'token' => $token,
        ], 'Đăng nhập thành công');
    }

    public function logout(Request $request)
    {
        $request->user('vault')->currentAccessToken()->delete();

        return $this->ok(null, 'Đã đăng xuất');
    }

    public function me(Request $request)
    {
        return $this->ok($this->transformUser($request->user('vault')));
    }

    /**
     * Đặt/đổi PIN — BẮT BUỘC OTP đúng trước đó (purpose=set_pin, xem
     * VaultOtpController). Chặn kẻ xấu chiếm được token đăng nhập rồi tự ý
     * đặt PIN mới để rút tiền (PIN là lớp xác nhận rút tiền quan trọng nhất).
     */
    public function setPin(Request $request)
    {
        $data = $request->validate([
            'pin' => ['required', 'string', 'regex:/^\d{6}$/'],
            'otp_code' => ['required', 'string', 'regex:/^\d{6}$/'],
        ]);

        $user = $request->user('vault');

        try {
            app(VaultOtpService::class)->verify($user, 'set_pin', $data['otp_code']);
        } catch (DomainException $e) {
            return $this->fail($e->getMessage(), 422);
        }

        $user->update([
            'pin_code_hash' => Hash::make($data['pin']),
            'pin_set_at' => now(),
        ]);

        return $this->ok(null, 'Đã cập nhật mã PIN');
    }

    public function verifyPin(Request $request)
    {
        $data = $request->validate([
            'pin' => ['required', 'string', 'regex:/^\d{6}$/'],
        ]);

        $user = $request->user('vault');

        if (! $user->pin_code_hash || ! Hash::check($data['pin'], $user->pin_code_hash)) {
            return $this->fail('Mã PIN không đúng', 422);
        }

        return $this->ok(['valid' => true]);
    }

    private function transformUser(VaultUser $user): array
    {
        return [
            'id' => $user->id,
            'name' => $user->name,
            'phone' => $user->phone,
            'email' => $user->email,
            'avatarUrl' => $user->avatar_url,
            'vipTier' => $user->vip_tier,
            'vaultCode' => $user->vault_code,
            'referralCode' => $user->referral_code,
            'ekycLevel' => $user->ekyc_level,
            'phoneVerified' => $user->phone_verified_at !== null,
            'faceIdEnabled' => $user->face_id_enabled,
            'hasPinSet' => $user->pin_code_hash !== null,
            'dailyWithdrawalLimit' => $user->dailyWithdrawalLimit(),
        ];
    }
}
