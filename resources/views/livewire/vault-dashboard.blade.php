<div class="h-full flex flex-col p-6 max-w-[1600px] w-full mx-auto relative flex-1 pb-24">
    <div class="flex flex-col sm:flex-row items-center justify-between gap-4 mb-8 flex-shrink-0">
        <div class="text-center sm:text-left">
            <h1 class="text-2xl sm:text-3xl font-bold text-slate-900 tracking-tight flex items-center justify-center sm:justify-start gap-3">
                <div class="p-2 sm:p-2.5 bg-emerald-100 rounded-xl">
                    <i class="fa-solid fa-vault text-emerald-600 text-lg sm:text-xl"></i>
                </div>
                Ví sinh lời (Vault)
            </h1>
            <p class="text-slate-500 mt-2 font-medium text-sm sm:text-base">
                Tổng quan module Vault — user, két, giao dịch, cấu hình SePay. Module hoàn toàn tách biệt hệ thống BĐS chính.
            </p>
        </div>
        <a href="{{ route('vault.ekyc.review') }}" class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-white border border-slate-200 text-slate-600 font-semibold text-sm hover:bg-slate-50">
            <i class="fa-solid fa-id-card"></i> Duyệt eKYC
        </a>
    </div>

    {{-- Cảnh báo OTP tắt --}}
    @unless ($otpEnabled)
        <div class="mb-6 p-4 rounded-xl bg-amber-50 border border-amber-100 flex items-start gap-3">
            <div class="p-2 bg-amber-100 rounded-lg text-amber-600 shrink-0">
                <i class="fa-solid fa-triangle-exclamation text-sm leading-none"></i>
            </div>
            <p class="text-amber-700 text-sm font-medium">
                OTP (Twilio) đang <strong>TẠM TẮT</strong> trên toàn hệ thống — đăng ký/đặt PIN/rút tiền không yêu cầu SMS xác thực. Đổi <code class="bg-amber-100 px-1 rounded">VAULT_OTP_ENABLED=true</code> trong .env server khi Twilio sẵn sàng.
            </p>
        </div>
    @endunless

    {{-- KPI tổng quan --}}
    <div class="grid grid-cols-2 md:grid-cols-5 gap-3 mb-6">
        <div class="bg-white rounded-2xl border border-slate-200 p-4">
            <p class="text-xs text-slate-400 font-semibold uppercase">Người dùng</p>
            <p class="text-2xl font-bold text-slate-900 mt-1 mono">{{ number_format($stats['usersCount']) }}</p>
        </div>
        <div class="bg-white rounded-2xl border border-slate-200 p-4">
            <p class="text-xs text-slate-400 font-semibold uppercase">Tổng gốc trong két</p>
            <p class="text-2xl font-bold text-slate-900 mt-1 mono">{{ number_format($stats['totalPrincipal']) }}đ</p>
        </div>
        <div class="bg-white rounded-2xl border border-slate-200 p-4">
            <p class="text-xs text-slate-400 font-semibold uppercase">Tổng lãi lũy kế</p>
            <p class="text-2xl font-bold text-emerald-600 mt-1 mono">{{ number_format($stats['totalInterest']) }}đ</p>
        </div>
        <div class="bg-white rounded-2xl border border-slate-200 p-4">
            <p class="text-xs text-slate-400 font-semibold uppercase">Rút tiền đang xử lý</p>
            <p class="text-2xl font-bold text-amber-600 mt-1 mono">{{ number_format($stats['pendingWithdrawals']) }}</p>
        </div>
        <div class="bg-white rounded-2xl border border-slate-200 p-4">
            <p class="text-xs text-slate-400 font-semibold uppercase">Chờ thanh toán (nạp)</p>
            <p class="text-2xl font-bold text-amber-600 mt-1 mono">{{ number_format($stats['pendingDeposits']) }}</p>
        </div>
    </div>

    {{-- Sub-tabs --}}
    <div class="mb-6 flex gap-2 flex-wrap">
        @foreach (['overview' => 'Tổng quan', 'users' => 'Người dùng', 'deposits' => 'Lịch sử nạp', 'withdrawals' => 'Lịch sử rút', 'sepay' => 'Cấu hình SePay'] as $key => $label)
            <button wire:click="switchSubTab('{{ $key }}')"
                class="px-4 py-2 rounded-xl text-sm font-semibold transition-all {{ $activeSubTab === $key ? 'bg-slate-900 text-white' : 'bg-white text-slate-600 border border-slate-200 hover:bg-slate-50' }}">
                {{ $label }}
            </button>
        @endforeach
    </div>

    @if ($activeSubTab === 'overview')
        <div class="bg-white rounded-2xl border border-slate-200 p-6 text-slate-500 text-sm">
            Chọn 1 mục ở trên để xem chi tiết: danh sách người dùng, lịch sử nạp/rút tiền, hoặc cấu hình webhook SePay.
        </div>
    @endif

    @if ($activeSubTab === 'users')
        <div class="bg-white rounded-2xl border border-slate-200 overflow-hidden">
            <table class="w-full text-sm">
                <thead class="bg-slate-50 text-slate-500 text-xs uppercase font-semibold">
                    <tr>
                        <th class="text-left px-4 py-3">Người dùng</th>
                        <th class="text-left px-4 py-3">SĐT</th>
                        <th class="text-left px-4 py-3">eKYC</th>
                        <th class="text-left px-4 py-3">Két</th>
                        <th class="text-left px-4 py-3">TK ngân hàng</th>
                        <th class="text-left px-4 py-3">Ngày tạo</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse ($users as $u)
                        <tr>
                            <td class="px-4 py-3">
                                <p class="font-semibold text-slate-900">{{ $u->name }}</p>
                                <p class="text-xs text-slate-400">{{ $u->vault_code }}</p>
                            </td>
                            <td class="px-4 py-3 mono">{{ $u->phone }}</td>
                            <td class="px-4 py-3">
                                <span class="px-2 py-1 rounded-lg text-xs font-bold {{ $u->phone_verified_at ? 'bg-emerald-100 text-emerald-700' : 'bg-slate-100 text-slate-500' }}">
                                    Cấp {{ $u->ekyc_level }}
                                </span>
                            </td>
                            <td class="px-4 py-3 mono">{{ $u->vaults_count }}</td>
                            <td class="px-4 py-3 mono">{{ $u->bank_accounts_count }}</td>
                            <td class="px-4 py-3 text-slate-400">{{ $u->created_at->format('d/m/Y H:i') }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="text-center py-10 text-slate-400">Chưa có người dùng nào.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="mt-4">{{ $users->links() }}</div>
    @endif

    @if ($activeSubTab === 'deposits')
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
    @endif

    @if ($activeSubTab === 'withdrawals')
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
    @endif

    @if ($activeSubTab === 'sepay')
        <div class="bg-white rounded-2xl border border-slate-200 p-6 max-w-2xl">
            <div class="flex items-center justify-between mb-5">
                <h2 class="font-bold text-lg text-slate-900">Webhook SePay</h2>
                <button wire:click="toggleSepayEnabled"
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
                            <button wire:click="rotateSepaySecret"
                                wire:confirm="Xoay secret key MỚI? Key cũ sẽ mất tác dụng ngay — nhớ cập nhật lại trên SePay Console sau khi bấm."
                                class="text-xs px-3 py-1.5 rounded-lg bg-white border border-red-200 text-red-600 hover:bg-red-50">
                                <i class="fa-solid fa-arrows-rotate"></i> Xoay key mới
                            </button>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    @endif
</div>
