<?php

namespace App\Models\Vault;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

/**
 * Module Vault — user HOÀN TOÀN ĐỘC LẬP với App\Models\User (site BĐS chính).
 * Không có quan hệ/khóa ngoại nào giữa 2 bảng users và vault_users.
 */
class VaultUser extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $table = 'vault_users';

    protected $fillable = [
        'name', 'phone', 'email', 'password', 'avatar_url', 'vip_tier',
        'vault_code', 'referral_code', 'referred_by_id', 'ekyc_level',
        'face_id_enabled', 'pin_code_hash', 'pin_set_at', 'phone_verified_at',
        'last_login_at',
    ];

    protected $hidden = [
        'password', 'pin_code_hash', 'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'password' => 'hashed',
            'face_id_enabled' => 'boolean',
            'pin_set_at' => 'datetime',
            'phone_verified_at' => 'datetime',
            'last_login_at' => 'datetime',
        ];
    }

    public function bankAccounts()
    {
        return $this->hasMany(VaultBankAccount::class);
    }

    public function vaults()
    {
        return $this->hasMany(VaultAccount::class);
    }

    public function ledgerEntries()
    {
        return $this->hasMany(VaultLedgerEntry::class);
    }

    public function withdrawalRequests()
    {
        return $this->hasMany(VaultWithdrawalRequest::class);
    }

    public function depositRequests()
    {
        return $this->hasMany(VaultDepositRequest::class);
    }

    public function referrer()
    {
        return $this->belongsTo(VaultUser::class, 'referred_by_id');
    }

    public function dailyWithdrawalLimit(): int
    {
        return match ($this->vip_tier) {
            'vip_gold' => 500_000_000,
            'vip_silver' => 150_000_000,
            default => 50_000_000,
        };
    }
}
