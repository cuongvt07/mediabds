<?php

namespace App\Models\Vault;

use Illuminate\Database\Eloquent\Model;

class VaultDepositRequest extends Model
{
    protected $table = 'vault_deposit_requests';

    protected $fillable = [
        'vault_user_id', 'vault_id', 'amount', 'status',
        'idempotency_key', 'method', 'note', 'completed_at',
    ];

    protected function casts(): array
    {
        return [
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
}
