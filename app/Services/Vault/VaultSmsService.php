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
     * Gửi 1 tin SMS. $to có thể ở dạng nội địa VN (0912345678) hoặc E.164
     * (+84912345678) — tự chuẩn hoá về E.164 trước khi gọi Twilio, vì API
     * bắt buộc định dạng này (thiếu sẽ trả lỗi 21211 Invalid 'To' Phone Number).
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
                'To' => $this->toE164($to),
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

    /**
     * Chuẩn hoá số điện thoại VN về E.164: "0912345678" -> "+84912345678".
     * Số đã ở dạng +84... hoặc 84... (không có 0 đầu) giữ nguyên/chỉ thêm dấu +.
     */
    private function toE164(string $phone): string
    {
        $digits = preg_replace('/\D/', '', $phone);

        if (str_starts_with($digits, '84')) {
            return '+' . $digits;
        }

        if (str_starts_with($digits, '0')) {
            return '+84' . substr($digits, 1);
        }

        return '+' . $digits;
    }
}
