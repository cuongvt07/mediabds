<?php

namespace App\Models\Vault;

use Illuminate\Database\Eloquent\Model;

/**
 * Một "két" tích lũy cụ thể của user (bảng vault_vaults). Đặt tên class là
 * VaultAccount (không phải "Vault") để tránh đụng tên module/namespace.
 */
class VaultAccount extends Model
{
    protected $table = 'vault_vaults';

    protected $fillable = [
        'vault_user_id', 'type', 'name', 'interest_rate_yearly',
        'principal_amount', 'accrued_interest_amount', 'term_days',
        'matures_at', 'auto_compound', 'status',
    ];

    protected function casts(): array
    {
        return [
            'interest_rate_yearly' => 'decimal:2',
            'matures_at' => 'datetime',
            'auto_compound' => 'boolean',
        ];
    }

    public function vaultUser()
    {
        return $this->belongsTo(VaultUser::class);
    }

    public function ledgerEntries()
    {
        return $this->hasMany(VaultLedgerEntry::class, 'vault_id');
    }

    public function totalBalance(): int
    {
        return $this->principal_amount + $this->accrued_interest_amount;
    }

    /** Lãi dự tính mỗi ngày dựa trên số dư hiện tại (đồng, đã làm tròn). */
    public function estimatedDailyInterest(): int
    {
        return (int) round($this->totalBalance() * ((float) $this->interest_rate_yearly / 100) / 365);
    }
}
