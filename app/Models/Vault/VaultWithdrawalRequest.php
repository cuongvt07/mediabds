<?php

namespace App\Models\Vault;

use Illuminate\Database\Eloquent\Model;

class VaultWithdrawalRequest extends Model
{
    protected $table = 'vault_withdrawal_requests';

    protected $fillable = [
        'vault_user_id', 'vault_id', 'vault_bank_account_id', 'amount', 'status',
        'idempotency_key', 'gateway_ref', 'failure_reason', 'requested_at', 'completed_at',
    ];

    protected function casts(): array
    {
        return [
            'requested_at' => 'datetime',
            'completed_at' => 'datetime',
        ];
    }

    public function vaultUser()
    {
        return $this->belongsTo(VaultUser::class);
    }

    public function vaultAccount()
    {
        return $this->belongsTo(VaultAccount::class, 'vault_id');
    }

    public function bankAccount()
    {
        return $this->belongsTo(VaultBankAccount::class, 'vault_bank_account_id');
    }
}
