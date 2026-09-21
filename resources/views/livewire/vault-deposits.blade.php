<div class="max-w-[1400px] w-full mx-auto">
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-slate-900">Lịch sử nạp tiền</h1>
        <p class="text-slate-500 mt-1 text-sm">Toàn bộ lệnh nạp tiền qua QR VietQR — SePay tự xác nhận khi khớp giao dịch.</p>
    </div>

    <div class="bg-white rounded-2xl border border-slate-200 overflow-hidden">
        <table class="w-full text-sm">
            <thead class="bg-slate-50 text-slate-500 text-xs uppercase font-semibold">
                <tr>
                    <th class="text-left px-4 py-3">Mã GD</th>
                    <th class="text-left px-4 py-3">Người dùng</th>
                    <th class="text-left px-4 py-3">Số tiền</th>
                    <th class="text-left px-4 py-3">Trạng thái</th>
                    <th class="text-left px-4 py-3">Mã SePay</th>
                    <th class="text-left px-4 py-3">Thời gian</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse ($deposits as $d)
                    <tr>
                        <td class="px-4 py-3 mono font-semibold text-slate-900">{{ $d->payment_code ?? '—' }}</td>
                        <td class="px-4 py-3">{{ $d->vaultUser->name ?? '—' }} <span class="text-xs text-slate-400">{{ $d->vaultUser->phone ?? '' }}</span></td>
                        <td class="px-4 py-3 mono">{{ number_format($d->amount) }}đ</td>
                        <td class="px-4 py-3">
                            @php $depositBadges = ['pending_payment' => 'bg-amber-100 text-amber-700', 'success' => 'bg-emerald-100 text-emerald-700', 'failed' => 'bg-red-100 text-red-700']; @endphp
                            <span class="px-2 py-1 rounded-lg text-xs font-bold {{ $depositBadges[$d->status] ?? 'bg-slate-100 text-slate-500' }}">{{ $d->status }}</span>
                        </td>
                        <td class="px-4 py-3 mono text-xs text-slate-400">{{ $d->sepay_transaction_id ?? '—' }}</td>
                        <td class="px-4 py-3 text-slate-400">{{ $d->created_at->format('d/m/Y H:i') }}</td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="text-center py-10 text-slate-400">Chưa có giao dịch nạp tiền nào.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="mt-4">{{ $deposits->links() }}</div>
</div>
