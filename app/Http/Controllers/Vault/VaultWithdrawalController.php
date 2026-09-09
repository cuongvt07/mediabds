<?php

namespace App\Http\Controllers\Vault;

use App\Actions\Vault\CreateVaultWithdrawal;
use App\Models\Vault\VaultAccount;
use App\Models\Vault\VaultBankAccount;
use App\Models\Vault\VaultWithdrawalRequest;
use DomainException;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class VaultWithdrawalController extends VaultBaseController
{
    public function store(Request $request, CreateVaultWithdrawal $action)
    {
        $data = $request->validate([
            'vault_id' => 'required|integer',
            'bank_account_id' => 'required|integer',
            'amount' => 'required|integer|min:50000',
            'idempotency_key' => 'nullable|string|max:64',
        ]);

        $user = $request->user('vault');
        $vault = VaultAccount::where('id', $data['vault_id'])->where('vault_user_id', $user->id)->first();
        $bankAccount = VaultBankAccount::where('id', $data['bank_account_id'])->where('vault_user_id', $user->id)->first();

        if (! $vault) {
            return $this->fail('Không tìm thấy két', 404);
        }
        if (! $bankAccount) {
            return $this->fail('Không tìm thấy tài khoản ngân hàng', 404);
        }

        try {
            $withdrawal = $action->run(
                user: $user,
                vault: $vault,
                bankAccount: $bankAccount,
                amount: (int) $data['amount'],
                idempotencyKey: $data['idempotency_key'] ?? (string) Str::uuid(),
            );
        } catch (DomainException $e) {
            return $this->fail($e->getMessage(), 422);
        }

        return $this->ok($this->transform($withdrawal), 'Yêu cầu rút tiền đã được tạo', 201);
    }

    public function show(Request $request, VaultWithdrawalRequest $withdrawalRequest)
    {
        abort_if($withdrawalRequest->vault_user_id !== $request->user('vault')->id, 403);

        return $this->ok($this->transform($withdrawalRequest));
    }

    public function index(Request $request)
    {
        $items = VaultWithdrawalRequest::where('vault_user_id', $request->user('vault')->id)
            ->orderByDesc('created_at')
            ->limit(30)
            ->get();

        return $this->ok($items->map(fn ($w) => $this->transform($w))->values());
    }

    private function transform(VaultWithdrawalRequest $w): array
    {
        return [
            'id' => $w->id,
            'vaultId' => $w->vault_id,
            'bankAccountId' => $w->vault_bank_account_id,
            'amount' => $w->amount,
            'status' => $w->status,
            'gatewayRef' => $w->gateway_ref,
            'failureReason' => $w->failure_reason,
            'requestedAt' => $w->requested_at?->toIso8601String(),
            'completedAt' => $w->completed_at?->toIso8601String(),
        ];
    }
}
