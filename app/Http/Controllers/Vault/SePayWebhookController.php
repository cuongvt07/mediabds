<?php

namespace App\Http\Controllers\Vault;

use App\Models\SepaySetting;
use App\Models\Vault\VaultDepositRequest;
use App\Services\Vault\VaultLedgerService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Nhận webhook SePay khi có tiền chuyển vào tài khoản ngân hàng — đối soát
 * qua nội dung chuyển khoản (chứa payment_code, vd "VM123") để cộng đúng
 * tiền vào đúng két Vault, KHÔNG cần user tự bấm "xác nhận đã chuyển".
 *
 * BẢO MẬT (endpoint PUBLIC, không qua guard 'vault' — SePay không có Bearer
 * token của user):
 * - Xác thực bằng HMAC-SHA256 (X-SePay-Signature = "sha256=" + hex,
 *   X-SePay-Timestamp), secret lưu mã hoá trong DB (SepaySetting), KHÔNG
 *   bao giờ lộ ra ngoài trừ giá trị gốc admin tự sinh 1 lần để dán lên SePay.
 * - Chống replay: từ chối nếu timestamp lệch quá 5 phút so với giờ server.
 * - hash_equals() so sánh chữ ký — chống timing attack (không dùng ===).
 * - KHÔNG bao giờ tin số tiền/nội dung từ payload để tự suy ra vault_id hay
 *   user — chỉ dùng payment_code để TRA LẠI đúng 1 VaultDepositRequest đã
 *   tồn tại (tạo từ trước bởi chính user, qua API có auth), rồi cộng ĐÚNG
 *   amount đã lưu sẵn ở deposit đó (không dùng amount từ SePay gửi, tránh
 *   trường hợp payload bị giả mạo/sai lệch cộng nhầm số tiền khác).
 * - post() của VaultLedgerService tự idempotent theo idempotency_key — SePay
 *   gửi lại (retry) không cộng tiền 2 lần.
 */
class SePayWebhookController extends VaultBaseController
{
    private const MAX_TIMESTAMP_DRIFT_SECONDS = 300;

    public function handle(Request $request, VaultLedgerService $ledger)
    {
        $settings = SepaySetting::current();

        if (! $settings->enabled || ! $settings->webhook_secret_encrypted) {
            Log::warning('SePayWebhookController: webhook chưa được cấu hình/kích hoạt');
            return response()->json(['message' => 'Webhook chưa được cấu hình'], 503);
        }

        $rawBody = $request->getContent();
        $signature = $request->header('X-SePay-Signature', '');
        $timestamp = $request->header('X-SePay-Timestamp', '');

        if (! $this->verifySignature($rawBody, $timestamp, $signature, $settings->webhook_secret_encrypted)) {
            Log::warning('SePayWebhookController: chữ ký không hợp lệ hoặc timestamp hết hạn');
            return response()->json(['message' => 'Invalid signature'], 401);
        }

        $settings->update(['last_webhook_at' => now()]);

        $payload = $request->json()->all();

        // SePay tự trích mã thanh toán theo template cấu hình trên Console
        // (my.sepay.vn -> Cấu hình Công ty -> Cấu trúc mã thanh toán, prefix
        // "VM") và gắn sẵn vào field "code" của payload — ưu tiên đọc field
        // này. Fallback tự regex trên "content" chỉ để an toàn nếu field
        // "code" trống (chưa cấu hình template/không khớp phía SePay).
        $paymentCode = strtoupper((string) ($payload['code'] ?? ''));

        if ($paymentCode === '') {
            $content = (string) ($payload['content'] ?? '');
            if (preg_match('/VM\d+/i', $content, $matches)) {
                $paymentCode = strtoupper($matches[0]);
            }
        }

        if ($paymentCode === '' || ! preg_match('/^VM(\d+)$/', $paymentCode, $matches)) {
            Log::info('SePayWebhookController: không tìm thấy payment_code hợp lệ', ['payload' => $payload]);
            return response()->json(['message' => 'Không tìm thấy mã giao dịch, bỏ qua']);
        }
        $deposit = VaultDepositRequest::where('payment_code', $paymentCode)->first();

        if (! $deposit) {
            Log::warning('SePayWebhookController: payment_code không khớp lệnh nạp nào', ['payment_code' => $paymentCode]);
            return response()->json(['message' => 'Không tìm thấy lệnh nạp tiền tương ứng']);
        }

        if ($deposit->status === 'success') {
            // Đã xử lý trước đó (SePay retry hoặc 2 webhook cùng giao dịch) — trả 200 để SePay không retry nữa.
            return response()->json(['message' => 'Đã xử lý trước đó']);
        }

        if ($deposit->status !== 'pending_payment') {
            Log::warning('SePayWebhookController: lệnh nạp không ở trạng thái chờ thanh toán', [
                'deposit_id' => $deposit->id,
                'status' => $deposit->status,
            ]);
            return response()->json(['message' => 'Lệnh nạp tiền không ở trạng thái hợp lệ']);
        }

        // Số tiền chuyển thực tế PHẢI khớp đúng số tiền đã đăng ký khi tạo
        // lệnh — không tự cộng nếu lệch (vd chuyển thiếu/thừa), tránh cộng
        // sai số tiền vào két dựa theo dữ liệu từ bên ngoài.
        $transferAmount = (int) ($payload['transferAmount'] ?? $payload['amount'] ?? 0);
        if ($transferAmount !== $deposit->amount) {
            Log::warning('SePayWebhookController: số tiền chuyển khoản không khớp lệnh nạp', [
                'deposit_id' => $deposit->id,
                'expected' => $deposit->amount,
                'received' => $transferAmount,
            ]);
            return response()->json(['message' => 'Số tiền chuyển khoản không khớp, cần admin kiểm tra thủ công'], 422);
        }

        DB::transaction(function () use ($deposit, $ledger, $payload) {
            $ledger->post(
                vaultAccountId: $deposit->vault_id,
                type: 'deposit',
                amount: $deposit->amount,
                idempotencyKey: $deposit->idempotency_key,
                referenceType: VaultDepositRequest::class,
                referenceId: $deposit->id,
            );

            $deposit->update([
                'status' => 'success',
                'completed_at' => now(),
                'sepay_transaction_id' => (string) ($payload['id'] ?? $payload['referenceCode'] ?? ''),
            ]);
        });

        return response()->json(['message' => 'OK']);
    }

    private function verifySignature(string $rawBody, string $timestamp, string $signature, string $secret): bool
    {
        if ($timestamp === '' || $signature === '') {
            return false;
        }

        if (! ctype_digit($timestamp) || abs(time() - (int) $timestamp) > self::MAX_TIMESTAMP_DRIFT_SECONDS) {
            return false;
        }

        $expected = 'sha256=' . hash_hmac('sha256', $timestamp . '.' . $rawBody, $secret);

        return hash_equals($expected, $signature);
    }
}
