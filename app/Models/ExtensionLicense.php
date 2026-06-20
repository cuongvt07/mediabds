<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ExtensionLicense extends Model
{
    protected $fillable = ['label', 'key_hash', 'key_hint', 'active', 'max_devices', 'expires_at', 'created_by'];

    protected function casts(): array
    {
        return ['active' => 'boolean', 'expires_at' => 'datetime'];
    }

    public function devices(): HasMany
    {
        return $this->hasMany(ExtensionDevice::class);
    }

    public function isUsable(): bool
    {
        return $this->active && (!$this->expires_at || $this->expires_at->isFuture());
    }
}
