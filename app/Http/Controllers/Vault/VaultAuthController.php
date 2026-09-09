<?php

namespace App\Http\Controllers\Vault;

use App\Models\Vault\VaultAccount;
use App\Models\Vault\VaultUser;
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

        return $this->ok([
            'user' => $this->transformUser($user),
            'token' => $token,
        ], 'Đăng ký thành công', 201);
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

    public function setPin(Request $request)
    {
        $data = $request->validate([
            'pin' => ['required', 'string', 'regex:/^\d{6}$/'],
        ]);

        $user = $request->user('vault');
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
            'faceIdEnabled' => $user->face_id_enabled,
            'hasPinSet' => $user->pin_code_hash !== null,
            'dailyWithdrawalLimit' => $user->dailyWithdrawalLimit(),
        ];
    }
}
