<?php

namespace App\Services\Vault;

use App\Models\Vault\VaultAccount;
use App\Models\Vault\VaultLedgerEntry;
use DomainException;
use Illuminate\Support\Facades\DB;

/**
 * Nguồn sự thật duy nhất cho số dư két: mọi cộng/trừ PHẢI đi qua service này,
 * KHÔNG bao giờ update trực tiếp vault_vaults.principal_amount ở nơi khác.
 *
 * post() luôn chạy trong transaction + lock row két (SELECT ... FOR UPDATE)
 * để tránh 2 request cùng lúc đọc số dư cũ rồi ghi đè lẫn nhau.
 */
class VaultLedgerService
{
    /**
     * Ghi 1 bút toán và cập nhật số dư két. $amount dương = cộng, âm = trừ.
     * Idempotent: nếu idempotencyKey đã tồn tại, trả về bút toán cũ, KHÔNG ghi lại.
     *
     * @throws DomainException nếu trừ vượt quá số dư khả dụng.
     */
    public function post(
        int $vaultAccountId,
        string $type,
        int $amount,
        string $idempotencyKey,
        ?string $referenceType = null,
        ?int $referenceId = null,
        array $meta = [],
    ): VaultLedgerEntry {
        if ($existing = VaultLedgerEntry::where('idempotency_key', $idempotencyKey)->first()) {
            return $existing;
        }

        return DB::transaction(function () use ($vaultAccountId, $type, $amount, $idempotencyKey, $referenceType, $referenceId, $meta) {
            /** @var VaultAccount $vault */
            $vault = VaultAccount::where('id', $vaultAccountId)->lockForUpdate()->firstOrFail();

            $currentBalance = $vault->principal_amount + $vault->accrued_interest_amount;
            $newBalance = $currentBalance + $amount;

            if ($newBalance < 0) {
                throw new DomainException('Số dư két không đủ để thực hiện giao dịch này');
            }

            // Lãi lũy kế cộng riêng vào accrued_interest_amount; các loại khác
            // (nạp/rút/hoàn tiền/thưởng) cộng thẳng vào gốc.
            if ($type === 'interest') {
                $vault->accrued_interest_amount += $amount;
            } else {
                $vault->principal_amount += $amount;
            }
            $vault->save();

            return VaultLedgerEntry::create([
                'vault_user_id' => $vault->vault_user_id,
                'vault_id' => $vault->id,
                'type' => $type,
                'amount' => $amount,
                'balance_after' => $newBalance,
                'idempotency_key' => $idempotencyKey,
                'reference_type' => $referenceType,
                'reference_id' => $referenceId,
                'meta' => $meta,
            ]);
        });
    }

    /** Đảo ngược 1 bút toán đã ghi (vd hoàn tiền khi rút thất bại). Idempotent qua $idempotencyKey riêng. */
    public function reverse(VaultLedgerEntry $original, string $idempotencyKey): VaultLedgerEntry
    {
        return $this->post(
            vaultAccountId: $original->vault_id,
            type: $original->type . '_refund',
            amount: -$original->amount,
            idempotencyKey: $idempotencyKey,
            referenceType: $original->reference_type,
            referenceId: $original->reference_id,
            meta: ['reversal_of_id' => $original->id],
        );
    }
}
