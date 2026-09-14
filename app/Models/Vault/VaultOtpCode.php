<?php

namespace App\Models\Vault;

use Illuminate\Database\Eloquent\Model;

class VaultOtpCode extends Model
{
    protected $table = 'vault_otp_codes';

    protected $fillable = [
        'vault_user_id', 'purpose', 'phone', 'code_hash', 'reference_id',
        'attempts', 'expires_at', 'consumed_at',
    ];

    // Không bao giờ xuất code_hash ra API.
    protected $hidden = ['code_hash'];

    protected function casts(): array
    {
        return [
            'expires_at' => 'datetime',
            'consumed_at' => 'datetime',
        ];
    }

    public function vaultUser()
    {
        return $this->belongsTo(VaultUser::class);
    }

    public function isExpired(): bool
    {
        return $this->expires_at->isPast();
    }

    public function isConsumed(): bool
    {
        return $this->consumed_at !== null;
    }
}
