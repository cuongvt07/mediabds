<?php

namespace App\Livewire;

use App\Models\Vault\VaultDepositRequest;
use Livewire\Component;
use Livewire\WithPagination;

/** Lịch sử nạp tiền module Vault — chỉ đọc, không có hành động ghi. */
class VaultDeposits extends Component
{
    use WithPagination;

    public function render()
    {
        return view('livewire.vault-deposits', [
            'deposits' => VaultDepositRequest::with('vaultUser:id,name,phone,vault_code')
                ->orderByDesc('created_at')
                ->paginate(20),
        ])->layout('components.layouts.vault-cms', [
            'title' => 'Lịch sử nạp tiền',
        ]);
    }
}
