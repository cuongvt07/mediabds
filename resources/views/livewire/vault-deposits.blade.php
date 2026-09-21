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
                    <th class="text-left px-4 py-3">Log webhook</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse ($deposits as $d)
                    <tr>
                        <td class="px-4 py-3 mono font-semibold text-slate-900">{{ $d->payment_code ?? '—' }}</td>
                        <td class="px-4 py-3">{{ $d->vaultUser->name ?? '—' }} <span class="text-xs text-slate-400">{{ $d->vaultUser->phone ?? '' }}</span></td>
                        <td class="px-4 py-3 mono">{{ number_format($d->amount) }}đ</td>
                        <td class="px-4 py-3">
                            @php $depositBadges = ['pending_payment' => 'bg-amber-100 text-amber-700', 'success' => 'bg-emerald-100 text-emerald-700', 'failed' => 'bg-red-100 text-red-700', 'expired' => 'bg-slate-100 text-slate-500']; @endphp
                            <span class="px-2 py-1 rounded-lg text-xs font-bold {{ $depositBadges[$d->status] ?? 'bg-slate-100 text-slate-500' }}">{{ $d->status }}</span>
                        </td>
                        <td class="px-4 py-3 mono text-xs text-slate-400">{{ $d->sepay_transaction_id ?? '—' }}</td>
                        <td class="px-4 py-3 text-slate-400">{{ $d->created_at->format('d/m/Y H:i') }}</td>
                        <td class="px-4 py-3">
                            @if ($d->webhook_logs_count > 0)
                                <button wire:click="viewLogs({{ $d->id }})" class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg bg-blue-50 text-blue-600 text-xs font-bold hover:bg-blue-100">
                                    <i class="fa-solid fa-list"></i> {{ $d->webhook_logs_count }} lần
                                </button>
                            @else
                                <span class="text-xs text-slate-300">Chưa có</span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="text-center py-10 text-slate-400">Chưa có giao dịch nạp tiền nào.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="mt-4">{{ $deposits->links() }}</div>

    {{-- Modal xem log webhook của 1 lệnh nạp --}}
    @if ($viewingLogsForDepositId)
        <div class="fixed inset-0 bg-black/40 flex items-center justify-center z-50 p-4" wire:click.self="closeLogs">
            <div class="bg-white rounded-2xl w-full max-w-3xl max-h-[80vh] overflow-y-auto shadow-xl">
                <div class="sticky top-0 bg-white border-b border-slate-100 px-6 py-4 flex items-center justify-between">
                    <h3 class="font-bold text-lg text-slate-900">Log webhook — lệnh #{{ $viewingLogsForDepositId }}</h3>
                    <button wire:click="closeLogs" class="text-slate-400 hover:text-slate-600"><i class="fa-solid fa-xmark text-lg"></i></button>
                </div>
                <div class="p-6 space-y-3">
                    @forelse ($logs as $log)
                        @php
                            $outcomeBadges = [
                                'accepted' => 'bg-emerald-100 text-emerald-700',
                                'duplicate' => 'bg-slate-100 text-slate-500',
                            ];
                            $badgeClass = $outcomeBadges[$log->outcome] ?? 'bg-red-100 text-red-700';
                        @endphp
                        <div class="border border-slate-200 rounded-xl p-4">
                            <div class="flex items-center justify-between mb-2">
                                <span class="px-2 py-1 rounded-lg text-xs font-bold {{ $badgeClass }}">{{ $log->outcome }}</span>
                                <span class="text-xs text-slate-400 mono">{{ $log->created_at->format('d/m/Y H:i:s') }}</span>
                            </div>
                            <p class="text-sm text-slate-700 mb-2">{{ $log->reason }}</p>
                            <div class="flex gap-3 text-xs text-slate-400 mb-2">
                                @if ($log->ip_address)<span>IP: <span class="mono">{{ $log->ip_address }}</span></span>@endif
                                @if ($log->sepay_transaction_id)<span>SePay TXN: <span class="mono">{{ $log->sepay_transaction_id }}</span></span>@endif
                            </div>
                            @if ($log->payload)
                                <details class="text-xs">
                                    <summary class="cursor-pointer text-slate-400 hover:text-slate-600">Xem payload gốc</summary>
                                    <pre class="mt-2 bg-slate-50 rounded-lg p-3 overflow-x-auto mono">{{ json_encode($log->payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
                                </details>
                            @endif
                        </div>
                    @empty
                        <p class="text-center text-slate-400 py-8">Chưa có log webhook nào cho lệnh này.</p>
                    @endforelse
                </div>
            </div>
        </div>
    @endif
</div>
