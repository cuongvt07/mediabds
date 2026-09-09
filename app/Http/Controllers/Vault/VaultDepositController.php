<?php

namespace App\Http\Controllers\Vault;

use App\Jobs\Vault\ProcessVaultDeposit;
use App\Models\Vault\VaultAccount;
use App\Models\Vault\VaultDepositRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

/**
 * Nạp tiền MÔ PHỎNG — dùng để test luồng rút tiền, không tích hợp cổng thanh
 * toán thật (xem CreateVaultWithdrawal / ProcessVaultWithdrawal cho ghi chú
 * tương đương ở luồng rút tiền).
 */
class VaultDepositController extends VaultBaseController
{
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

        $idempotencyKey = $data['idempotency_key'] ?? (string) Str::uuid();

        if ($existing = VaultDepositRequest::where('idempotency_key', $idempotencyKey)->first()) {
            return $this->ok($this->transform($existing));
        }

        $deposit = VaultDepositRequest::create([
            'vault_user_id' => $user->id,
            'vault_id' => $vault->id,
            'amount' => (int) $data['amount'],
            'status' => 'pending',
            'idempotency_key' => $idempotencyKey,
            'note' => 'Mã GD #' . random_int(10000, 99999),
        ]);

        ProcessVaultDeposit::dispatch($deposit)->onQueue('vault');

        return $this->ok($this->transform($deposit), 'Yêu cầu nạp tiền đã được tạo', 201);
    }

    public function show(Request $request, VaultDepositRequest $depositRequest)
    {
        abort_if($depositRequest->vault_user_id !== $request->user('vault')->id, 403);

        return $this->ok($this->transform($depositRequest));
    }

    private function transform(VaultDepositRequest $d): array
    {
        return [
            'id' => $d->id,
            'vaultId' => $d->vault_id,
            'amount' => $d->amount,
            'status' => $d->status,
            'note' => $d->note,
            'completedAt' => $d->completed_at?->toIso8601String(),
        ];
    }
}
