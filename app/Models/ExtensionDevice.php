<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ExtensionDevice extends Model
{
    protected $fillable = ['extension_license_id', 'device_hash', 'device_name', 'token_hash', 'last_seen_at', 'revoked_at'];

    protected function casts(): array
    {
        return ['last_seen_at' => 'datetime', 'revoked_at' => 'datetime'];
    }

    public function license(): BelongsTo
    {
        return $this->belongsTo(ExtensionLicense::class, 'extension_license_id');
    }

    public function isUsable(): bool
    {
        return !$this->revoked_at && $this->license?->isUsable();
    }
}
