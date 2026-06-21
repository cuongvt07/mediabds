<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;

class RegisterRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'name' => 'required|string|max:120',
            'phone' => ['required', 'string', 'regex:/^0\d{9}$/', 'unique:users,phone'],
            'password' => 'required|string|min:6|confirmed',
            'invite_code' => 'nullable|string|exists:users,invite_code',
        ];
    }

    public function messages(): array
    {
        return [
            'phone.required' => 'Vui lòng nhập số điện thoại.',
            'phone.regex' => 'Số điện thoại phải gồm 10 số và bắt đầu bằng 0.',
            'phone.unique' => 'Số điện thoại này đã có tài khoản.',
        ];
    }

    /**
     * Resolve the role for the registering user.
     *
     * If an invite_code is provided (and valid), the user becomes a "ctv".
     * Otherwise, the user defaults to "buyer".
     */
    public function resolvedRole(): string
    {
        return $this->filled('invite_code') ? 'ctv' : 'buyer';
    }
}
