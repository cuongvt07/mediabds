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
                'registerName' => 'required|string|min:2|max:255',
                'registerPhone' => ['required', 'regex:/^(0[3|5|7|8|9])+([0-9]{8})$/', 'unique:users,phone'],
                'registerInviteCode' => 'required|exists:users,invite_code',
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
            'registerName.min' => 'Họ và tên phải có ít nhất 2 ký tự.',
            'registerName.max' => 'Họ và tên không được vượt quá 255 ký tự.',
            'registerPhone.required' => 'Vui lòng nhập số điện thoại.',
            'registerPhone.regex' => 'Số điện thoại không đúng định dạng (Ví dụ: 098... hoặc 03...).',
            'registerPhone.unique' => 'Số điện thoại này đã tồn tại trong hệ thống.',
            'registerInviteCode.required' => 'Vui lòng nhập mã giới thiệu hợp lệ.',
            'registerInviteCode.exists' => 'Mã giới thiệu không tồn tại. Vui lòng kiểm tra lại.',
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
        $inviterCode = '';

        if ($this->registerInviteCode) {
            $inviter = \App\Models\User::where('invite_code', $this->registerInviteCode)->first();
            if ($inviter) {
                $inviterId = $inviter->id;
                $inviterCode = $inviter->invite_code;
            }
        }

        // Must logically have an inviter since it's validated, but check again to be safe.
        if (!$inviterId) {
            $this->addError('registerInviteCode', 'Không tìm thấy người giới thiệu.');
            return;
        }

        \Illuminate\Support\Facades\DB::transaction(function () use ($inviterId, $inviterCode) {
            $user = \App\Models\User::create([
                'name' => $this->registerName,
                'phone' => $this->registerPhone,
                'password' => \Illuminate\Support\Facades\Hash::make('password'), // default placeholder
                'invite_code' => 'TEMP', // Will update right after getting ID
                'invited_by_user_id' => $inviterId,
            ]);

            // Generate structured invite code: [InviterCode][NewUserID]
            $user->update([
                'invite_code' => $inviterCode . $user->id,
            ]);

            \App\Models\UserInvite::create([
                'inviter_user_id' => $inviterId,
                'invited_user_id' => $user->id,
                'inviter_code' => $this->registerInviteCode,
            ]);

            Auth::login($user, true);
        });

        session()->regenerate();
        return redirect()->route('listings');
    }

    public function render()
    {
        return view('livewire.auth.login')->layout('components.layouts.guest');
    }
}
