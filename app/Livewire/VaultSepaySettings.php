<?php

namespace App\Livewire;

use App\Models\SepaySetting;
use Livewire\Component;

/**
 * Cấu hình webhook SePay — bật/tắt, xem lại/xoay secret key, thông tin tài
 * khoản nhận tiền. BẢO MẬT: mọi hành động ghi tự đọc lại SepaySetting::current()
 * tại thời điểm xử lý, không nhận Model trực tiếp từ Livewire property.
 */
class VaultSepaySettings extends Component
{
    public ?string $revealedSecret = null;

    public function toggleEnabled(): void
    {
        $settings = SepaySetting::current();
        $settings->update(['enabled' => ! $settings->enabled]);
    }

    /** Hiện lại secret key hiện tại (đã giải mã) để admin copy dán lại lên SePay Console nếu cần. */
    public function revealSecret(): void
    {
        $this->revealedSecret = SepaySetting::current()->webhook_secret_encrypted;
    }

    public function hideSecret(): void
    {
        $this->revealedSecret = null;
    }

    /** Xoay secret key mới — PHẢI cập nhật lại trên SePay Console sau khi bấm. */
    public function rotateSecret(): void
    {
        $settings = SepaySetting::current();
        $settings->update(['webhook_secret_encrypted' => bin2hex(random_bytes(32))]);
        $this->revealedSecret = $settings->fresh()->webhook_secret_encrypted;
    }

    public function render()
    {
        return view('livewire.vault-sepay-settings', [
            'sepaySettings' => SepaySetting::current(),
        ])->layout('components.layouts.vault-cms', [
            'title' => 'Cấu hình SePay',
        ]);
    }
}
