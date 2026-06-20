<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;

class UpdateExtensionConfigRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isAdmin() === true;
    }

    public function rules(): array
    {
        return [
            'enabled' => ['required', 'boolean'],
            'minVersion' => ['required', 'string', 'regex:/^\d+\.\d+\.\d+$/'],
            'pollIntervalSeconds' => ['required', 'integer', 'min:60', 'max:86400'],
            'maintenanceMessage' => ['nullable', 'string', 'max:500'],
            'branding' => ['required', 'array:title,supportPhone,supportUrl'],
            'branding.title' => ['required', 'string', 'max:100'],
            'branding.supportPhone' => ['nullable', 'string', 'max:30'],
            'branding.supportUrl' => ['nullable', 'url:http,https', 'max:500'],
            'features' => ['required', 'array:uiEnabled,autoNavigation'],
            'features.uiEnabled' => ['required', 'boolean'],
            'features.autoNavigation' => ['required', 'boolean'],
            'courses' => ['required', 'array', 'max:50'],
            'courses.*' => ['required', 'array:path,label,enabled,priority'],
            'courses.*.path' => ['required', 'string', 'regex:/^\/slides\/[a-z0-9\-]+$/', 'distinct'],
            'courses.*.label' => ['required', 'string', 'max:150'],
            'courses.*.enabled' => ['required', 'boolean'],
            'courses.*.priority' => ['required', 'integer', 'min:1', 'max:9999'],
        ];
    }
}
