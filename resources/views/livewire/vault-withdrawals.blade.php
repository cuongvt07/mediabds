<div class="max-w-[1400px] w-full mx-auto">
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-slate-900">Lịch sử rút tiền</h1>
        <p class="text-slate-500 mt-1 text-sm">Toàn bộ lệnh rút tiền — mỗi lệnh yêu cầu PIN xác nhận (+ OTP khi bật lại).</p>
    </div>

    <div class="bg-white rounded-2xl border border-slate-200 overflow-hidden">
        <table class="w-full text-sm">
            <thead class="bg-slate-50 text-slate-500 text-xs uppercase font-semibold">
                <tr>
                    <th class="text-left px-4 py-3">#</th>
                    <th class="text-left px-4 py-3">Người dùng</th>
                    <th class="text-left px-4 py-3">Số tiền</th>
                    <th class="text-left px-4 py-3">Trạng thái</th>
                    <th class="text-left px-4 py-3">Lý do lỗi</th>
                    <th class="text-left px-4 py-3">Thời gian</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse ($withdrawals as $w)
                    <tr>
                        <td class="px-4 py-3 mono font-semibold text-slate-900">#{{ $w->id }}</td>
                        <td class="px-4 py-3">{{ $w->vaultUser->name ?? '—' }} <span class="text-xs text-slate-400">{{ $w->vaultUser->phone ?? '' }}</span></td>
                        <td class="px-4 py-3 mono">{{ number_format($w->amount) }}đ</td>
                        <td class="px-4 py-3">
                            @php $withdrawBadges = ['pending_otp' => 'bg-slate-100 text-slate-500', 'pending' => 'bg-amber-100 text-amber-700', 'processing' => 'bg-amber-100 text-amber-700', 'success' => 'bg-emerald-100 text-emerald-700', 'failed' => 'bg-red-100 text-red-700']; @endphp
                            <span class="px-2 py-1 rounded-lg text-xs font-bold {{ $withdrawBadges[$w->status] ?? 'bg-slate-100 text-slate-500' }}">{{ $w->status }}</span>
                        </td>
                        <td class="px-4 py-3 text-xs text-red-500">{{ $w->failure_reason ?? '—' }}</td>
                        <td class="px-4 py-3 text-slate-400">{{ $w->created_at->format('d/m/Y H:i') }}</td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="text-center py-10 text-slate-400">Chưa có giao dịch rút tiền nào.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="mt-4">{{ $withdrawals->links() }}</div>
</div>
