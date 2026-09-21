<div class="max-w-[1400px] w-full mx-auto">
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-slate-900">Tổng quan Ví sinh lời</h1>
        <p class="text-slate-500 mt-1 text-sm">Module Vault hoàn toàn tách biệt hệ thống BĐS chính (user, guard, bảng riêng).</p>
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
        <a href="{{ route('vault.withdrawals') }}" class="bg-white rounded-2xl border border-slate-200 p-4 hover:border-blue-300 transition">
            <p class="text-xs text-slate-400 font-semibold uppercase">Rút tiền đang xử lý</p>
            <p class="text-2xl font-bold text-amber-600 mt-1 mono">{{ number_format($stats['pendingWithdrawals']) }}</p>
        </a>
        <a href="{{ route('vault.deposits') }}" class="bg-white rounded-2xl border border-slate-200 p-4 hover:border-blue-300 transition">
            <p class="text-xs text-slate-400 font-semibold uppercase">Chờ thanh toán (nạp)</p>
            <p class="text-2xl font-bold text-amber-600 mt-1 mono">{{ number_format($stats['pendingDeposits']) }}</p>
        </a>
    </div>

    <div class="bg-white rounded-2xl border border-slate-200 overflow-hidden">
        <div class="px-4 py-3 border-b border-slate-100 font-bold text-slate-900">Danh sách người dùng</div>
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
</div>
