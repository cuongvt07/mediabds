<?php

namespace App\Services\Vault;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use RuntimeException;

/**
 * Gửi SMS qua Twilio REST API (Messages resource) — dùng Http facade có sẵn
 * của Laravel, KHÔNG cần cài twilio/sdk (SDK chính thức khá nặng cho 1 thao
 * tác POST đơn giản; xem https://www.twilio.com/docs/messaging/quickstart).
 *
 * API: POST https://api.twilio.com/2010-04-01/Accounts/{AccountSid}/Messages.json
 * Auth: HTTP Basic (AccountSid làm username, AuthToken làm password).
 *
 * CHƯA CÓ KEY (TWILIO_ACCOUNT_SID/AUTH_TOKEN/FROM_NUMBER rỗng trong .env):
 * send() throw RuntimeException rõ ràng — KHÔNG âm thầm giả vờ gửi thành
 * công. Khi bạn điền key vào .env, service tự hoạt động ngay, không cần sửa
 * code nào ở đây hay ở nơi gọi.
 */
class VaultSmsService
{
    private const TWILIO_API_BASE = 'https://api.twilio.com/2010-04-01';

    public function isConfigured(): bool
    {
        return filled(config('services.twilio.account_sid'))
            && filled(config('services.twilio.auth_token'))
            && filled(config('services.twilio.from_number'));
    }

    /**
     * Gửi 1 tin SMS. $to phải ở định dạng E.164 (vd +84912345678).
     *
     * @throws RuntimeException nếu chưa cấu hình Twilio hoặc Twilio trả lỗi.
     */
    public function send(string $to, string $body): void
    {
        if (! $this->isConfigured()) {
            throw new RuntimeException(
                'Twilio chưa được cấu hình (thiếu TWILIO_ACCOUNT_SID/AUTH_TOKEN/FROM_NUMBER trong .env). '
                . 'Không thể gửi OTP SMS cho tới khi có đủ thông tin.'
            );
        }

        $accountSid = config('services.twilio.account_sid');
        $authToken = config('services.twilio.auth_token');
        $from = config('services.twilio.from_number');

        $response = Http::asForm()
            ->withBasicAuth($accountSid, $authToken)
            ->post(self::TWILIO_API_BASE . "/Accounts/{$accountSid}/Messages.json", [
                'To' => $to,
                'From' => $from,
                'Body' => $body,
            ]);

        if ($response->failed()) {
            // KHÔNG log nội dung SMS (có thể chứa mã OTP) — chỉ log mã lỗi
            // Twilio để debug mà không làm lộ dữ liệu nhạy cảm ra file log.
            Log::error('VaultSmsService: Twilio trả lỗi', [
                'status' => $response->status(),
                'twilio_error_code' => $response->json('code'),
                'twilio_error_message' => $response->json('message'),
            ]);

            throw new RuntimeException('Gửi SMS thất bại, vui lòng thử lại sau.');
        }
    }
}
