<?php

namespace App\Http\Controllers\Vault;

use App\Models\Vault\VaultBankAccount;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class VaultBankAccountController extends VaultBaseController
{
    private const BANKS = [
        'VCB' => 'Vietcombank',
        'TCB' => 'Techcombank',
        'MB' => 'MB Bank',
        'ACB' => 'ACB',
        'BIDV' => 'BIDV',
        'VTB' => 'VietinBank',
        'TPB' => 'TPBank',
        'VPB' => 'VPBank',
    ];

    public function index(Request $request)
    {
        $accounts = $request->user('vault')->bankAccounts()->orderByDesc('is_default')->get();

        return $this->ok($accounts->map(fn ($a) => $this->transform($a))->values());
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'bank_code' => ['required', 'string', 'in:' . implode(',', array_keys(self::BANKS))],
            'account_number' => 'required|string|min:6|max:20',
            'account_name' => 'required|string|max:100',
        ]);

        $user = $request->user('vault');
        $accountNumber = $data['account_number'];

        $bankAccount = DB::transaction(function () use ($user, $data, $accountNumber) {
            // Lock toàn bộ bank account hiện có của user trong transaction để
            // 2 request "thêm tài khoản đầu tiên" đồng thời không thể cùng
            // thấy count()===0 rồi cùng tự đặt is_default=true.
            $isFirst = VaultBankAccount::where('vault_user_id', $user->id)->lockForUpdate()->count() === 0;

            return VaultBankAccount::create([
                'vault_user_id' => $user->id,
                'bank_code' => $data['bank_code'],
                'bank_name' => self::BANKS[$data['bank_code']],
                'account_number_encrypted' => $accountNumber,
                'masked_number' => '*' . substr($accountNumber, -4),
                'account_name' => mb_strtoupper($data['account_name']),
                'is_default' => $isFirst,
                'is_verified' => true, // mô phỏng: coi như xác thực tức thì
            ]);
        });

        return $this->ok($this->transform($bankAccount), 'Đã thêm tài khoản ngân hàng', 201);
    }

    public function setDefault(Request $request, VaultBankAccount $bankAccount)
    {
        $this->authorizeOwnership($request, $bankAccount);
        $userId = $request->user('vault')->id;

        DB::transaction(function () use ($userId, $bankAccount) {
            // Lock toàn bộ hàng của user trước khi update để 2 request setDefault
            // đồng thời (cho 2 bank account khác nhau) không thể xen kẽ ghi đè
            // nhau — request thứ 2 phải đợi request thứ 1 commit xong.
            VaultBankAccount::where('vault_user_id', $userId)->lockForUpdate()->get();

            VaultBankAccount::where('vault_user_id', $userId)->update(['is_default' => false]);
            $bankAccount->update(['is_default' => true]);
        });

        return $this->ok(null, 'Đã đặt làm tài khoản mặc định');
    }

    public function destroy(Request $request, VaultBankAccount $bankAccount)
    {
        $this->authorizeOwnership($request, $bankAccount);
        $bankAccount->delete();

        return $this->ok(null, 'Đã xoá tài khoản ngân hàng');
    }

    private function authorizeOwnership(Request $request, VaultBankAccount $bankAccount): void
    {
        abort_if($bankAccount->vault_user_id !== $request->user('vault')->id, 403);
    }

    private function transform(VaultBankAccount $a): array
    {
        return [
            'id' => $a->id,
            'bankCode' => $a->bank_code,
            'bankName' => $a->bank_name,
            'maskedNumber' => $a->masked_number,
            'accountName' => $a->account_name,
            'isDefault' => $a->is_default,
            'isVerified' => $a->is_verified,
        ];
    }
}
