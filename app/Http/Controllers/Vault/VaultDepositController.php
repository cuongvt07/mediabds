<?php

namespace App\Http\Controllers\Vault;

use App\Models\SepaySetting;
use App\Models\Vault\VaultAccount;
use App\Models\Vault\VaultDepositRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

/**
 * Nạp tiền qua SePay (VietQR): tạo lệnh ở trạng thái pending_payment kèm
 * payment_code duy nhất (nhúng vào nội dung chuyển khoản QR) — CHƯA cộng
 * tiền. SePayWebhookController mới là nơi thực sự cộng tiền vào két, sau khi
 * xác nhận chữ ký HMAC + khớp đúng payment_code với 1 lệnh đang chờ.
 *
 * store() ở đây KHÔNG bao giờ tự chuyển status sang success — chỉ tạo lệnh
 * và trả về payment_code/URL ảnh QR cho FE hiển thị. FE polling show() để
 * biết khi nào webhook đã xử lý xong (status chuyển sang success).
 */
class VaultDepositController extends VaultBaseController
{
    /** Lệnh nạp quá hạn này (chưa nhận được webhook) coi như hết hiệu lực — FE hiện đồng hồ đếm ngược theo mốc này. */
    public const EXPIRES_MINUTES = 15;

    public function store(Request $request)
    {
        $data = $request->validate([
            'vault_id' => 'required|integer',
            'amount' => 'required|integer|min:10000',
            'idempotency_key' => 'nullable|string|max:64',
        ]);

        $user = $request->user('vault');
        $vault = VaultAccount::where('id', $data['vault_id'])->where('vault_user_id', $user->id)->first();

        if (! $vault) {
            return $this->fail('Không tìm thấy két', 404);
        }
        if ($vault->status !== 'active') {
            return $this->fail('Két này hiện không thể nạp tiền (đã đáo hạn hoặc đã đóng)', 422);
        }

        $idempotencyKey = $data['idempotency_key'] ?? (string) Str::uuid();

        if ($existing = VaultDepositRequest::where('idempotency_key', $idempotencyKey)
            ->where('vault_user_id', $user->id)
            ->first()) {
            return $this->ok($this->transform($existing));
        }

        $deposit = VaultDepositRequest::create([
            'vault_user_id' => $user->id,
            'vault_id' => $vault->id,
            'amount' => (int) $data['amount'],
            'status' => 'pending_payment',
            'idempotency_key' => $idempotencyKey,
            'method' => 'sepay_qr',
        ]);

        // Mã tham chiếu NGẮN, duy nhất — sinh SAU khi có id thật (đảm bảo
        // không đụng dù 2 request cùng lúc tạo deposit), nhúng vào nội dung
        // chuyển khoản để SePay đối soát đúng giao dịch khi bắn webhook.
        // Prefix "VM" + hậu tố PHẢI khớp đúng template đã cấu hình trên SePay
        // Console (my.sepay.vn -> Cấu hình Công ty -> Cấu trúc mã thanh toán:
        // "VM" + số nguyên TỪ 3 ĐẾN 10 KÝ TỰ) — đệm số 0 phía trước LUÔN CỐ
        // ĐỊNH 5 chữ số (dư nhiều so với mức tối thiểu 3, support tới 99999
        // giao dịch mà độ dài mã không đổi) để id nhỏ vẫn đủ ký tự, nếu không
        // SePay sẽ KHÔNG nhận diện được mã trong nội dung CK — đã xảy ra thật
        // với các deposit id 1-99: "VM6", "VM14" chỉ có 1-2 ký tự hậu tố, dưới
        // mức tối thiểu 3 nên webhook không bao giờ được gọi.
        $deposit->update(['payment_code' => 'VM' . str_pad((string) $deposit->id, 5, '0', STR_PAD_LEFT)]);

        return $this->ok($this->transform($deposit), 'Vui lòng chuyển khoản theo mã QR để hoàn tất nạp tiền', 201);
    }

    public function show(Request $request, VaultDepositRequest $depositRequest)
    {
        abort_if($depositRequest->vault_user_id !== $request->user('vault')->id, 403);

        // Tự chuyển 'expired' khi FE polling phát hiện quá hạn 15 phút mà
        // vẫn chưa nhận webhook — KHÔNG trừ/hoàn tiền gì (chưa từng cộng
        // tiền ở bước này), chỉ đổi status để FE dừng đếm ngược/polling và
        // báo hết hạn. SePayWebhookController cũng tự chặn riêng nếu webhook
        // đến trễ sau khi đã hết hạn (xem isExpired()).
        if ($depositRequest->status === 'pending_payment' && $this->isExpired($depositRequest)) {
            $depositRequest->update(['status' => 'expired']);
        }

        return $this->ok($this->transform($depositRequest));
    }

    private function isExpired(VaultDepositRequest $d): bool
    {
        return $d->created_at->addMinutes(self::EXPIRES_MINUTES)->isPast();
    }

    private function transform(VaultDepositRequest $d): array
    {
        return [
            'id' => $d->id,
            'vaultId' => $d->vault_id,
            'amount' => $d->amount,
            'status' => $d->status,
            'paymentCode' => $d->payment_code,
            'qrImageUrl' => $d->payment_code ? $this->buildQrUrl($d) : null,
            'expiresAt' => $d->created_at->addMinutes(self::EXPIRES_MINUTES)->toIso8601String(),
            'completedAt' => $d->completed_at?->toIso8601String(),
        ];
    }

    /**
     * URL ảnh QR VietQR động — quét lên tự điền sẵn số tiền + nội dung CK
     * (chứa payment_code), người chuyển chỉ cần xác nhận trong app ngân hàng.
     * Thông tin tài khoản nhận đọc từ SepaySetting (CMS admin), KHÔNG hard-code.
     */
    private function buildQrUrl(VaultDepositRequest $d): ?string
    {
        $settings = SepaySetting::current();
        if (! $settings->bank_account_number || ! $settings->bank_name) {
            return null;
        }

        $query = http_build_query([
            'bank' => $settings->bank_name,
            'acc' => $settings->bank_account_number,
            'template' => 'qronly',
            'showinfo' => 'true',
            'fullacc' => 'true',
            'holder' => $settings->bank_account_name,
            'amount' => $d->amount,
            'des' => $d->payment_code,
        ]);

        return "https://vietqr.app/img?{$query}";
    }
}
