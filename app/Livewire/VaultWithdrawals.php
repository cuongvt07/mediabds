<?php

namespace App\Livewire;

use App\Models\Vault\VaultWithdrawalRequest;
use Livewire\Component;
use Livewire\WithPagination;

/** Lịch sử rút tiền module Vault — chỉ đọc, không có hành động ghi. */
class VaultWithdrawals extends Component
{
    use WithPagination;

    public function render()
    {
        return view('livewire.vault-withdrawals', [
            'withdrawals' => VaultWithdrawalRequest::with('vaultUser:id,name,phone,vault_code')
                ->orderByDesc('created_at')
                ->paginate(20),
        ])->layout('components.layouts.vault-cms', [
            'title' => 'Lịch sử rút tiền',
        ]);
    }
}
