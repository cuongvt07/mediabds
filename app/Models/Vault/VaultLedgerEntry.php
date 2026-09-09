<?php

namespace App\Models\Vault;

use Illuminate\Database\Eloquent\Model;

class VaultLedgerEntry extends Model
{
    protected $table = 'vault_ledger_entries';

    protected $fillable = [
        'vault_user_id', 'vault_id', 'type', 'amount', 'balance_after',
        'idempotency_key', 'reversal_of_id', 'reference_type', 'reference_id', 'meta',
    ];

    protected function casts(): array
    {
        return [
            'meta' => 'array',
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
}
