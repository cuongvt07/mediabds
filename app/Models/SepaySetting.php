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

    /**
     * Giá trị mặc định khi bảng chưa từng được cấu hình (vd production mới
     * deploy, chưa ai chạy lệnh set dữ liệu) — để trang QR nạp tiền LUÔN
     * hiển thị được ngay, không phụ thuộc phải chạy tinker/CMS trước. Admin
     * vẫn có thể đổi lại sau qua /vault-dashboard (tab SePay) hoặc tinker;
     * giá trị đã lưu trong DB luôn được ưu tiên hơn default này.
     */
    private const DEFAULT_BANK_NAME = 'MBBank';
    private const DEFAULT_BANK_ACCOUNT_NUMBER = '0962464506';
    private const DEFAULT_BANK_ACCOUNT_NAME = 'VO XUAN PHONG';

    public static function current(): self
    {
        $settings = self::query()->firstOrCreate(['id' => 1], [
            'bank_name' => self::DEFAULT_BANK_NAME,
            'bank_account_number' => self::DEFAULT_BANK_ACCOUNT_NUMBER,
            'bank_account_name' => self::DEFAULT_BANK_ACCOUNT_NAME,
        ]);

        // Dòng đã tồn tại từ trước (vd tạo bởi lệnh sepay:generate-secret,
        // không kèm default bank) nhưng vẫn thiếu thông tin ngân hàng — tự
        // vá lại 1 lần, KHÔNG ghi đè nếu admin đã từng set giá trị khác rồi.
        if (! $settings->bank_name || ! $settings->bank_account_number) {
            $settings->update([
                'bank_name' => $settings->bank_name ?: self::DEFAULT_BANK_NAME,
                'bank_account_number' => $settings->bank_account_number ?: self::DEFAULT_BANK_ACCOUNT_NUMBER,
                'bank_account_name' => $settings->bank_account_name ?: self::DEFAULT_BANK_ACCOUNT_NAME,
            ]);
        }

        return $settings;
    }
}
