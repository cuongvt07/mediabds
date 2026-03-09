<div class="h-full flex flex-col bg-slate-50 relative overflow-hidden">
    <!-- Header with Glassmorphism -->
    <div class="sticky top-0 z-10 bg-white/80 backdrop-blur-md border-b border-gray-200 px-4 sm:px-6 py-4 flex flex-col sm:flex-row items-center justify-between gap-4 shadow-sm">
        <div class="flex items-center gap-3 md:gap-4 w-full sm:w-auto">
            <a href="/business" class="hover:bg-gray-100 p-2 rounded-full transition-colors">
                <i class="fa-solid fa-arrow-left text-gray-400 text-sm"></i>
            </a>
            <div class="flex items-center gap-2 md:gap-3">
                <div class="w-8 h-8 md:w-10 md:h-10 rounded-lg md:rounded-xl bg-gradient-to-br from-blue-500 to-indigo-600 text-white flex items-center justify-center font-bold shadow-lg text-sm md:text-base">
                    {{ substr($user->name, 0, 1) }}
                </div>
                <div>
                    <div class="flex items-center gap-2 md:gap-3">
                        <h1 class="text-sm md:text-xl font-black text-slate-800 tracking-tight uppercase leading-none truncate max-w-[120px] md:max-w-none">{{ $user->name }}</h1>
                        @if($user->rank)
                        <span class="px-1.5 py-0.5 rounded text-[8px] md:text-[9px] font-black uppercase tracking-tighter bg-blue-100 text-blue-600 border border-blue-200 whitespace-nowrap">
                            {{ $user->rank->name }}
                        </span>
                        @endif
                    </div>
                    <p class="text-[9px] md:text-[11px] text-gray-500 mt-0.5 md:mt-1 font-mono font-bold uppercase">{{ substr($user->phone, 0, 7) }}*** • ID: {{ $user->id }}</p>
                </div>
            </div>
        </div>

        <div class="flex items-center gap-2 sm:gap-3 w-full sm:w-auto">
            <select wire:model.live="filterYear" class="flex-1 sm:flex-none bg-white border border-gray-200 rounded-xl px-3 py-2 text-[10px] md:text-xs font-black text-slate-700 outline-none focus:ring-2 focus:ring-blue-500/20 uppercase tracking-widest">
                @for ($y = date('Y'); $y >= 2024; $y--)
                    <option value="{{ $y }}">Năm {{ $y }}</option>
                @endfor
            </select>
            <select wire:model.live="filterQuarter" class="flex-1 sm:flex-none bg-white border border-gray-200 rounded-xl px-3 py-2 text-[10px] md:text-xs font-black text-slate-700 outline-none focus:ring-2 focus:ring-blue-500/20 uppercase tracking-widest">
                <option value="all">Tất cả</option>
                <option value="1">Q1</option>
                <option value="2">Q2</option>
                <option value="3">Q3</option>
                <option value="4">Q4</option>
            </select>
        </div>
    </div>

    <div class="flex-1 overflow-auto p-6 space-y-6">
        <!-- Stats Grid (Antigravity Style) -->
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 md:gap-6">
            <div class="bg-white p-4 sm:p-6 rounded-[20px] sm:rounded-[24px] border border-gray-100 shadow-sm relative overflow-hidden group hover:shadow-xl hover:-translate-y-1 transition-all">
                <div class="absolute top-0 right-0 p-3 sm:p-4 text-blue-500/5 text-3xl sm:text-4xl font-black"><i class="fa-solid fa-coins"></i></div>
                <p class="text-[9px] sm:text-[10px] text-gray-400 font-black uppercase tracking-widest mb-1 sm:mb-1">Hoa hồng</p>
                <h3 class="text-lg sm:text-2xl font-mono font-black text-blue-600 truncate">{{ number_format($stats['total_revenue'] / 1000, 0, ',', '.') }}<span class="text-[10px] sm:text-xs">k</span></h3>
                <div class="mt-2 sm:mt-4 h-1 w-6 sm:w-8 bg-blue-500 rounded-full"></div>
            </div>
            
            <div class="bg-white p-4 sm:p-6 rounded-[20px] sm:rounded-[24px] border border-gray-100 shadow-sm relative overflow-hidden group hover:shadow-xl hover:-translate-y-1 transition-all">
                <div class="absolute top-0 right-0 p-3 sm:p-4 text-emerald-500/5 text-3xl sm:text-4xl font-black"><i class="fa-solid fa-gift"></i></div>
                <p class="text-[9px] sm:text-[10px] text-gray-400 font-black uppercase tracking-widest mb-1 sm:mb-1">Tiền thưởng</p>
                <h3 class="text-lg sm:text-2xl font-mono font-black text-emerald-600 truncate">{{ number_format($stats['total_bonus'] / 1000, 0, ',', '.') }}<span class="text-[10px] sm:text-xs">k</span></h3>
                <div class="mt-2 sm:mt-4 h-1 w-6 sm:w-8 bg-emerald-500 rounded-full"></div>
            </div>
 
            <div class="bg-white p-4 sm:p-6 rounded-[20px] sm:rounded-[24px] border border-gray-100 shadow-sm relative overflow-hidden group hover:shadow-xl hover:-translate-y-1 transition-all">
                <div class="absolute top-0 right-0 p-3 sm:p-4 text-orange-500/5 text-3xl sm:text-4xl font-black"><i class="fa-solid fa-file-contract"></i></div>
                <p class="text-[9px] sm:text-[10px] text-gray-400 font-black uppercase tracking-widest mb-1 sm:mb-1">Giao dịch</p>
                <h3 class="text-lg sm:text-2xl font-mono font-black text-orange-600">{{ $stats['total_deals'] }}</h3>
                <div class="mt-2 sm:mt-4 h-1 w-6 sm:w-8 bg-orange-500 rounded-full"></div>
            </div>
 
            <div class="bg-white p-4 sm:p-6 rounded-[20px] sm:rounded-[24px] border border-gray-100 shadow-sm relative overflow-hidden group hover:shadow-xl hover:-translate-y-1 transition-all">
                <div class="absolute top-0 right-0 p-3 sm:p-4 text-indigo-500/5 text-3xl sm:text-4xl font-black"><i class="fa-solid fa-user-plus"></i></div>
                <p class="text-[9px] sm:text-[10px] text-gray-400 font-black uppercase tracking-widest mb-1 sm:mb-1">Tuyển dụng</p>
                <h3 class="text-lg sm:text-2xl font-mono font-black text-indigo-600">{{ $stats['total_invites'] }}</h3>
                <div class="mt-2 sm:mt-4 h-1 w-6 sm:w-8 bg-indigo-500 rounded-full"></div>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <!-- Sales List -->
            <div class="bg-white rounded-[32px] border border-gray-100 shadow-sm overflow-hidden flex flex-col">
                <div class="px-5 sm:px-8 py-4 sm:py-6 border-b border-gray-50 flex items-center justify-between">
                    <h3 class="text-sm sm:text-base font-black text-slate-800 uppercase tracking-tight flex items-center gap-2 sm:gap-3">
                        <span class="w-7 h-7 sm:w-8 sm:h-8 rounded-lg bg-orange-100 text-orange-600 flex items-center justify-center transition-transform group-hover:rotate-12">
                            <i class="fa-solid fa-shopping-bag text-[10px] sm:text-xs"></i>
                        </span>
                        Lịch sử bán hàng
                    </h3>
                </div>
                <div class="flex-1 overflow-auto">
                    <div class="min-w-[400px]">
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
                </div>
                <div class="p-4 border-t border-gray-50">
                    {{ $sales->links() }}
                </div>
            </div>

            <!-- Invites List -->
            <div class="bg-white rounded-[32px] border border-gray-100 shadow-sm overflow-hidden flex flex-col">
                <div class="px-5 sm:px-8 py-4 sm:py-6 border-b border-gray-50 flex items-center justify-between">
                    <h3 class="text-sm sm:text-base font-black text-slate-800 uppercase tracking-tight flex items-center gap-2 sm:gap-3">
                        <span class="w-7 h-7 sm:w-8 sm:h-8 rounded-lg bg-indigo-100 text-indigo-600 flex items-center justify-center transition-transform group-hover:rotate-12">
                            <i class="fa-solid fa-users text-[10px] sm:text-xs"></i>
                        </span>
                        Danh sách CTV mới
                    </h3>
                </div>
                <div class="flex-1 overflow-auto">
                    <div class="min-w-[400px]">
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
                </div>
                <div class="p-4 border-t border-gray-50">
                    {{ $invites->links() }}
                </div>
            </div>
        </div>
    </div>
</div>
