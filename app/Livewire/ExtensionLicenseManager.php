<?php

namespace App\Livewire;

use App\Models\ExtensionDevice;
use App\Models\ExtensionLicense;
use Illuminate\Support\Str;
use Livewire\Component;

class ExtensionLicenseManager extends Component
{
    public string $label = '';
    public int $maxDevices = 1;
    public ?string $expiresAt = null;
    public ?string $newLicenseKey = null;

    public function createLicense(): void
    {
        $data = $this->validate([
            'label' => ['required', 'string', 'max:120'],
            'maxDevices' => ['required', 'integer', 'min:1', 'max:100'],
            'expiresAt' => ['nullable', 'date', 'after:now'],
        ]);

        $plainKey = 'CUONG-'.strtoupper(Str::random(8)).'-'.strtoupper(Str::random(8)).'-'.strtoupper(Str::random(8));
        ExtensionLicense::create([
            'label' => $data['label'],
            'key_hash' => hash('sha256', $plainKey),
            'key_hint' => substr($plainKey, 0, 14).'…'.substr($plainKey, -4),
            'active' => true,
            'max_devices' => $data['maxDevices'],
            'expires_at' => $data['expiresAt'] ?: null,
            'created_by' => auth()->id(),
        ]);

        $this->newLicenseKey = $plainKey;
        $this->reset('label', 'expiresAt');
        $this->maxDevices = 1;
    }

    public function toggleLicense(int $id): void
    {
        $license = ExtensionLicense::findOrFail($id);
        $license->update(['active' => !$license->active]);
    }

    public function revokeDevice(int $id): void
    {
        ExtensionDevice::findOrFail($id)->update(['revoked_at' => now(), 'token_hash' => null]);
    }

    public function render()
    {
        return view('livewire.extension-license-manager', [
            'licenses' => ExtensionLicense::with(['devices' => fn ($q) => $q->latest()])->latest()->get(),
        ])->layout('components.layouts.app', ['title' => 'License Extension']);
    }
}
