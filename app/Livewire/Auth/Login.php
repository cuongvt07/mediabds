<?php

namespace App\Livewire\Auth;

use Livewire\Component;
use Illuminate\Support\Facades\Auth;

class Login extends Component
{
    public $phone = '';
    public $remember = true; 

    protected $rules = [
        'phone' => 'required',
    ];

    public function login()
    {
        $this->validate([
            'phone' => 'required|exists:users,phone',
        ], [
            'phone.exists' => 'Số điện thoại này chưa được đăng ký.',
        ]);

        $user = \App\Models\User::where('phone', $this->phone)->first();

        if ($user) {
            Auth::login($user, $this->remember);
            session()->regenerate();
            return redirect()->intended(route('listings'));
        }

        $this->addError('phone', 'Có lỗi xảy ra khi đăng nhập.');
    }

    public function render()
    {
        return view('livewire.auth.login')->layout('components.layouts.guest');
    }
}
