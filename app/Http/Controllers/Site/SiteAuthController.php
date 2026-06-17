<?php

namespace App\Http\Controllers\Site;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class SiteAuthController extends Controller
{
    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'phone' => 'required|string',
            'password' => 'required|string',
        ], [
            'phone.required' => 'Vui lòng nhập số điện thoại.',
            'password.required' => 'Vui lòng nhập mật khẩu.',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput($request->only('phone'))->with('authMode', 'login');
        }

        if (! Auth::attempt(['phone' => $request->input('phone'), 'password' => $request->input('password')], (bool) $request->boolean('remember'))) {
            return back()
                ->withErrors(['phone' => 'Số điện thoại hoặc mật khẩu không đúng.'])
                ->withInput($request->only('phone'))
                ->with('authMode', 'login');
        }

        $request->session()->regenerate();

        $user = Auth::user();
        if ($user && method_exists($user, 'isAdmin') && $user->isAdmin()) {
            return redirect()->intended(route('site.admin'));
        }

        return redirect()->intended(route('site.home'));
    }

    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|min:2|max:255',
            'phone' => ['required', 'regex:/^0\d{9}$/', 'unique:users,phone'],
            'password' => 'required|string|min:6|max:255',
        ], [
            'name.required' => 'Vui lòng nhập họ và tên.',
            'phone.required' => 'Vui lòng nhập số điện thoại.',
            'phone.regex' => 'Số điện thoại phải gồm 10 số và bắt đầu bằng 0.',
            'phone.unique' => 'Số điện thoại này đã có tài khoản.',
            'password.required' => 'Vui lòng nhập mật khẩu.',
            'password.min' => 'Mật khẩu phải có ít nhất 6 ký tự.',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput($request->only('name', 'phone'))->with('authMode', 'register');
        }

        $user = User::create([
            'name' => $request->input('name'),
            'phone' => $request->input('phone'),
            'password' => Hash::make($request->input('password')),
            'role' => 'buyer',
        ]);

        Auth::login($user, true);
        $request->session()->regenerate();

        return redirect()->intended(route('site.home'));
    }
}
