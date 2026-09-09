<?php

namespace App\Models\Vault;

use Illuminate\Database\Eloquent\Model;

class VaultBankAccount extends Model
{
    protected $table = 'vault_bank_accounts';

    protected $fillable = [
        'vault_user_id', 'bank_code', 'bank_name', 'account_number_encrypted',
        'masked_number', 'account_name', 'is_default', 'is_verified',
    ];

    // account_number_encrypted không bao giờ xuất ra API — chỉ dùng nội bộ.
    protected $hidden = ['account_number_encrypted'];

    protected function casts(): array
    {
        return [
            'account_number_encrypted' => 'encrypted',
            'is_default' => 'boolean',
            'is_verified' => 'boolean',
        ];
    }

    public function vaultUser()
    {
        return $this->belongsTo(VaultUser::class);
    }
}
