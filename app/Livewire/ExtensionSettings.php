<?php

namespace App\Livewire;

use App\Models\ExtensionSetting;
use Livewire\Component;

class ExtensionSettings extends Component
{
    public bool $enabled = true;
    public string $minVersion = '1.0.0';
    public int $pollIntervalSeconds = 300;
    public ?string $maintenanceMessage = null;
    public string $brandingTitle = 'DEV CƯỜNG TOOL';
    public ?string $supportPhone = '0943206425';
    public ?string $supportUrl = null;
    public bool $uiEnabled = true;
    public bool $autoNavigation = false;
    public array $courses = [];
    public ?string $signingPublicKey = null;

    public function mount(): void
    {
        $this->loadSetting();
    }

    public function save(): void
    {
        $data = $this->validate($this->rules());
        $setting = ExtensionSetting::current();
        $setting->forceFill([
            'value' => [
                'enabled' => (bool) $data['enabled'],
                'minVersion' => $data['minVersion'],
                'pollIntervalSeconds' => (int) $data['pollIntervalSeconds'],
                'maintenanceMessage' => $data['maintenanceMessage'] ?: null,
                'branding' => [
                    'title' => $data['brandingTitle'],
                    'supportPhone' => $data['supportPhone'] ?: null,
                    'supportUrl' => $data['supportUrl'] ?: null,
                ],
                'features' => [
                    'uiEnabled' => (bool) $data['uiEnabled'],
                    'autoNavigation' => (bool) $data['autoNavigation'],
                ],
                'courses' => collect($data['courses'])
                    ->sortBy('priority')
                    ->values()
                    ->all(),
            ],
            'updated_by' => auth()->id(),
        ])->save();

        $this->loadSetting();
        session()->flash('message', 'Đã lưu cấu hình extension.');
    }

    public function addCourse(): void
    {
        $this->courses[] = [
            'path' => '/slides/',
            'label' => '',
            'enabled' => true,
            'priority' => count($this->courses) + 1,
        ];
    }

    public function removeCourse(int $index): void
    {
        unset($this->courses[$index]);
        $this->courses = array_values($this->courses);
    }

    public function generateSigningKeys(): void
    {
        $pair = sodium_crypto_sign_keypair();
        $setting = ExtensionSetting::current();
        $setting->forceFill([
            'signing_public_key' => base64_encode(sodium_crypto_sign_publickey($pair)),
            'signing_secret_key' => base64_encode(sodium_crypto_sign_secretkey($pair)),
            'updated_by' => auth()->id(),
        ])->save();

        $this->signingPublicKey = $setting->signing_public_key;
        session()->flash('message', 'Đã tạo cặp khóa ký mới. Public key trên client cần được cập nhật tương ứng.');
    }

    private function loadSetting(): void
    {
        $setting = ExtensionSetting::current();
        $defaults = config('extension.defaults');
        $stored = $setting->value ?? [];
        $value = array_replace($defaults, $stored);
        $value['branding'] = array_replace($defaults['branding'], $stored['branding'] ?? []);
        $value['features'] = array_replace($defaults['features'], $stored['features'] ?? []);
        $value['courses'] = $stored['courses'] ?? $defaults['courses'];

        $this->enabled = (bool) $value['enabled'];
        $this->minVersion = $value['minVersion'];
        $this->pollIntervalSeconds = (int) $value['pollIntervalSeconds'];
        $this->maintenanceMessage = $value['maintenanceMessage'];
        $this->brandingTitle = $value['branding']['title'];
        $this->supportPhone = $value['branding']['supportPhone'];
        $this->supportUrl = $value['branding']['supportUrl'];
        $this->uiEnabled = (bool) $value['features']['uiEnabled'];
        $this->autoNavigation = (bool) $value['features']['autoNavigation'];
        $this->courses = array_values($value['courses']);
        $this->signingPublicKey = $setting->signing_public_key;
    }

    private function rules(): array
    {
        return [
            'enabled' => ['boolean'],
            'minVersion' => ['required', 'string', 'regex:/^\d+\.\d+\.\d+$/'],
            'pollIntervalSeconds' => ['required', 'integer', 'min:60', 'max:86400'],
            'maintenanceMessage' => ['nullable', 'string', 'max:500'],
            'brandingTitle' => ['required', 'string', 'max:100'],
            'supportPhone' => ['nullable', 'string', 'max:30'],
            'supportUrl' => ['nullable', 'url:http,https', 'max:500'],
            'uiEnabled' => ['boolean'],
            'autoNavigation' => ['boolean'],
            'courses' => ['array', 'max:50'],
            'courses.*.path' => ['required', 'string', 'regex:/^\/slides\/[a-z0-9\-]+$/', 'distinct'],
            'courses.*.label' => ['required', 'string', 'max:150'],
            'courses.*.enabled' => ['boolean'],
            'courses.*.priority' => ['required', 'integer', 'min:1', 'max:9999'],
        ];
    }

    public function render()
    {
        return view('livewire.extension-settings')
            ->layout('components.layouts.app', ['title' => 'Cấu hình Extension']);
    }
}
