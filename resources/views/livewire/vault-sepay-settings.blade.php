<div class="max-w-2xl w-full mx-auto">
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-slate-900">Cấu hình SePay</h1>
        <p class="text-slate-500 mt-1 text-sm">Webhook xác nhận giao dịch chuyển khoản QR tự động.</p>
    </div>

    <div class="bg-white rounded-2xl border border-slate-200 p-6">
        <div class="flex items-center justify-between mb-5">
            <h2 class="font-bold text-lg text-slate-900">Webhook SePay</h2>
            <button wire:click="toggleEnabled"
                class="px-4 py-2 rounded-xl text-sm font-semibold {{ $sepaySettings->enabled ? 'bg-emerald-100 text-emerald-700' : 'bg-slate-100 text-slate-500' }}">
                <i class="fa-solid {{ $sepaySettings->enabled ? 'fa-toggle-on' : 'fa-toggle-off' }}"></i>
                {{ $sepaySettings->enabled ? 'Đang bật' : 'Đang tắt' }}
            </button>
        </div>

        <div class="space-y-4 text-sm">
            <div>
                <p class="text-slate-400 font-semibold uppercase text-xs mb-1">URL webhook</p>
                <p class="mono bg-slate-50 rounded-lg px-3 py-2">{{ url('/vault/hooks/sepay-payment') }}</p>
            </div>
            <div>
                <p class="text-slate-400 font-semibold uppercase text-xs mb-1">Tài khoản nhận tiền</p>
                <p class="bg-slate-50 rounded-lg px-3 py-2">
                    {{ $sepaySettings->bank_name ?? 'Chưa cấu hình' }}
                    @if ($sepaySettings->bank_account_number)
                        — {{ $sepaySettings->bank_account_number }} ({{ $sepaySettings->bank_account_name }})
                    @endif
                </p>
            </div>
            <div>
                <p class="text-slate-400 font-semibold uppercase text-xs mb-1">Lần webhook gần nhất</p>
                <p class="bg-slate-50 rounded-lg px-3 py-2">
                    {{ $sepaySettings->last_webhook_at?->format('d/m/Y H:i:s') ?? 'Chưa nhận webhook nào' }}
                </p>
            </div>

            <div>
                <p class="text-slate-400 font-semibold uppercase text-xs mb-1">Secret key (HMAC-SHA256)</p>
                @if ($revealedSecret)
                    <div class="bg-amber-50 border border-amber-100 rounded-lg px-3 py-2 mono text-xs break-all">{{ $revealedSecret }}</div>
                    <button wire:click="hideSecret" class="mt-2 text-xs text-slate-400 hover:text-slate-600">
                        <i class="fa-solid fa-eye-slash"></i> Ẩn lại
                    </button>
                @else
                    <div class="bg-slate-50 rounded-lg px-3 py-2 mono text-slate-400">••••••••••••••••••••••••••••••••</div>
                    <div class="mt-2 flex gap-2">
                        <button wire:click="revealSecret" class="text-xs px-3 py-1.5 rounded-lg bg-white border border-slate-200 text-slate-600 hover:bg-slate-50">
                            <i class="fa-solid fa-eye"></i> Xem lại
                        </button>
                        <button wire:click="rotateSecret"
                            wire:confirm="Xoay secret key MỚI? Key cũ sẽ mất tác dụng ngay — nhớ cập nhật lại trên SePay Console sau khi bấm."
                            class="text-xs px-3 py-1.5 rounded-lg bg-white border border-red-200 text-red-600 hover:bg-red-50">
                            <i class="fa-solid fa-arrows-rotate"></i> Xoay key mới
                        </button>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
