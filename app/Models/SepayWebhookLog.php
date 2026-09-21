<?php

namespace App\Models;

use App\Models\Vault\VaultDepositRequest;
use Illuminate\Database\Eloquent\Model;

/**
 * Log audit MỌI request webhook SePay — xem SePayWebhookController. Chỉ đọc
 * qua CMS (VaultDeposits/VaultSepaySettings), không có logic nghiệp vụ nào
 * phụ thuộc vào bảng này.
 */
class SepayWebhookLog extends Model
{
    protected $table = 'sepay_webhook_logs';

    protected $fillable = [
        'vault_deposit_request_id',
        'payment_code',
        'outcome',
        'reason',
        'payload',
        'signature_header',
        'sepay_transaction_id',
        'ip_address',
    ];

    protected function casts(): array
    {
        return [
            'payload' => 'array',
        ];
    }

    public function depositRequest()
    {
        return $this->belongsTo(VaultDepositRequest::class, 'vault_deposit_request_id');
    }
}
