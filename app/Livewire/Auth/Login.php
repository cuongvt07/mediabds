<?php

namespace App\Livewire\Auth;

use Livewire\Component;
use Illuminate\Support\Facades\Auth;

class Login extends Component
{
    public $phone = '';
    public $password = '';
    public $remember = true; // User said 'store for long time', effectively remember me with long duration

    protected $rules = [
        'phone' => 'required',
        'password' => 'required',
    ];

    public function login()
    {
        $this->validate();

        if (Auth::attempt(['phone' => $this->phone, 'password' => $this->password], $this->remember)) {
            session()->regenerate();
            return redirect()->intended('/');
        }

        $this->addError('phone', 'The provided credentials do not match our records.');
    }

    public function render()
    {
        return view('livewire.auth.login')->layout('components.layouts.app'); 
        // Assuming default layout, will check if layout needs to be blank style or standard. 
        // Usually login has its own layout or a simple wrapper. I'll stick to a simple clean view in the blade.
    }
}
