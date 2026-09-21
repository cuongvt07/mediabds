<div class="max-w-[1400px] w-full mx-auto">
    <div class="mb-6 flex items-center gap-3">
        <a href="{{ route('vault.dashboard') }}" class="flex h-9 w-9 items-center justify-center rounded-xl bg-white border border-slate-200 text-slate-500 hover:text-slate-700">
            <i class="fa-solid fa-arrow-left"></i>
        </a>
        <div>
            <h1 class="text-2xl font-bold text-slate-900">{{ $vaultUser->name }}</h1>
            <p class="text-slate-500 mt-1 text-sm mono">{{ $vaultUser->phone }} · {{ $vaultUser->vault_code }}</p>
        </div>
        <span class="ml-auto px-3 py-1.5 rounded-lg text-xs font-bold {{ $vaultUser->phone_verified_at ? 'bg-emerald-100 text-emerald-700' : 'bg-slate-100 text-slate-500' }}">
            eKYC cấp {{ $vaultUser->ekyc_level }}
        </span>
    </div>

    {{-- KPI --}}
    <div class="grid grid-cols-2 md:grid-cols-4 gap-3 mb-6">
        <div class="bg-white rounded-2xl border border-slate-200 p-4">
            <p class="text-xs text-slate-400 font-semibold uppercase">Tổng gốc trong két</p>
            <p class="text-xl font-bold text-slate-900 mt-1 mono">{{ number_format($totalPrincipal) }}đ</p>
        </div>
        <div class="bg-white rounded-2xl border border-slate-200 p-4">
            <p class="text-xs text-slate-400 font-semibold uppercase">Lãi lũy kế</p>
            <p class="text-xl font-bold text-emerald-600 mt-1 mono">{{ number_format($totalInterest) }}đ</p>
        </div>
        <div class="bg-white rounded-2xl border border-slate-200 p-4">
            <p class="text-xs text-slate-400 font-semibold uppercase">Lệnh nạp tiền</p>
            <p class="text-xl font-bold text-slate-900 mt-1 mono">{{ number_format($depositsCount) }}</p>
        </div>
        <div class="bg-white rounded-2xl border border-slate-200 p-4">
            <p class="text-xs text-slate-400 font-semibold uppercase">Lệnh rút tiền</p>
            <p class="text-xl font-bold text-slate-900 mt-1 mono">{{ number_format($withdrawalsCount) }}</p>
        </div>
    </div>

    {{-- Két --}}
    <div class="bg-white rounded-2xl border border-slate-200 overflow-hidden mb-6">
        <div class="px-4 py-3 border-b border-slate-100 font-bold text-slate-900">Két ({{ $vaultUser->vaults->count() }})</div>
        <table class="w-full text-sm">
            <thead class="bg-slate-50 text-slate-500 text-xs uppercase font-semibold">
                <tr>
                    <th class="text-left px-4 py-3">Tên két</th>
                    <th class="text-left px-4 py-3">Loại</th>
                    <th class="text-left px-4 py-3">Lãi suất/năm</th>
                    <th class="text-left px-4 py-3">Gốc</th>
                    <th class="text-left px-4 py-3">Lãi lũy kế</th>
                    <th class="text-left px-4 py-3">Trạng thái</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse ($vaultUser->vaults as $v)
                    <tr>
                        <td class="px-4 py-3 font-semibold text-slate-900">{{ $v->name }}</td>
                        <td class="px-4 py-3">{{ $v->type === 'flexible' ? 'Linh hoạt' : 'Kỳ hạn' }}</td>
                        <td class="px-4 py-3 mono">{{ $v->interest_rate_yearly }}%</td>
                        <td class="px-4 py-3 mono">{{ number_format($v->principal_amount) }}đ</td>
                        <td class="px-4 py-3 mono text-emerald-600">{{ number_format($v->accrued_interest_amount) }}đ</td>
                        <td class="px-4 py-3">
                            <span class="px-2 py-1 rounded-lg text-xs font-bold {{ $v->status === 'active' ? 'bg-emerald-100 text-emerald-700' : 'bg-slate-100 text-slate-500' }}">{{ $v->status }}</span>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="text-center py-8 text-slate-400">Chưa có két nào.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- TK ngân hàng --}}
    <div class="bg-white rounded-2xl border border-slate-200 overflow-hidden mb-6">
        <div class="px-4 py-3 border-b border-slate-100 font-bold text-slate-900">Tài khoản ngân hàng ({{ $vaultUser->bankAccounts->count() }})</div>
        <table class="w-full text-sm">
            <thead class="bg-slate-50 text-slate-500 text-xs uppercase font-semibold">
                <tr>
                    <th class="text-left px-4 py-3">Ngân hàng</th>
                    <th class="text-left px-4 py-3">Số TK</th>
                    <th class="text-left px-4 py-3">Chủ TK</th>
                    <th class="text-left px-4 py-3">Mặc định</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse ($vaultUser->bankAccounts as $b)
                    <tr>
                        <td class="px-4 py-3 font-semibold text-slate-900">{{ $b->bank_name }}</td>
                        <td class="px-4 py-3 mono">{{ $b->masked_number }}</td>
                        <td class="px-4 py-3">{{ $b->account_name }}</td>
                        <td class="px-4 py-3">{{ $b->is_default ? '✓' : '' }}</td>
                    </tr>
                @empty
                    <tr><td colspan="4" class="text-center py-8 text-slate-400">Chưa có tài khoản ngân hàng nào.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- Lịch sử ledger --}}
    <div class="bg-white rounded-2xl border border-slate-200 overflow-hidden">
        <div class="px-4 py-3 border-b border-slate-100 font-bold text-slate-900">Lịch sử giao dịch (ledger)</div>
        <table class="w-full text-sm">
            <thead class="bg-slate-50 text-slate-500 text-xs uppercase font-semibold">
                <tr>
                    <th class="text-left px-4 py-3">Loại</th>
                    <th class="text-left px-4 py-3">Số tiền</th>
                    <th class="text-left px-4 py-3">Số dư sau GD</th>
                    <th class="text-left px-4 py-3">Mã idempotency</th>
                    <th class="text-left px-4 py-3">Thời gian</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse ($ledger as $entry)
                    @php
                        $typeLabels = [
                            'deposit' => ['Nạp tiền', 'bg-emerald-100 text-emerald-700'],
                            'withdrawal' => ['Rút tiền', 'bg-red-100 text-red-700'],
                            'withdrawal_refund' => ['Hoàn tiền rút', 'bg-amber-100 text-amber-700'],
                            'interest' => ['Lãi tự động', 'bg-blue-100 text-blue-700'],
                        ];
                        [$label, $badge] = $typeLabels[$entry->type] ?? [$entry->type, 'bg-slate-100 text-slate-500'];
                    @endphp
                    <tr>
                        <td class="px-4 py-3"><span class="px-2 py-1 rounded-lg text-xs font-bold {{ $badge }}">{{ $label }}</span></td>
                        <td class="px-4 py-3 mono font-semibold {{ $entry->amount >= 0 ? 'text-emerald-600' : 'text-red-600' }}">
                            {{ $entry->amount >= 0 ? '+' : '' }}{{ number_format($entry->amount) }}đ
                        </td>
                        <td class="px-4 py-3 mono">{{ number_format($entry->balance_after) }}đ</td>
                        <td class="px-4 py-3 mono text-xs text-slate-400">{{ $entry->idempotency_key }}</td>
                        <td class="px-4 py-3 text-slate-400">{{ $entry->created_at->format('d/m/Y H:i:s') }}</td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="text-center py-10 text-slate-400">Chưa có giao dịch nào.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="mt-4">{{ $ledger->links() }}</div>
</div>
