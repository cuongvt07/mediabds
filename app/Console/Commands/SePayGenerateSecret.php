<?php

namespace App\Console\Commands;

use App\Models\SepaySetting;
use Illuminate\Console\Command;

/**
 * Sinh secret key HMAC-SHA256 cho webhook SePay TRỰC TIẾP TRÊN SERVER đang
 * chạy (mỗi môi trường — local/production — phải có secret riêng, không copy
 * qua lại giữa các môi trường). Chạy 1 lần trên production sau khi deploy +
 * migrate, rồi copy giá trị in ra dán vào SePay Console.
 *
 * An toàn khi chạy nhiều lần: mặc định KHÔNG sinh lại nếu đã có secret (tránh
 * vô tình làm SePay Console đang lưu key cũ mất tác dụng) — dùng --force để
 * cố ý xoay key (rotate), sau đó phải cập nhật lại key mới trên SePay Console.
 */
class SePayGenerateSecret extends Command
{
    protected $signature = 'sepay:generate-secret {--force : Sinh key mới dù đã có, ghi đè key cũ}';

    protected $description = 'Sinh (hoặc xem lại) secret key HMAC-SHA256 cho webhook SePay, lưu mã hoá trong DB';

    public function handle(): int
    {
        $settings = SepaySetting::current();

        if ($settings->webhook_secret_encrypted && ! $this->option('force')) {
            $this->warn('Secret key đã tồn tại. Dùng --force nếu muốn sinh key MỚI (nhớ cập nhật lại trên SePay Console sau khi xoay key).');
            $this->line('Secret key hiện tại: ' . $settings->webhook_secret_encrypted);

            return self::SUCCESS;
        }

        $secret = bin2hex(random_bytes(32));
        $settings->update(['webhook_secret_encrypted' => $secret, 'enabled' => true]);

        $this->info('Đã sinh secret key mới, lưu mã hoá trong DB:');
        $this->line($secret);
        $this->newLine();
        $this->line('Copy giá trị trên, dán vào SePay Console (mục cấu hình HMAC-SHA256).');

        return self::SUCCESS;
    }
}
