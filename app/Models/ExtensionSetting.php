<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ExtensionSetting extends Model
{
    public const CONFIG_KEY = 'public_extension_config';

    protected $fillable = ['key', 'value', 'signing_public_key', 'signing_secret_key', 'updated_by'];

    protected function casts(): array
    {
        return [
            'value' => 'array',
            'signing_secret_key' => 'encrypted',
        ];
    }

    public static function current(): self
    {
        return self::query()->firstOrCreate(
            ['key' => self::CONFIG_KEY],
            ['value' => config('extension.defaults')],
        );
    }
}
