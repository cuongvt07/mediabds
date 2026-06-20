<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;

class ActivateExtensionRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'licenseKey' => ['required', 'string', 'min:20', 'max:120'],
            'deviceId' => ['required', 'string', 'min:16', 'max:200'],
            'deviceName' => ['nullable', 'string', 'max:120'],
        ];
    }
}
