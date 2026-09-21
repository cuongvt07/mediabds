<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Cấu hình webhook SePay — bảng SINGLETON (luôn 1 dòng), quản lý qua CMS
 * admin (WebsiteAdmin, tab "sepay"). webhook_secret_encrypted mã hoá bằng
 * Eloquent `encrypted` cast (khoá APP_KEY) — cùng convention với
 * ExtensionSetting::signing_secret_key, KHÔNG dùng chung JSON blob của
 * SiteSetting (không mã hoá được field con trong JSON).
 */
class SepaySetting extends Model
{
    protected $table = 'sepay_settings';

    protected $fillable = [
        'webhook_secret_encrypted',
        'bank_account_number',
        'bank_name',
        'bank_account_name',
        'enabled',
        'last_webhook_at',
        'updated_by_user_id',
    ];

    protected $hidden = ['webhook_secret_encrypted'];

    protected function casts(): array
    {
        return [
            'webhook_secret_encrypted' => 'encrypted',
            'enabled' => 'boolean',
            'last_webhook_at' => 'datetime',
        ];
    }

    public static function current(): self
    {
        return self::query()->firstOrCreate(['id' => 1]);
    }
}
