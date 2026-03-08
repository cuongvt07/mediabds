<div class="h-full flex flex-col bg-slate-50 relative overflow-hidden">
    <!-- Header with Glassmorphism -->
    <div class="sticky top-0 z-10 bg-white/80 backdrop-blur-md border-b border-gray-200 px-6 py-4 flex items-center justify-between shadow-sm">
        <div class="flex items-center gap-4">
            <a href="/business" class="hover:bg-gray-100 p-2 rounded-full transition-colors">
                <i class="fa-solid fa-arrow-left text-gray-400"></i>
            </a>
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-blue-500 to-indigo-600 text-white flex items-center justify-center font-bold shadow-lg">
                    {{ substr($user->name, 0, 1) }}
                </div>
                <div>
                    <div class="flex items-center gap-3">
                        <h1 class="text-xl font-black text-slate-800 tracking-tight uppercase leading-none">{{ $user->name }}</h1>
                        @if($user->rank)
                        <span class="px-2 py-0.5 rounded text-[9px] font-black uppercase tracking-tighter bg-blue-100 text-blue-600 border border-blue-200">
                            {{ $user->rank->name }}
                        </span>
                        @endif
                    </div>
                    <p class="text-[11px] text-gray-500 mt-1 font-mono font-bold uppercase">{{ $user->phone }} • ID: {{ $user->id }}</p>
                </div>
            </div>
        </div>

        <div class="flex items-center gap-3">
            <select wire:model.live="filterYear" class="bg-white border border-gray-200 rounded-xl px-4 py-2 text-xs font-bold text-slate-700 outline-none focus:ring-2 focus:ring-blue-500/20">
                @for ($y = date('Y'); $y >= 2024; $y--)
                    <option value="{{ $y }}">Năm {{ $y }}</option>
                @endfor
            </select>
            <select wire:model.live="filterQuarter" class="bg-white border border-gray-200 rounded-xl px-4 py-2 text-xs font-bold text-slate-700 outline-none focus:ring-2 focus:ring-blue-500/20">
                <option value="all">Tất cả quý</option>
                <option value="1">Quý 1 (T1-T3)</option>
                <option value="2">Quý 2 (T4-T6)</option>
                <option value="3">Quý 3 (T7-T9)</option>
                <option value="4">Quý 4 (T10-T12)</option>
            </select>
        </div>
    </div>

    <div class="flex-1 overflow-auto p-6 space-y-6">
        <!-- Stats Grid (Antigravity Style) -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
            <div class="bg-white p-6 rounded-[24px] border border-gray-100 shadow-sm relative overflow-hidden group hover:shadow-xl hover:-translate-y-1 transition-all">
                <div class="absolute top-0 right-0 p-4 text-blue-500/5 text-4xl font-black"><i class="fa-solid fa-coins"></i></div>
                <p class="text-[10px] text-gray-400 font-black uppercase tracking-widest mb-1">Hoa hồng</p>
                <h3 class="text-2xl font-mono font-black text-blue-600">{{ number_format($stats['total_revenue'], 0, ',', '.') }} <span class="text-xs">đ</span></h3>
                <div class="mt-4 h-1 w-8 bg-blue-500 rounded-full"></div>
            </div>
            
            <div class="bg-white p-6 rounded-[24px] border border-gray-100 shadow-sm relative overflow-hidden group hover:shadow-xl hover:-translate-y-1 transition-all">
                <div class="absolute top-0 right-0 p-4 text-emerald-500/5 text-4xl font-black"><i class="fa-solid fa-gift"></i></div>
                <p class="text-[10px] text-gray-400 font-black uppercase tracking-widest mb-1">Tiền thưởng</p>
                <h3 class="text-2xl font-mono font-black text-emerald-600">{{ number_format($stats['total_bonus'], 0, ',', '.') }} <span class="text-xs">đ</span></h3>
                <div class="mt-4 h-1 w-8 bg-emerald-500 rounded-full"></div>
            </div>

            <div class="bg-white p-6 rounded-[24px] border border-gray-100 shadow-sm relative overflow-hidden group hover:shadow-xl hover:-translate-y-1 transition-all">
                <div class="absolute top-0 right-0 p-4 text-orange-500/5 text-4xl font-black"><i class="fa-solid fa-file-contract"></i></div>
                <p class="text-[10px] text-gray-400 font-black uppercase tracking-widest mb-1">Giao dịch</p>
                <h3 class="text-2xl font-mono font-black text-orange-600">{{ $stats['total_deals'] }}</h3>
                <div class="mt-4 h-1 w-8 bg-orange-500 rounded-full"></div>
            </div>

            <div class="bg-white p-6 rounded-[24px] border border-gray-100 shadow-sm relative overflow-hidden group hover:shadow-xl hover:-translate-y-1 transition-all">
                <div class="absolute top-0 right-0 p-4 text-indigo-500/5 text-4xl font-black"><i class="fa-solid fa-user-plus"></i></div>
                <p class="text-[10px] text-gray-400 font-black uppercase tracking-widest mb-1">Tuyển dụng</p>
                <h3 class="text-2xl font-mono font-black text-indigo-600">{{ $stats['total_invites'] }}</h3>
                <div class="mt-4 h-1 w-8 bg-indigo-500 rounded-full"></div>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <!-- Sales List -->
            <div class="bg-white rounded-[32px] border border-gray-100 shadow-sm overflow-hidden flex flex-col">
                <div class="px-8 py-6 border-b border-gray-50 flex items-center justify-between">
                    <h3 class="font-black text-slate-800 uppercase tracking-tight flex items-center gap-3">
                        <span class="w-8 h-8 rounded-lg bg-orange-100 text-orange-600 flex items-center justify-center transition-transform group-hover:rotate-12">
                            <i class="fa-solid fa-shopping-bag text-xs"></i>
                        </span>
                        Lịch sử bán hàng
                    </h3>
                </div>
                <div class="flex-1 overflow-auto">
                    <table class="w-full text-left">
                        <thead class="bg-gray-50/50 text-[10px] font-black uppercase text-gray-400 tracking-wider">
                            <tr>
                                <th class="px-8 py-4">Dự án</th>
                                <th class="px-4 py-4 text-right">Hoa hồng</th>
                                <th class="px-4 py-4 text-right">Thưởng</th>
                                <th class="px-8 py-4 text-right">Ngày</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-50">
                            @forelse($sales as $sale)
                            <tr class="hover:bg-slate-50 transition-colors group">
                                <td class="px-8 py-5">
                                    <div class="text-[13px] font-black text-slate-700 uppercase leading-snug">{{ $sale->project_name }}</div>
                                    <div class="text-[10px] text-gray-400 font-mono mt-0.5">{{ $sale->listing->code ?? 'N/A' }}</div>
                                </td>
                                <td class="px-4 py-5 text-right font-mono text-[13px] font-bold text-blue-600">
                                    {{ number_format($sale->revenue_amount, 0, ',', '.') }}
                                </td>
                                <td class="px-4 py-5 text-right font-mono text-[13px] font-bold text-emerald-500">
                                    {{ number_format($sale->bonus_amount, 0, ',', '.') }}
                                </td>
                                <td class="px-8 py-5 text-right text-[11px] text-gray-400 font-medium">
                                    {{ $sale->sold_at->format('d/m/Y') }}
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="4" class="px-8 py-20 text-center">
                                    <div class="flex flex-col items-center gap-3">
                                        <i class="fa-solid fa-inbox text-4xl text-gray-200"></i>
                                        <p class="text-xs text-gray-400 font-medium italic">Chưa có dữ liệu bán hàng</p>
                                    </div>
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="p-4 border-t border-gray-50">
                    {{ $sales->links() }}
                </div>
            </div>

            <!-- Invites List -->
            <div class="bg-white rounded-[32px] border border-gray-100 shadow-sm overflow-hidden flex flex-col">
                <div class="px-8 py-6 border-b border-gray-50 flex items-center justify-between">
                    <h3 class="font-black text-slate-800 uppercase tracking-tight flex items-center gap-3">
                        <span class="w-8 h-8 rounded-lg bg-indigo-100 text-indigo-600 flex items-center justify-center transition-transform group-hover:rotate-12">
                            <i class="fa-solid fa-users text-xs"></i>
                        </span>
                        Danh sách CTV mới
                    </h3>
                </div>
                <div class="flex-1 overflow-auto">
                    <table class="w-full text-left">
                        <thead class="bg-gray-50/50 text-[10px] font-black uppercase text-gray-400 tracking-wider">
                            <tr>
                                <th class="px-8 py-4">Nhân viên</th>
                                <th class="px-4 py-4">Liên hệ</th>
                                <th class="px-8 py-4 text-right">Ngày tham gia</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-50">
                            @forelse($invites as $invite)
                            <tr class="hover:bg-slate-50 transition-colors group">
                                <td class="px-8 py-5">
                                    <div class="text-[13px] font-black text-slate-700 uppercase leading-snug">{{ $invite->invitedUser->name ?? 'N/A' }}</div>
                                    <div class="text-[10px] text-gray-400 font-mono mt-0.5">Mã: {{ $invite->inviter_code }}</div>
                                </td>
                                <td class="px-4 py-5 text-[12px] font-mono text-gray-500">
                                    {{ $invite->invitedUser->phone ?? 'N/A' }}
                                </td>
                                <td class="px-8 py-5 text-right text-[11px] text-gray-400 font-medium">
                                    {{ $invite->created_at->format('d/m/Y') }}
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="3" class="px-8 py-20 text-center">
                                    <div class="flex flex-col items-center gap-3">
                                        <i class="fa-solid fa-user-slash text-4xl text-gray-200"></i>
                                        <p class="text-xs text-gray-400 font-medium italic">Chưa mời được CTV nào</p>
                                    </div>
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="p-4 border-t border-gray-50">
                    {{ $invites->links() }}
                </div>
            </div>
        </div>
    </div>
</div>
