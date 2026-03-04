<?php

namespace App\Livewire\Auth;

use Livewire\Component;
use Illuminate\Support\Facades\Auth;

class Login extends Component
{
    public $phone = '';
    public $remember = true;

    // Registration properties
    public $isRegistering = false;
    public $registerName = '';
    public $registerPhone = '';
    public $registerInviteCode = '';

    protected function rules()
    {
        if ($this->isRegistering) {
            return [
                'registerName' => 'required|min:2',
                'registerPhone' => 'required|unique:users,phone',
                'registerInviteCode' => 'nullable|exists:users,invite_code',
            ];
        }

        return [
            'phone' => 'required',
        ];
    }

    protected function messages()
    {
        return [
            'registerName.required' => 'Vui lòng nhập họ và tên.',
            'registerPhone.required' => 'Vui lòng nhập số điện thoại.',
            'registerPhone.unique' => 'Số điện thoại này đã tồn tại.',
            'registerInviteCode.exists' => 'Mã mời không hợp lệ.',
            'phone.required' => 'Vui lòng nhập số điện thoại.',
            'phone.exists' => 'Số điện thoại này chưa được đăng ký.',
        ];
    }

    public function toggleRegister()
    {
        $this->isRegistering = !$this->isRegistering;
        $this->resetErrorBag();
    }

    public function login()
    {
        $this->validate([
            'phone' => 'required|exists:users,phone',
        ]);

        $user = \App\Models\User::where('phone', $this->phone)->first();

        if ($user) {
            Auth::login($user, $this->remember);
            session()->regenerate();
            return redirect()->intended(route('listings'));
        }

        $this->addError('phone', 'Có lỗi xảy ra khi đăng nhập.');
    }

    public function register()
    {
        $this->validate($this->rules(), $this->messages());

        $inviterId = null;

        if ($this->registerInviteCode) {
            $inviter = \App\Models\User::where('invite_code', $this->registerInviteCode)->first();
            if ($inviter) {
                $inviterId = $inviter->id;
            }
        }

        // Generate unique 6-character alphanumeric invite code
        $inviteCode = null;
        do {
            $inviteCode = strtoupper(\Illuminate\Support\Str::random(6));
        } while (\App\Models\User::where('invite_code', $inviteCode)->exists());

        $user = \App\Models\User::create([
            'name' => $this->registerName,
            'phone' => $this->registerPhone,
            'password' => \Illuminate\Support\Facades\Hash::make('password'), // default placeholder
            'invite_code' => $inviteCode,
            'invited_by_user_id' => $inviterId,
        ]);

        if ($inviterId) {
            \App\Models\UserInvite::create([
                'inviter_user_id' => $inviterId,
                'invited_user_id' => $user->id,
                'inviter_code' => $this->registerInviteCode,
            ]);
        }

        Auth::login($user, true);
        session()->regenerate();
        return redirect()->route('listings');
    }

    public function render()
    {
        return view('livewire.auth.login')->layout('components.layouts.guest');
    }
}
