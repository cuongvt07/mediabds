<?php

namespace App\Http\Controllers\Api;

use App\Http\Requests\Api\LoginRequest;
use App\Http\Requests\Api\RegisterRequest;
use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AuthController extends BaseApiController
{
    /**
     * Authenticate a user via the web guard (SPA cookie mode).
     */
    public function login(LoginRequest $req)
    {
        $credentials = $req->only('phone', 'password');

        if (! auth()->attempt($credentials)) {
            return $this->fail('Sai số điện thoại hoặc mật khẩu', 401);
        }

        $req->session()->regenerate();

        return $this->ok(new UserResource(auth()->user()), 'OK');
    }

    /**
     * Register a new user. Buyers by default; CTV if a valid invite code is provided.
     */
    public function register(RegisterRequest $req)
    {
        $data = $req->validated();

        $inviter = null;
        if (! empty($data['invite_code'])) {
            $inviter = User::where('invite_code', $data['invite_code'])->first();
        }

        $user = User::create([
            'name'               => $data['name'],
            'phone'              => $data['phone'],
            'password'           => Hash::make($data['password']),
            'role'               => $inviter ? 'ctv' : 'buyer',
            'invited_by_user_id' => $inviter?->id,
            'invite_code'        => null,
        ]);

        // Generate invite_code = [inviterCode][userId] hoặc [BD][id] cho buyer
        $prefix = $inviter ? $inviter->invite_code : 'BD';
        $user->update(['invite_code' => $prefix . $user->id]);

        auth()->login($user);
        $req->session()->regenerate();

        return $this->ok(new UserResource($user->fresh()), 'Registered', 201);
    }

    /**
     * Return the currently authenticated user.
     */
    public function me()
    {
        $user = auth()->user();

        if (! $user) {
            return $this->fail('Unauthenticated', 401);
        }

        return $this->ok(new UserResource($user));
    }

    /**
     * Log the current user out of the web session.
     */
    public function logout(Request $req)
    {
        auth()->guard('web')->logout();
        $req->session()->invalidate();
        $req->session()->regenerateToken();

        return $this->ok(null, 'Logged out');
    }
}
