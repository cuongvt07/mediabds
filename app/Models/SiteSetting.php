<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;

class SiteSetting extends Model
{
    public const CONFIG_KEY = 'site.settings';

    protected $fillable = ['key', 'value', 'updated_by'];

    protected function casts(): array
    {
        return [
            'value' => 'array',
        ];
    }

    public static function current(): self
    {
        return self::query()->firstOrCreate(
            ['key' => self::CONFIG_KEY],
            ['value' => config('site.defaults')],
        );
    }

    /**
     * Settings merged over defaults so newly added keys always resolve.
     */
    public static function values(): array
    {
        $defaults = config('site.defaults');
        $stored = static::current()->value ?: [];

        return array_replace_recursive($defaults, $stored);
    }

    /**
     * Dot-path getter against the merged settings, e.g. get('watermark.enabled').
     */
    public static function get(string $path, mixed $default = null): mixed
    {
        return Arr::get(static::values(), $path, $default);
    }
}
